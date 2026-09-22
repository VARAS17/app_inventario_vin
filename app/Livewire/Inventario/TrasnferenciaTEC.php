<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Tecnologia;
use App\Models\Personal;
use App\Models\Area;
use App\Models\asignaciones as Asignacion;
use App\Models\asignaciones_archivos as AsignacionArchivo;
use Carbon\Carbon;


class TrasnferenciaTEC extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // Buscadores reactivos y Filtro de Estado
    public string $searchTecnologia = '';
    public string $searchPersonal = '';
    public string $searchHistorial = '';
    public string $filtroEstado = 'activas'; // 'activas' | 'baja' | 'todas'

    // Estado del Formulario / CRUD
    public bool $isEditing = false;
    public ?int $asignacionId = null;
    public bool $mostrarModal = false;
    public bool $mostrarModalEliminar = false;
    public ?int $asignacionAEliminarId = null;

    // Fila seleccionada para el detalle inferior (Master-Detail)
    public ?int $asignacionSeleccionadaId = null;

    // Entidades seleccionadas en formulario
    public ?Tecnologia $tecnologiaSeleccionada = null;
    public ?Personal $personalSeleccionado = null;

    // Campos del formulario con Claves Foráneas
    public ?int $tecnologia_id = null;
    public ?int $personal_id = null;
    public ?int $area_origen_id = null;
    public ?int $area_destino_id = null;
    public string $fecha_traspaso = '';

    // Gestión de Archivos
    public array $nuevosArchivos = [];
    public array $archivos = [];
    public $archivosExistentes = [];

    // Previsualizador modal de archivos
    public bool $mostrarModalPreview = false;
    public ?string $previewSrc = null;
    public ?string $previewNombre = null;
    public ?string $previewTipo = null;

    public function updatingSearchHistorial(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'tecnologia_id'   => 'required|exists:tecnologias,id',
            'personal_id'     => 'nullable|exists:personal,id',
            'area_origen_id'  => 'nullable|exists:areas,id',
            'area_destino_id' => 'required|exists:areas,id',
            'fecha_traspaso'  => 'required|date',
            'archivos.*'      => 'nullable|file|max:15360',
        ];
    }

    protected $messages = [
        'tecnologia_id.required'   => 'Debe seleccionar un activo tecnológico.',
        'area_destino_id.required' => 'Debe seleccionar un área de destino válida.',
        'area_destino_id.exists'   => 'El área de destino seleccionada no existe.',
        'fecha_traspaso.required'  => 'La fecha del traspaso es obligatoria.',
        'archivos.*.max'           => 'Cada archivo adjunto no debe superar los 15MB.',
    ];

    public function mount(): void
    {
        $this->fecha_traspaso = now()->toDateString();

        $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
        if ($areaAlmacen) {
            $this->area_origen_id = $areaAlmacen->id;
        }

        $primera = Asignacion::latest('id')->first();
        if ($primera) {
            $this->asignacionSeleccionadaId = $primera->id;
        }
    }

    public function updatedAreaDestinoId(): void
    {
        if ($this->personalSeleccionado && $this->personalSeleccionado->area_id != $this->area_destino_id) {
            $this->deseleccionarPersonal();
        }
        $this->searchPersonal = '';
    }

    public function seleccionarParaDetalle(int $id): void
    {
        $this->asignacionSeleccionadaId = $id;
    }

    // =========================================================================
    // SELECCIÓN CON DETECCIÓN INTELIGENTE DE ORIGEN
    // =========================================================================
    public function seleccionarTecnologia(int $id): void
    {
        $this->tecnologiaSeleccionada = Tecnologia::find($id);
        if ($this->tecnologiaSeleccionada) {
            $this->tecnologia_id = $this->tecnologiaSeleccionada->id;
            $this->searchTecnologia = '';

            if ($this->tecnologiaSeleccionada->estado === 'Disponible') {
                $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
                $this->area_origen_id = $areaAlmacen?->id;
            } elseif ($this->tecnologiaSeleccionada->estado === 'Asignado') {
                $ultimaAsignacion = Asignacion::where('tecnologia_id', $this->tecnologia_id)
                    ->latest('id')
                    ->first();

                if ($ultimaAsignacion && $ultimaAsignacion->area_destino_id) {
                    $this->area_origen_id = $ultimaAsignacion->area_destino_id;
                }
            }
        }
    }

    public function deseleccionarTecnologia(): void
    {
        $this->tecnologiaSeleccionada = null;
        $this->tecnologia_id = null;
        $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
        $this->area_origen_id = $areaAlmacen?->id;
    }

    public function seleccionarPersonal(int $id): void
    {
        $this->personalSeleccionado = Personal::find($id);
        if ($this->personalSeleccionado) {
            $this->personal_id = $this->personalSeleccionado->id;
            $this->searchPersonal = '';
        }
    }

    public function deseleccionarPersonal(): void
    {
        $this->personalSeleccionado = null;
        $this->personal_id = null;
    }

    // =========================================================================
    // GESTIÓN DE ARCHIVOS CON BOTÓN "+"
    // =========================================================================
    public function updatedNuevosArchivos(): void
    {
        $this->validate(['nuevosArchivos.*' => 'file|max:15360']);
        foreach ($this->nuevosArchivos as $nuevo) {
            $this->archivos[] = $nuevo;
        }
        $this->nuevosArchivos = [];
    }

    public function eliminarArchivoTemporal(int $index): void
    {
        unset($this->archivos[$index]);
        $this->archivos = array_values($this->archivos);
    }

    public function eliminarArchivoExistente(int $archivoId): void
    {
        $archivo = AsignacionArchivo::find($archivoId);
        if ($archivo) {
            if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                Storage::disk('local')->delete($archivo->ruta_archivo);
            }
            $archivo->delete();
            $this->archivosExistentes = AsignacionArchivo::where('asignacion_id', $this->asignacionId)->get();
        }
    }

    // =========================================================================
    // PREVISUALIZADOR DE ARCHIVOS (BASE64)
    // =========================================================================
    public function previsualizarArchivoNuevo(int $index): void
    {
        if (!isset($this->archivos[$index])) return;

        $file = $this->archivos[$index];
        $mime = $file->getMimeType();
        $this->previewNombre = $file->getClientOriginalName();

        if (str_starts_with($mime, 'image/')) {
            $this->previewTipo = 'imagen';
            $this->previewSrc = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        } elseif ($mime === 'application/pdf') {
            $this->previewTipo = 'pdf';
            $this->previewSrc = 'data:application/pdf;base64,' . base64_encode(file_get_contents($file->getRealPath()));
        } else {
            $this->previewTipo = 'otro';
            $this->previewSrc = null;
        }

        $this->mostrarModalPreview = true;
    }

    public function previsualizarArchivoExistente(int $archivoId): void
    {
        $archivo = AsignacionArchivo::find($archivoId);
        if (!$archivo || !Storage::disk('local')->exists($archivo->ruta_archivo)) return;

        $mime = Storage::disk('local')->mimeType($archivo->ruta_archivo);
        $contenido = Storage::disk('local')->get($archivo->ruta_archivo);
        $this->previewNombre = $archivo->nombre_archivo;

        if (str_starts_with($mime, 'image/')) {
            $this->previewTipo = 'imagen';
            $this->previewSrc = 'data:' . $mime . ';base64,' . base64_encode($contenido);
        } elseif ($mime === 'application/pdf') {
            $this->previewTipo = 'pdf';
            $this->previewSrc = 'data:application/pdf;base64,' . base64_encode($contenido);
        } else {
            $this->previewTipo = 'otro';
            $this->previewSrc = null;
        }

        $this->mostrarModalPreview = true;
    }

    public function cerrarPreview(): void
    {
        $this->mostrarModalPreview = false;
        $this->previewSrc = null;
        $this->previewNombre = null;
        $this->previewTipo = null;
    }

    // =========================================================================
    // OPERACIONES CRUD (AUDITORÍA HISTÓRICA)
    // =========================================================================
    public function abrirModal(): void
    {
        $this->resetFormulario();
        $this->isEditing = false;
        $this->mostrarModal = true;
    }

    public function editar(int $id): void
    {
        $asignacion = Asignacion::with(['tecnologia', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])->findOrFail($id);

        $esUltima = Asignacion::where('tecnologia_id', $asignacion->tecnologia_id)->max('id') === $id;
        if (!$esUltima) {
            session()->flash('mensaje', 'Este registro es histórico. Solo se permite editar la asignación actual.');
            return;
        }

        $this->resetFormulario();
        $this->isEditing = true;
        $this->asignacionId = $id;

        $this->tecnologia_id          = $asignacion->tecnologia_id;
        $this->tecnologiaSeleccionada = $asignacion->tecnologia;
        $this->personal_id            = $asignacion->personal_id;
        $this->personalSeleccionado   = $asignacion->personal;
        $this->area_origen_id         = $asignacion->area_origen_id;
        $this->area_destino_id        = $asignacion->area_destino_id;
        $this->fecha_traspaso         = $asignacion->fecha_traspaso;
        $this->archivosExistentes     = $asignacion->archivos;

        $this->mostrarModal = true;
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->isEditing) {
                $asignacion = Asignacion::findOrFail($this->asignacionId);
                $antiguoTecnologiaId = $asignacion->tecnologia_id;

                $asignacion->update([
                    'tecnologia_id'   => $this->tecnologia_id,
                    'personal_id'     => $this->personal_id,
                    'area_origen_id'  => $this->area_origen_id,
                    'area_destino_id' => $this->area_destino_id,
                    'fecha_traspaso'  => $this->fecha_traspaso,
                ]);

                if ($antiguoTecnologiaId !== $this->tecnologia_id) {
                    Tecnologia::where('id', $antiguoTecnologiaId)->update(['estado' => 'Disponible']);
                    Tecnologia::where('id', $this->tecnologia_id)->update(['estado' => 'Asignado']);
                }
            } else {
                $asignacion = Asignacion::create([
                    'tecnologia_id'   => $this->tecnologia_id,
                    'personal_id'     => $this->personal_id,
                    'area_origen_id'  => $this->area_origen_id,
                    'area_destino_id' => $this->area_destino_id,
                    'fecha_traspaso'  => $this->fecha_traspaso,
                ]);

                Tecnologia::where('id', $this->tecnologia_id)->update(['estado' => 'Asignado']);
                $this->asignacionSeleccionadaId = $asignacion->id;
            }

            if (!empty($this->archivos)) {
                foreach ($this->archivos as $archivo) {
                    $nombreOriginal = $archivo->getClientOriginalName();
                    $rutaGuardada = $archivo->store('asignaciones_archivos', 'local');

                    AsignacionArchivo::create([
                        'asignacion_id'  => $asignacion->id,
                        'nombre_archivo' => $nombreOriginal,
                        'ruta_archivo'   => $rutaGuardada,
                    ]);
                }
            }
        });

        $this->cerrarModal();
        session()->flash('mensaje', $this->isEditing ? '¡Asignación actualizada correctamente!' : '¡Nueva transferencia registrada con éxito!');
    }

    public function confirmarEliminar(int $id): void
    {
        $asignacion = Asignacion::findOrFail($id);

        $esUltima = Asignacion::where('tecnologia_id', $asignacion->tecnologia_id)->max('id') === $id;
        if (!$esUltima) {
            session()->flash('mensaje', 'Solo se puede anular la asignación más reciente de un equipo.');
            return;
        }

        $this->asignacionAEliminarId = $id;
        $this->mostrarModalEliminar = true;
    }

    public function eliminar(): void
    {
        if (!$this->asignacionAEliminarId) return;

        $asignacion = Asignacion::with('archivos')->find($this->asignacionAEliminarId);

        if ($asignacion) {
            $tecId = $asignacion->tecnologia_id;

            foreach ($asignacion->archivos as $doc) {
                if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                    Storage::disk('local')->delete($doc->ruta_archivo);
                }
            }
            $asignacion->delete();

            $asignacionPrevia = Asignacion::where('tecnologia_id', $tecId)->latest('id')->first();

            if ($asignacionPrevia) {
                Tecnologia::where('id', $tecId)->update(['estado' => 'Asignado']);
            } else {
                Tecnologia::where('id', $tecId)->update(['estado' => 'Disponible']);
            }

            if ($this->asignacionSeleccionadaId === $this->asignacionAEliminarId) {
                $siguiente = Asignacion::latest('id')->first();
                $this->asignacionSeleccionadaId = $siguiente?->id;
            }

            session()->flash('mensaje', 'Asignación anulada. Se restauró el estado previo del activo.');
        }

        $this->mostrarModalEliminar = false;
        $this->asignacionAEliminarId = null;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetFormulario();
    }

    public function resetFormulario(): void
    {
        $this->reset([
            'tecnologia_id',
            'personal_id',
            'tecnologiaSeleccionada',
            'personalSeleccionado',
            'area_destino_id',
            'archivos',
            'nuevosArchivos',
            'archivosExistentes',
            'searchTecnologia',
            'searchPersonal',
            'isEditing',
            'asignacionId'
        ]);
        $this->fecha_traspaso = now()->toDateString();
        
        $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
        $this->area_origen_id = $areaAlmacen?->id;

        $this->resetErrorBag();
    }
/**
     * Exporta la cadena de custodia y transferencias a CSV compatible con Excel
     */
    public function exportar()
    {
        // 1. Obtener los IDs de las asignaciones más recientes para clasificar Vigente vs Histórico
        $ultimasAsignacionesIds = Asignacion::selectRaw('MAX(id) as id')
            ->groupBy('tecnologia_id')
            ->pluck('id')
            ->toArray();

        // 2. Consulta optimizada aplicando exactamente los mismos filtros de la tabla
        $query = Asignacion::with(['tecnologia', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])
            ->when($this->filtroEstado === 'activas', function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('estado', 'Asignado');
                });
            })
            ->when($this->filtroEstado === 'baja', function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('estado', 'De baja');
                });
            })
            ->when($this->searchHistorial, function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('codigo_vin', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('nombre', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('personal', function ($p) {
                    $p->where('nombre', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('apellido', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('areaDestino', function ($ad) {
                    $ad->where('nombre', 'like', '%' . $this->searchHistorial . '%');
                });
            })
            ->orderBy('id', 'asc');

        $asignaciones = $query->get();

        // 3. Nombre del archivo dinámico
        $filename = now()->format('Y-m-d') . '_transferencias_custodia.csv';

        // 4. Generación por Streaming (Memoria limpia)
        $callback = function () use ($asignaciones, $ultimasAsignacionesIds) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para Excel en español
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezado Institucional
            fputcsv($file, ['REPORTE DE TRANSFERENCIAS Y CADENA DE CUSTODIA DE ACTIVOS'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' | Filtro aplicado: ' . strtoupper($this->filtroEstado)], ';');
            fputcsv($file, [], ';'); // Separador

            // Encabezados de Columnas de Auditoría
            fputcsv($file, [
                'Fecha Traspaso',
                'Código VIN',
                'Equipo / Activo',
                'Marca',
                'Número de Serie',
                'Área Origen',
                'Área Destino',
                'Custodio Receptor',
                'Cargo del Custodio',
                'Condición Custodia',
                'Sustento Documental'
            ], ';');

            // Llenado de Filas
            foreach ($asignaciones as $asig) {
                $esVigente = in_array($asig->id, $ultimasAsignacionesIds);
                $condicion = $esVigente ? 'Custodio Vigente' : 'Histórico (Reasignado)';

                $nombreCustodio = $asig->personal 
                    ? $asig->personal->nombre . ' ' . $asig->personal->apellido 
                    : 'Área general';
                
                $cargoCustodio = $asig->personal 
                    ? $asig->personal->cargo 
                    : 'Sin asignar';

                $docsCount = $asig->archivos->count();
                $sustento = $docsCount > 0 ? "Con Acta ({$docsCount} doc)" : 'Sin Acta adjunta';

                fputcsv($file, [
                    $asig->fecha_traspaso ? Carbon::parse($asig->fecha_traspaso)->format('d/m/Y') : '—',
                    $asig->tecnologia?->codigo_vin ?? '—',
                    $asig->tecnologia?->nombre ?? '—',
                    $asig->tecnologia?->marca ?? '—',
                    $asig->tecnologia?->serie ?? 'S/N',
                    $asig->areaOrigen?->nombre ?? 'ALMACEN',
                    $asig->areaDestino?->nombre ?? '—',
                    $nombreCustodio,
                    $cargoCustodio,
                    $condicion,
                    $sustento
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    // =========================================================================
    // RENDERIZADO REACTIVO (CON FILTRO DE ASIGNACIONES ACTIVAS / BAJAS)
    // =========================================================================
    public function render()
    {
        $activoActualId = ($this->isEditing && $this->asignacionId) 
            ? Asignacion::find($this->asignacionId)?->tecnologia_id 
            : null;

        // 1. Buscador asistido en modal (Solo activos Disponible y Asignado)
        $tecnologiasSugeridas = collect();
        if (strlen(trim($this->searchTecnologia)) >= 2 && !$this->tecnologiaSeleccionada) {
            $tecnologiasSugeridas = Tecnologia::whereDoesntHave('salida')
                ->where(function ($q) use ($activoActualId) {
                    $q->whereIn('estado', ['Disponible', 'Asignado']);
                    if ($activoActualId) {
                        $q->orWhere('id', $activoActualId);
                    }
                })
                ->where(function ($query) {
                    $query->where('nombre', 'like', '%' . $this->searchTecnologia . '%')
                          ->orWhere('codigo_vin', 'like', '%' . $this->searchTecnologia . '%')
                          ->orWhere('serie', 'like', '%' . $this->searchTecnologia . '%')
                          ->orWhere('marca', 'like', '%' . $this->searchTecnologia . '%');
                })
                ->take(6)
                ->get();
        }

        // 2. Personal por Área Destino
        $personalSugerido = collect();
        if ($this->area_destino_id && !$this->personalSeleccionado) {
            $personalSugerido = Personal::where('area_id', $this->area_destino_id)
                ->when(strlen(trim($this->searchPersonal)) > 0, function ($q) {
                    $q->where(function ($query) {
                        $query->where('nombre', 'like', '%' . $this->searchPersonal . '%')
                              ->orWhere('apellido', 'like', '%' . $this->searchPersonal . '%')
                              ->orWhere('cargo', 'like', '%' . $this->searchPersonal . '%');
                    });
                })
                ->get();
        }

        // 3. Consulta de Historial con Filtro Inteligente de Estado
        $historial = Asignacion::with(['tecnologia', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])
            // Filtro de Estado del Activo
            ->when($this->filtroEstado === 'activas', function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('estado', 'Asignado');
                });
            })
            ->when($this->filtroEstado === 'baja', function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('estado', 'De baja');
                });
            })
            // Buscador reactivo
            ->when($this->searchHistorial, function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('codigo_vin', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('nombre', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('personal', function ($p) {
                    $p->where('nombre', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('apellido', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('areaDestino', function ($ad) {
                    $ad->where('nombre', 'like', '%' . $this->searchHistorial . '%');
                });
            })
            ->oldest('id')
            ->paginate(15);

        // 4. Detalle y Cadena de Custodios del Activo
        $detalleAsignacion = null;
        $cadenaCustodios = collect();

        if ($this->asignacionSeleccionadaId) {
            $detalleAsignacion = Asignacion::with(['tecnologia', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])
                ->find($this->asignacionSeleccionadaId);

            if ($detalleAsignacion) {
                $cadenaCustodios = Asignacion::with(['personal', 'areaDestino', 'areaOrigen'])
                    ->where('tecnologia_id', $detalleAsignacion->tecnologia_id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $areas = Area::orderBy('nombre', 'asc')->get();

        // 5. IDs de asignaciones más recientes para blindar la vista
        $ultimasAsignacionesIds = Asignacion::selectRaw('MAX(id) as id')
            ->groupBy('tecnologia_id')
            ->pluck('id')
            ->toArray();
        


        return view('livewire.inventario.trasnferencia-t-e-c', [
            'tecnologiasSugeridas'   => $tecnologiasSugeridas,
            'personalSugerido'       => $personalSugerido,
            'historial'              => $historial,
            'detalleAsignacion'      => $detalleAsignacion,
            'cadenaCustodios'        => $cadenaCustodios,
            'ultimasAsignacionesIds' => $ultimasAsignacionesIds,
            'areas'                  => $areas,
        ]);
    }
}