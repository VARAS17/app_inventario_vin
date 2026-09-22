<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Tecnologia;
use App\Models\Area;
use App\Models\asignaciones as Asignacion;
use App\Models\prestamos as Prestamo;
use App\Models\prestamos_archivos as PrestamoArchivo;

class PrestamoTEC extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // Buscadores reactivos
    public string $searchPrestamo = '';
    public string $searchTecnologia = '';

    // Master-Detail: Fila seleccionada para el panel de inspección derecho
    public ?int $prestamoSeleccionadoId = null;

    // Estado del Formulario / CRUD
    public bool $isEditing = false;
    public ?int $prestamoId = null;
    public bool $mostrarModal = false;
    public bool $mostrarModalEliminar = false;
    public ?int $prestamoAEliminarId = null;

    // Activo seleccionado en el modal
    public ?Tecnologia $tecnologiaSeleccionada = null;

    // Campos del formulario (Salida)
    public ?int $tecnologia_id = null;
    public ?int $area_origen_id = null; // Autocompletado y bloqueado
    public string $area_destino = '';
    public ?string $responsable = null;
    public string $fecha_prestamo = '';
    public string $fecha_devolucion_pactada = '';

    // Campos de Recepción (Recuadro Verde)
    public ?string $fecha_devolucion_real = null;
    public ?string $observacion_devolucion = null;

    // Gestión de Archivos (Acumulativa)
    public array $nuevosArchivos = [];
    public array $archivos = [];
    public $archivosExistentes = [];

    // Previsualizador modal de archivos (Local Base64)
    public bool $mostrarModalPreview = false;
    public ?string $previewSrc = null;
    public ?string $previewNombre = null;
    public ?string $previewTipo = null;

    protected function rules(): array
    {
        return [
            'tecnologia_id'            => 'required|exists:tecnologias,id',
            'area_origen_id'           => 'required|exists:areas,id',
            'area_destino'             => 'required|string|max:255',
            'responsable'              => 'nullable|string|max:255',
            'fecha_prestamo'           => 'required|date',
            'fecha_devolucion_pactada' => 'required|date|after_or_equal:fecha_prestamo',
            'fecha_devolucion_real'    => 'nullable|date|after_or_equal:fecha_prestamo',
            'observacion_devolucion'   => 'nullable|string|max:500',
            'archivos.*'               => 'nullable|file|max:20480',
        ];
    }

    protected $messages = [
        'tecnologia_id.required'                 => 'Debe seleccionar un activo tecnológico.',
        'area_origen_id.required'                => 'El área de origen es obligatoria.',
        'area_destino.required'                  => 'El área de destino es obligatoria.',
        'fecha_prestamo.required'                => 'La fecha de préstamo es requerida.',
        'fecha_devolucion_pactada.required'      => 'La fecha límite pactada es requerida.',
        'fecha_devolucion_pactada.after_or_equal'=> 'La fecha pactada no puede ser anterior a la de préstamo.',
        'fecha_devolucion_real.after_or_equal'   => 'La fecha de devolución no puede ser anterior a la de préstamo.',
        'archivos.*.max'                         => 'Cada archivo adjunto no debe exceder los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_prestamo = Carbon::now()->format('Y-m-d');
        $this->fecha_devolucion_pactada = Carbon::now()->addDays(7)->format('Y-m-d');

        $primer = Prestamo::latest('id')->first();
        if ($primer) {
            $this->prestamoSeleccionadoId = $primer->id;
        }
    }

    // =========================================================================
    // CÁLCULO DE MORA REACTIVO EN TIEMPO REAL (DENTRO DEL MODAL)
    // =========================================================================
    #[Computed]
    public function diagnosticoRetrasoModal(): ?array
    {
        if (!$this->fecha_devolucion_real || !$this->fecha_devolucion_pactada) {
            return null;
        }

        $pactada = Carbon::parse($this->fecha_devolucion_pactada)->startOfDay();
        $real = Carbon::parse($this->fecha_devolucion_real)->startOfDay();

        if ($real->greaterThan($pactada)) {
            $dias = (int) $pactada->diffInDays($real);
            return [
                'es_mora' => true,
                'dias'    => $dias,
                'mensaje' => "⚠ Devolución fuera de plazo: +{$dias} día(s) de retraso respecto a la fecha pactada.",
            ];
        }

        return [
            'es_mora' => false,
            'dias'    => 0,
            'mensaje' => '✔ Devolución conforme dentro del plazo pactado.',
        ];
    }

    // =========================================================================
    // 1. MASTER-DETAIL: SELECCIÓN Y ALTERNANCIA DE FILAS
    // =========================================================================
    public function seleccionarParaDetalle(int $id): void
    {
        $this->prestamoSeleccionadoId = ($this->prestamoSeleccionadoId === $id) ? null : $id;
    }

    public function cerrarDetalle(): void
    {
        $this->prestamoSeleccionadoId = null;
    }

    // =========================================================================
    // 2. SELECCIÓN CON DETECCIÓN AUTOMÁTICA DE ORIGEN
    // =========================================================================
    public function seleccionarTecnologia(int $id): void
    {
        $this->tecnologiaSeleccionada = Tecnologia::find($id);
        if ($this->tecnologiaSeleccionada) {
            $this->tecnologia_id = $this->tecnologiaSeleccionada->id;
            $this->searchTecnologia = '';

            // DETECCIÓN AUTOMÁTICA DE ORIGEN
            if ($this->tecnologiaSeleccionada->estado === 'Disponible') {
                $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
                $this->area_origen_id = $areaAlmacen?->id;
            } elseif ($this->tecnologiaSeleccionada->estado === 'Asignado') {
                $ultimaAsignacion = Asignacion::where('tecnologia_id', $this->tecnologia_id)->latest('id')->first();
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
        $this->area_origen_id = null;
    }

    // =========================================================================
    // 3. GESTIÓN DE ARCHIVOS CON BOTÓN (+)
    // =========================================================================
    public function updatedNuevosArchivos(): void
    {
        $this->validate(['nuevosArchivos.*' => 'file|max:20480']);
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
        $archivo = PrestamoArchivo::find($archivoId);
        if ($archivo) {
            if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                Storage::disk('local')->delete($archivo->ruta_archivo);
            }
            $archivo->delete();
            $this->archivosExistentes = PrestamoArchivo::where('prestamo_id', $this->prestamoId)->get();
        }
    }

    // =========================================================================
    // 4. PREVISUALIZADOR MODAL DE ARCHIVOS (BASE64)
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
        $archivo = PrestamoArchivo::find($archivoId);
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

    public function obtenerFotoBase64(?string $rutaFoto): ?string
    {
        if (!$rutaFoto || !Storage::disk('local')->exists($rutaFoto)) {
            return null;
        }
        $mime = Storage::disk('local')->mimeType($rutaFoto);
        $contenido = Storage::disk('local')->get($rutaFoto);
        return 'data:' . $mime . ';base64,' . base64_encode($contenido);
    }

    // =========================================================================
    // 5. OPERACIONES CRUD (CREAR, EDITAR, GUARDAR CON RECUADRO VERDE)
    // =========================================================================
    public function abrirModal(): void
    {
        $this->resetFormulario();
        $this->isEditing = false;
        $this->mostrarModal = true;
    }

    public function editar(int $id): void
    {
        $this->resetFormulario();
        $this->isEditing = true;
        $this->prestamoId = $id;

        $prestamo = Prestamo::with(['tecnologia', 'archivos', 'areaOrigen'])->findOrFail($id);

        $this->tecnologia_id            = $prestamo->tecnologia_id;
        $this->tecnologiaSeleccionada   = $prestamo->tecnologia;
        $this->area_origen_id           = $prestamo->area_origen_id;
        $this->area_destino             = $prestamo->area_destino;
        $this->responsable              = $prestamo->responsable;
        $this->fecha_prestamo           = $prestamo->fecha_prestamo;
        $this->fecha_devolucion_pactada = $prestamo->fecha_devolucion_pactada;

        // Cargar campos de retorno
        $this->fecha_devolucion_real    = $prestamo->fecha_devolucion_real ? Carbon::parse($prestamo->fecha_devolucion_real)->format('Y-m-d') : null;
        $this->observacion_devolucion   = $prestamo->observacion_devolucion;

        $this->archivosExistentes       = $prestamo->archivos;
        $this->mostrarModal = true;
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->isEditing) {
                $prestamo = Prestamo::findOrFail($this->prestamoId);
                $antiguaTecnologiaId = $prestamo->tecnologia_id;

                $prestamo->update([
                    'tecnologia_id'            => $this->tecnologia_id,
                    'area_origen_id'           => $this->area_origen_id,
                    'area_destino'             => $this->area_destino,
                    'responsable'              => !empty($this->responsable) ? trim($this->responsable) : null,
                    'fecha_prestamo'           => $this->fecha_prestamo,
                    'fecha_devolucion_pactada' => $this->fecha_devolucion_pactada,
                    'fecha_devolucion_real'    => $this->fecha_devolucion_real ?: null,
                    'observacion_devolucion'   => $this->observacion_devolucion ?: null,
                ]);

                // Transición de estados según si se registró la fecha real de devolución
                if ($this->fecha_devolucion_real) {
                    // Si ya retornó, recupera su estado previo (Disponible o Asignado)
                    Tecnologia::where('id', $this->tecnologia_id)->update([
                        'estado' => $prestamo->estado_previo ?: 'Disponible'
                    ]);
                } else {
                    // Si sigue en préstamo
                    Tecnologia::where('id', $this->tecnologia_id)->update(['estado' => 'Prestado']);
                }

                // Si se cambió de activo durante la edición
                if ($antiguaTecnologiaId !== $this->tecnologia_id) {
                    Tecnologia::where('id', $antiguaTecnologiaId)->update(['estado' => $prestamo->estado_previo ?: 'Disponible']);
                }
            } else {
                // Nuevo Préstamo
                $tecnologia = Tecnologia::lockForUpdate()->findOrFail($this->tecnologia_id);
                $estadoPrevio = $tecnologia->estado;

                $prestamo = Prestamo::create([
                    'tecnologia_id'            => $this->tecnologia_id,
                    'area_origen_id'           => $this->area_origen_id,
                    'area_destino'             => $this->area_destino,
                    'responsable'              => !empty($this->responsable) ? trim($this->responsable) : null,
                    'fecha_prestamo'           => $this->fecha_prestamo,
                    'fecha_devolucion_pactada' => $this->fecha_devolucion_pactada,
                    'fecha_devolucion_real'    => $this->fecha_devolucion_real ?: null,
                    'estado_previo'            => $estadoPrevio,
                    'observacion_devolucion'   => $this->observacion_devolucion ?: null,
                ]);

                // Si se creó con fecha de retorno ya incluida, vuelve al estado previo; si no, pasa a 'Prestado'
                $estadoFinal = $this->fecha_devolucion_real ? $estadoPrevio : 'Prestado';
                $tecnologia->update(['estado' => $estadoFinal]);

                $this->prestamoSeleccionadoId = $prestamo->id;
            }

            // Archivos adjuntos
            if (!empty($this->archivos)) {
                foreach ($this->archivos as $archivo) {
                    $nombreOriginal = $archivo->getClientOriginalName();
                    $ruta = $archivo->store('prestamos_archivos', 'local');

                    PrestamoArchivo::create([
                        'prestamo_id'    => $prestamo->id,
                        'nombre_archivo' => $nombreOriginal,
                        'ruta_archivo'   => $ruta,
                    ]);
                }
            }
        });

        $this->cerrarModal();
        session()->flash('mensaje', 'Registro de préstamo guardado y estado del activo actualizado correctamente.');
    }

    public function confirmarEliminar(int $id): void
    {
        $this->prestamoAEliminarId = $id;
        $this->mostrarModalEliminar = true;
    }

    public function eliminar(): void
    {
        if (!$this->prestamoAEliminarId) return;

        DB::transaction(function () {
            $prestamo = Prestamo::with('archivos')->find($this->prestamoAEliminarId);

            if ($prestamo) {
                // Si se elimina un préstamo activo, restaurar al estado previo
                if (is_null($prestamo->fecha_devolucion_real) && $prestamo->tecnologia_id) {
                    Tecnologia::where('id', $prestamo->tecnologia_id)->update(['estado' => $prestamo->estado_previo ?: 'Disponible']);
                }

                foreach ($prestamo->archivos as $doc) {
                    if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                        Storage::disk('local')->delete($doc->ruta_archivo);
                    }
                }

                $prestamo->delete();

                if ($this->prestamoSeleccionadoId === $this->prestamoAEliminarId) {
                    $siguiente = Prestamo::latest('id')->first();
                    $this->prestamoSeleccionadoId = $siguiente?->id;
                }
            }
        });

        $this->mostrarModalEliminar = false;
        $this->prestamoAEliminarId = null;
        session()->flash('mensaje', 'Registro de préstamo eliminado y activo restaurado.');
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
            'tecnologiaSeleccionada',
            'area_origen_id',
            'area_destino',
            'responsable',
            'fecha_devolucion_real',
            'observacion_devolucion',
            'archivos',
            'nuevosArchivos',
            'archivosExistentes',
            'searchTecnologia',
            'isEditing',
            'prestamoId'
        ]);
        $this->fecha_prestamo = Carbon::now()->format('Y-m-d');
        $this->fecha_devolucion_pactada = Carbon::now()->addDays(7)->format('Y-m-d');
        $this->resetErrorBag();
    }
    /**
     * Exporta el historial de préstamos, control de plazos y moras a CSV/Excel
     */
    public function exportar()
    {
        // 1. Consulta optimizada respetando los mismos filtros de búsqueda
        $query = Prestamo::with(['tecnologia', 'archivos', 'areaOrigen'])
            ->when($this->searchPrestamo, function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('nombre', 'like', '%' . $this->searchPrestamo . '%')
                      ->orWhere('codigo_vin', 'like', '%' . $this->searchPrestamo . '%');
                })
                ->orWhere('area_destino', 'like', '%' . $this->searchPrestamo . '%')
                ->orWhere('responsable', 'like', '%' . $this->searchPrestamo . '%')
                ->orWhereHas('areaOrigen', function ($ao) {
                    $ao->where('nombre', 'like', '%' . $this->searchPrestamo . '%');
                });
            })
            ->latest('id');

        $prestamos = $query->get();

        // 2. Nombre de archivo con fecha
        $filename = now()->format('Y-m-d') . '_control_prestamos.csv';

        // 3. Streaming nativo de memoria limpia con BOM UTF-8
        $callback = function () use ($prestamos) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para Excel en español
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados Institucionales
            fputcsv($file, ['REPORTE DE CONTROL DE PRESTAMOS TECNOLOGICOS Y MORAS'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' | Sistema Patrimonial'], ';');
            fputcsv($file, [], ';'); // Separador

            // Columnas de Auditoría de Préstamos
            fputcsv($file, [
                'Código VIN',
                'Equipo / Activo',
                'Número de Serie',
                'Área Origen',
                'Destino del Préstamo',
                'Responsable Receptor',
                'Fecha Préstamo',
                'Fecha Límite Pactada',
                'Fecha Real Devolución',
                'Situación / Diagnóstico de Mora',
                'Observaciones de Retorno',
                'Sustento Documental'
            ], ';');

            $hoy = Carbon::today();

            // Llenado de Filas con Cálculo Matemático de Plazos
            foreach ($prestamos as $p) {
                $pactada = Carbon::parse($p->fecha_devolucion_pactada)->startOfDay();

                // Lógica de Diagnóstico de Retraso en tiempo real
                if (is_null($p->fecha_devolucion_real)) {
                    if ($hoy->greaterThan($pactada)) {
                        $dias = (int) $pactada->diffInDays($hoy);
                        $situacion = "VENCIDO - En Mora (+{$dias} días)";
                    } else {
                        $situacion = "En Curso (Dentro del Plazo)";
                    }
                    $fechaRetornoTexto = 'Pendiente de Retorno';
                } else {
                    $real = Carbon::parse($p->fecha_devolucion_real)->startOfDay();
                    if ($real->greaterThan($pactada)) {
                        $dias = (int) $pactada->diffInDays($real);
                        $situacion = "Devuelto con Retraso (+{$dias} días)";
                    } else {
                        $situacion = "Devuelto Conforme (A Tiempo)";
                    }
                    $fechaRetornoTexto = $real->format('d/m/Y');
                }

                $docsCount = $p->archivos->count();
                $sustento = $docsCount > 0 ? "Con Acta ({$docsCount} doc)" : 'Sin Acta adjunta';

                fputcsv($file, [
                    $p->tecnologia?->codigo_vin ?? '—',
                    $p->tecnologia?->nombre ?? '—',
                    $p->tecnologia?->serie ?? 'S/N',
                    $p->areaOrigen?->nombre ?? 'ALMACEN',
                    $p->area_destino,
                    $p->responsable ?? 'No indicado',
                    $p->fecha_prestamo ? Carbon::parse($p->fecha_prestamo)->format('d/m/Y') : '—',
                    $pactada->format('d/m/Y'),
                    $fechaRetornoTexto,
                    $situacion,
                    $p->observacion_devolucion ?? 'Sin observaciones registradas',
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
    // 6. RENDERIZADO REACTIVO (CÁLCULO DE MORAS)
    // =========================================================================
    public function render()
    {
        $activoActualId = ($this->isEditing && $this->prestamoId) 
            ? Prestamo::find($this->prestamoId)?->tecnologia_id 
            : null;

        // 1. Activos disponibles o asignados
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

        // 2. Historial de Préstamos
        $prestamos = Prestamo::with(['tecnologia', 'archivos', 'areaOrigen'])
            ->when($this->searchPrestamo, function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('nombre', 'like', '%' . $this->searchPrestamo . '%')
                      ->orWhere('codigo_vin', 'like', '%' . $this->searchPrestamo . '%');
                })
                ->orWhere('area_destino', 'like', '%' . $this->searchPrestamo . '%')
                ->orWhere('responsable', 'like', '%' . $this->searchPrestamo . '%')
                ->orWhereHas('areaOrigen', function ($ao) {
                    $ao->where('nombre', 'like', '%' . $this->searchPrestamo . '%');
                });
            })
            ->latest('id')
            ->paginate(8);

        // 3. Lógica matemática de retrasos
        $hoy = Carbon::today();
        foreach ($prestamos as $item) {
            $pactada = Carbon::parse($item->fecha_devolucion_pactada)->startOfDay();

            if (is_null($item->fecha_devolucion_real)) {
                $item->dias_retraso = $hoy->greaterThan($pactada) ? (int) $pactada->diffInDays($hoy) : 0;
                $item->esta_vencido = $hoy->greaterThan($pactada);
                $item->entregado_con_retraso = false;
            } else {
                $real = Carbon::parse($item->fecha_devolucion_real)->startOfDay();
                $item->dias_retraso = $real->greaterThan($pactada) ? (int) $pactada->diffInDays($real) : 0;
                $item->entregado_con_retraso = $real->greaterThan($pactada);
                $item->esta_vencido = false;
            }
        }

        // 4. Panel lateral de inspección
        $detallePrestamo = null;
        if ($this->prestamoSeleccionadoId) {
            $detallePrestamo = Prestamo::with(['tecnologia', 'archivos', 'areaOrigen'])->find($this->prestamoSeleccionadoId);
            if ($detallePrestamo) {
                $pactadaDetalle = Carbon::parse($detallePrestamo->fecha_devolucion_pactada)->startOfDay();
                if (is_null($detallePrestamo->fecha_devolucion_real)) {
                    $detallePrestamo->dias_retraso = $hoy->greaterThan($pactadaDetalle) ? (int) $pactadaDetalle->diffInDays($hoy) : 0;
                    $detallePrestamo->esta_vencido = $hoy->greaterThan($pactadaDetalle);
                    $detallePrestamo->entregado_con_retraso = false;
                } else {
                    $realDetalle = Carbon::parse($detallePrestamo->fecha_devolucion_real)->startOfDay();
                    $detallePrestamo->dias_retraso = $realDetalle->greaterThan($pactadaDetalle) ? (int) $pactadaDetalle->diffInDays($realDetalle) : 0;
                    $detallePrestamo->entregado_con_retraso = $realDetalle->greaterThan($pactadaDetalle);
                    $detallePrestamo->esta_vencido = false;
                }
            }
        }

        $areas = Area::orderBy('nombre', 'asc')->get();

        return view('livewire.inventario.prestamo-t-e-c', [
            'prestamos'            => $prestamos,
            'detallePrestamo'      => $detallePrestamo,
            'tecnologiasSugeridas' => $tecnologiasSugeridas,
            'areas'                => $areas,
        ]);
    }
}