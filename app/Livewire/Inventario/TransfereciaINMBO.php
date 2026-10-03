<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Mobiliario;
use App\Models\Personal;
use App\Models\Area;
use App\Models\MobiliarioAsignacion as Asignacion;
use App\Models\MobiliarioAsignacionArchivo as AsignacionArchivo;
use Carbon\Carbon;

class TransfereciaINMBO extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // Buscadores reactivos y Filtro de Estado
    public string $searchMobiliario = '';
    public string $searchPersonal = '';
    public string $searchHistorial = '';
    public string $filtroEstado = 'activas'; // 'activas' | 'baja' | 'todas'
    protected int $perPage = 15;

    // Estado del Formulario / Modal
    public bool $isEditing = false;
    public ?int $asignacionId = null;
    public bool $mostrarModal = false;
    public bool $mostrarModalEliminar = false;
    public ?int $asignacionAEliminarId = null;

    // Fila seleccionada para el detalle inferior (Master-Detail)
    public ?int $asignacionSeleccionadaId = null;

    // Entidades seleccionadas en formulario
    public ?Mobiliario $mobiliarioSeleccionado = null;
    public ?Personal $personalSeleccionado = null;

    // Campos del formulario
    public ?int $mobiliario_id = null;
    public ?int $personal_id = null; // Opcional (null para áreas comunes)
    public ?int $area_origen_id = null;
    public ?int $area_destino_id = null;
    public string $fecha_traspaso = '';

    // Gestión de Archivos Incrementales (+)
    public array $nuevosArchivos = [];
    public array $archivos = [];
    public $archivosExistentes = [];

    // Previsualizador modal de archivos (Base64)
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
            'mobiliario_id'   => 'required|exists:mobiliarios,id',
            'personal_id'     => 'nullable|exists:personal,id', // Opcional para espacios comunes
            'area_origen_id'  => 'nullable|exists:areas,id',
            'area_destino_id' => 'required|exists:areas,id',
            'fecha_traspaso'  => 'required|date',
            'archivos.*'      => 'nullable|file|max:15360', // Máx 15MB
        ];
    }

    protected $messages = [
        'mobiliario_id.required'   => 'Debe seleccionar un mueble para transferir.',
        'area_destino_id.required' => 'Debe seleccionar el área de destino.',
        'area_destino_id.exists'   => 'El área de destino seleccionada no es válida.',
        'fecha_traspaso.required'  => 'La fecha de transferencia es obligatoria.',
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
        // Si cambia de área y el custodio elegido no pertenece a esa área, se deselecciona
        if ($this->personalSeleccionado && $this->personalSeleccionado->area_id != $this->area_destino_id) {
            $this->deseleccionarPersonal();
        }
        $this->searchPersonal = '';
    }

    public function seleccionarParaDetalle(int $id): void
    {
        $this->asignacionSeleccionadaId = ($this->asignacionSeleccionadaId === $id) ? null : $id;
    }

    // =========================================================================
    // SELECCIÓN CON DETECCIÓN INTELIGENTE DE ORIGEN
    // =========================================================================
    public function seleccionarMobiliario(int $id): void
    {
        $this->mobiliarioSeleccionado = Mobiliario::find($id);
        if ($this->mobiliarioSeleccionado) {
            $this->mobiliario_id = $this->mobiliarioSeleccionado->id;
            $this->searchMobiliario = '';

            // DETECCIÓN AUTOMÁTICA DE PROCEDENCIA
            if ($this->mobiliarioSeleccionado->estado === 'Disponible') {
                $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
                $this->area_origen_id = $areaAlmacen?->id;
            } elseif ($this->mobiliarioSeleccionado->estado === 'Asignado') {
                $ultimaAsignacion = Asignacion::where('mobiliario_id', $this->mobiliario_id)
                    ->latest('id')
                    ->first();

                if ($ultimaAsignacion && $ultimaAsignacion->area_destino_id) {
                    $this->area_origen_id = $ultimaAsignacion->area_destino_id;
                }
            }
        }
    }

    public function deseleccionarMobiliario(): void
    {
        $this->mobiliarioSeleccionado = null;
        $this->mobiliario_id = null;
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
    // GESTIÓN DE ARCHIVOS CON BOTÓN (+)
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
        $asignacion = Asignacion::with(['mobiliario', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])->findOrFail($id);

        // Bloqueo: Solo se puede editar la última asignación activa
        $esUltima = Asignacion::where('mobiliario_id', $asignacion->mobiliario_id)->max('id') === $id;
        if (!$esUltima) {
            session()->flash('mensaje', 'Este registro es histórico. Solo se permite editar la transferencia vigente.');
            return;
        }

        $this->resetFormulario();
        $this->isEditing = true;
        $this->asignacionId = $id;

        $this->mobiliario_id          = $asignacion->mobiliario_id;
        $this->mobiliarioSeleccionado = $asignacion->mobiliario;
        $this->personal_id            = $asignacion->personal_id;
        $this->personalSeleccionado   = $asignacion->personal;
        $this->area_origen_id         = $asignacion->area_origen_id;
        $this->area_destino_id        = $asignacion->area_destino_id;
        $this->fecha_traspaso         = $asignacion->fecha_traspaso ? $asignacion->fecha_traspaso->format('Y-m-d') : '';
        $this->archivosExistentes     = $asignacion->archivos;

        $this->mostrarModal = true;
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->isEditing) {
                $asignacion = Asignacion::findOrFail($this->asignacionId);
                $antiguoMobiliarioId = $asignacion->mobiliario_id;

                $asignacion->update([
                    'mobiliario_id'   => $this->mobiliario_id,
                    'personal_id'     => $this->personal_id,
                    'area_origen_id'  => $this->area_origen_id,
                    'area_destino_id' => $this->area_destino_id,
                    'fecha_traspaso'  => $this->fecha_traspaso,
                ]);

                if ($antiguoMobiliarioId !== $this->mobiliario_id) {
                    Mobiliario::where('id', $antiguoMobiliarioId)->update(['estado' => 'Disponible']);
                    Mobiliario::where('id', $this->mobiliario_id)->update(['estado' => 'Asignado']);
                }
            } else {
                $asignacion = Asignacion::create([
                    'mobiliario_id'   => $this->mobiliario_id,
                    'personal_id'     => $this->personal_id,
                    'area_origen_id'  => $this->area_origen_id,
                    'area_destino_id' => $this->area_destino_id,
                    'fecha_traspaso'  => $this->fecha_traspaso,
                ]);

                Mobiliario::where('id', $this->mobiliario_id)->update(['estado' => 'Asignado']);
                $this->asignacionSeleccionadaId = $asignacion->id;
            }

            // Guardar archivos físicos de evidencias
            if (!empty($this->archivos)) {
                foreach ($this->archivos as $archivo) {
                    $nombreOriginal = $archivo->getClientOriginalName();
                    $rutaGuardada = $archivo->store('mobiliarios_asignaciones_archivos', 'local');

                    AsignacionArchivo::create([
                        'asignacion_id'  => $asignacion->id,
                        'nombre_archivo' => $nombreOriginal,
                        'ruta_archivo'   => $rutaGuardada,
                    ]);
                }
            }
        });

        $this->cerrarModal();
        session()->flash('mensaje', $this->isEditing ? '¡Transferencia actualizada correctamente!' : '¡Nueva transferencia registrada con éxito!');
    }

    public function confirmarEliminar(int $id): void
    {
        $asignacion = Asignacion::findOrFail($id);

        $esUltima = Asignacion::where('mobiliario_id', $asignacion->mobiliario_id)->max('id') === $id;
        if (!$esUltima) {
            session()->flash('mensaje', 'Solo se puede anular la transferencia más reciente de un mueble.');
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
            $mobId = $asignacion->mobiliario_id;

            // Borrar archivos físicos
            foreach ($asignacion->archivos as $doc) {
                if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                    Storage::disk('local')->delete($doc->ruta_archivo);
                }
            }
            $asignacion->delete();

            // Rollback: Si tenía transferencias previas sigue Asignado, si no, vuelve a Disponible
            $asignacionPrevia = Asignacion::where('mobiliario_id', $mobId)->latest('id')->first();

            if ($asignacionPrevia) {
                Mobiliario::where('id', $mobId)->update(['estado' => 'Asignado']);
            } else {
                Mobiliario::where('id', $mobId)->update(['estado' => 'Disponible']);
            }

            if ($this->asignacionSeleccionadaId === $this->asignacionAEliminarId) {
                $this->asignacionSeleccionadaId = Asignacion::latest('id')->first()?->id;
            }

            session()->flash('mensaje', 'Transferencia anulada. Se restauró el estado y ubicación previa del mueble.');
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
            'mobiliario_id',
            'personal_id',
            'mobiliarioSeleccionado',
            'personalSeleccionado',
            'area_destino_id',
            'archivos',
            'nuevosArchivos',
            'archivosExistentes',
            'searchMobiliario',
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
     * Exporta la cadena de custodia y transferencias a CSV
     */
    public function exportar()
    {
        $ultimasAsignacionesIds = Asignacion::selectRaw('MAX(id) as id')
            ->groupBy('mobiliario_id')
            ->pluck('id')
            ->toArray();

        $query = Asignacion::with(['mobiliario', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])
            ->when($this->filtroEstado === 'activas', function ($q) {
                $q->whereHas('mobiliario', function ($m) {
                    $m->where('estado', 'Asignado');
                });
            })
            ->when($this->filtroEstado === 'baja', function ($q) {
                $q->whereHas('mobiliario', function ($m) {
                    $m->where('estado', 'De baja');
                });
            })
            ->when($this->searchHistorial, function ($q) {
                $q->whereHas('mobiliario', function ($m) {
                    $m->where('codigo_vin', 'like', '%' . $this->searchHistorial . '%')
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
        $filename = now()->format('Y-m-d') . '_transferencias_mobiliario.csv';

        $callback = function () use ($asignaciones, $ultimasAsignacionesIds) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($file, ['REPORTE DE TRANSFERENCIAS Y CADENA DE CUSTODIA DE MOBILIARIO'], ';');
            fputcsv($file, ['Sistema Patrimonial VIN UNT - Fecha: ' . now()->format('d/m/Y H:i:s')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, [
                'Fecha Traspaso',
                'Código Único',
                'Nombre del Mueble',
                'Área Origen',
                'Área Destino',
                'Custodio Receptor',
                'Condición',
                'Evidencias Adjuntas'
            ], ';');

            foreach ($asignaciones as $asig) {
                $esVigente = in_array($asig->id, $ultimasAsignacionesIds);
                $condicion = $esVigente ? 'Custodio Vigente' : 'Histórico (Reasignado)';
                $nombreCustodio = $asig->personal 
                    ? "{$asig->personal->nombre} {$asig->personal->apellido}" 
                    : 'Uso Común (Área General)';

                $docsCount = $asig->archivos->count();
                $sustento = $docsCount > 0 ? "Con Evidencias ({$docsCount})" : 'Sin archivos';

                fputcsv($file, [
                    $asig->fecha_traspaso ? Carbon::parse($asig->fecha_traspaso)->format('d/m/Y') : '—',
                    $asig->mobiliario?->codigo_vin ?? '—',
                    $asig->mobiliario?->nombre ?? '—',
                    $asig->areaOrigen?->nombre ?? 'ALMACEN',
                    $asig->areaDestino?->nombre ?? '—',
                    $nombreCustodio,
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

    public function render()
    {
        $activoActualId = ($this->isEditing && $this->asignacionId) 
            ? Asignacion::find($this->asignacionId)?->mobiliario_id 
            : null;

        // 1. Buscador asistido en modal (Solo muebles Disponibles y Asignados)
        $mobiliariosSugeridos = collect();
        if (strlen(trim($this->searchMobiliario)) >= 2 && !$this->mobiliarioSeleccionado) {
            $mobiliariosSugeridos = Mobiliario::whereDoesntHave('salida')
                ->where(function ($q) use ($activoActualId) {
                    $q->whereIn('estado', ['Disponible', 'Asignado']);
                    if ($activoActualId) {
                        $q->orWhere('id', $activoActualId);
                    }
                })
                ->where(function ($query) {
                    $query->where('nombre', 'like', '%' . $this->searchMobiliario . '%')
                          ->orWhere('codigo_vin', 'like', '%' . $this->searchMobiliario . '%')
                          ->orWhere('codigo_inventario_unt', 'like', '%' . $this->searchMobiliario . '%');
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
                              ->orWhere('apellido', 'like', '%' . $this->searchPersonal . '%');
                    });
                })
                ->get();
        }

        // 3. Consulta de Historial
        $historial = Asignacion::with(['mobiliario', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])
            ->when($this->filtroEstado === 'activas', function ($q) {
                $q->whereHas('mobiliario', function ($m) {
                    $m->where('estado', 'Asignado');
                });
            })
            ->when($this->filtroEstado === 'baja', function ($q) {
                $q->whereHas('mobiliario', function ($m) {
                    $m->where('estado', 'De baja');
                });
            })
            ->when($this->searchHistorial, function ($q) {
                $q->whereHas('mobiliario', function ($m) {
                    $m->where('codigo_vin', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('nombre', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('personal', function ($p) {
                    $p->where('nombre', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('apellido', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('areaDestino', function ($ad) {
                    $ad->where('nombre', 'like', '%' . $this->searchHistorial . '%');
                });
            })
            ->latest('id')
            ->paginate($this->perPage);

        // 4. Detalle y Cadena de Custodios del Mueble
        $detalleAsignacion = null;
        $cadenaCustodios = collect();

        if ($this->asignacionSeleccionadaId) {
            $detalleAsignacion = Asignacion::with(['mobiliario', 'personal', 'archivos', 'areaOrigen', 'areaDestino'])
                ->find($this->asignacionSeleccionadaId);

            if ($detalleAsignacion) {
                $cadenaCustodios = Asignacion::with(['personal', 'areaDestino', 'areaOrigen'])
                    ->where('mobiliario_id', $detalleAsignacion->mobiliario_id)
                    ->orderBy('id', 'desc')
                    ->get();
            }
        }

        $areas = Area::orderBy('nombre', 'asc')->get();

        // 5. IDs de asignaciones más recientes para blindar la vista
        $ultimasAsignacionesIds = Asignacion::selectRaw('MAX(id) as id')
            ->groupBy('mobiliario_id')
            ->pluck('id')
            ->toArray();

        return view('livewire.inventario.transferecia-i-n-m-b-o', [
            'mobiliariosSugeridos'   => $mobiliariosSugeridos,
            'personalSugerido'       => $personalSugerido,
            'historial'              => $historial,
            'detalleAsignacion'      => $detalleAsignacion,
            'cadenaCustodios'        => $cadenaCustodios,
            'ultimasAsignacionesIds' => $ultimasAsignacionesIds,
            'areas'                  => $areas,
        ]);
    }
}