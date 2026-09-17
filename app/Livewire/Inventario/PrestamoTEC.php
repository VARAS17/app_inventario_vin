<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Tecnologia;
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

    // Campos del formulario
    public ?int $tecnologia_id = null;
    public string $area_origen = 'TI';
    public string $area_destino = '';
    public ?string $responsable = null;
    public string $fecha_prestamo = '';
    public string $fecha_devolucion_pactada = '';

    // Gestión de Archivos (Acumulativa)
    public array $nuevosArchivos = []; // Receptor del input file (+)
    public array $archivos = [];       // Archivos temporales acumulados
    public $archivosExistentes = [];   // Archivos en BD durante edición

    // Previsualizador modal de archivos (Local Base64)
    public bool $mostrarModalPreview = false;
    public ?string $previewSrc = null;
    public ?string $previewNombre = null;
    public ?string $previewTipo = null; // 'imagen' | 'pdf' | 'otro'

    // Opciones autorizadas del Enum
    public const AREAS_ORIGEN = [
        'TI',
        'Administracion',
        'Imagen',
        'Mesa de partes',
        'Secretaría',
        'Despacho Vicerrectoral'
    ];

    protected function rules(): array
    {
        return [
            'tecnologia_id'            => 'required|exists:tecnologias,id',
            'area_origen'              => 'required|in:' . implode(',', self::AREAS_ORIGEN),
            'area_destino'             => 'required|string|max:255',
            'responsable'              => 'nullable|string|max:255',
            'fecha_prestamo'           => 'required|date',
            'fecha_devolucion_pactada' => 'required|date|after_or_equal:fecha_prestamo',
            'archivos.*'               => 'nullable|file|max:20480', // Máx 20MB por archivo
        ];
    }

    protected $messages = [
        'tecnologia_id.required'                 => 'Debe seleccionar un activo tecnológico.',
        'area_destino.required'                  => 'El área de destino es obligatoria.',
        'fecha_prestamo.required'                => 'La fecha de préstamo es requerida.',
        'fecha_devolucion_pactada.required'      => 'La fecha límite pactada es requerida.',
        'fecha_devolucion_pactada.after_or_equal'=> 'La fecha pactada no puede ser anterior a la de préstamo.',
        'archivos.*.max'                         => 'Cada archivo adjunto no debe exceder los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_prestamo = Carbon::now()->format('Y-m-d');
        $this->fecha_devolucion_pactada = Carbon::now()->addDays(7)->format('Y-m-d');

        // Seleccionar por defecto el primer préstamo para el panel de inspección
        $primer = Prestamo::latest('id')->first();
        if ($primer) {
            $this->prestamoSeleccionadoId = $primer->id;
        }
    }

    // =========================================================================
    // 1. MASTER-DETAIL: SELECCIÓN Y ALTERNANCIA DE FILAS
    // =========================================================================
    public function seleccionarParaDetalle(int $id): void
    {
        // Clic en la misma fila: deselecciona y expande la tabla al 100%
        if ($this->prestamoSeleccionadoId === $id) {
            $this->prestamoSeleccionadoId = null;
        } else {
            $this->prestamoSeleccionadoId = $id;
        }
    }

    public function cerrarDetalle(): void
    {
        $this->prestamoSeleccionadoId = null;
    }

    // =========================================================================
    // 2. SELECCIÓN ASISTIDA DE ACTIVOS EN MODAL (BUSCADOR VISUAL)
    // =========================================================================
    public function seleccionarTecnologia(int $id): void
    {
        $this->tecnologiaSeleccionada = Tecnologia::find($id);
        if ($this->tecnologiaSeleccionada) {
            $this->tecnologia_id = $this->tecnologiaSeleccionada->id;
            $this->searchTecnologia = '';
        }
    }

    public function deseleccionarTecnologia(): void
    {
        $this->tecnologiaSeleccionada = null;
        $this->tecnologia_id = null;
    }

    // =========================================================================
    // 3. GESTIÓN DINÁMICA DE ARCHIVOS CON BOTÓN (+)
    // =========================================================================
    public function updatedNuevosArchivos(): void
    {
        $this->validate([
            'nuevosArchivos.*' => 'file|max:20480',
        ]);

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
    // 4. PREVISUALIZADOR MODAL DE ARCHIVOS (LOCAL BASE64)
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
    // 5. OPERACIONES CRUD (CREAR, EDITAR, ELIMINAR, DEVOLVER)
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

        $prestamo = Prestamo::with(['tecnologia', 'archivos'])->findOrFail($id);

        $this->tecnologia_id = $prestamo->tecnologia_id;
        $this->tecnologiaSeleccionada = $prestamo->tecnologia;
        $this->area_origen = $prestamo->area_origen;
        $this->area_destino = $prestamo->area_destino;
        $this->responsable = $prestamo->responsable;
        $this->fecha_prestamo = $prestamo->fecha_prestamo;
        $this->fecha_devolucion_pactada = $prestamo->fecha_devolucion_pactada;

        $this->archivosExistentes = $prestamo->archivos;
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
                    'area_origen'              => $this->area_origen,
                    'area_destino'             => $this->area_destino,
                    'responsable'              => !empty($this->responsable) ? trim($this->responsable) : null,
                    'fecha_prestamo'           => $this->fecha_prestamo,
                    'fecha_devolucion_pactada' => $this->fecha_devolucion_pactada,
                ]);

                // Si se cambió de activo durante la edición
                if ($antiguaTecnologiaId !== $this->tecnologia_id) {
                    // El activo anterior recupera su estado previo
                    Tecnologia::where('id', $antiguaTecnologiaId)->update(['estado' => $prestamo->estado_previo]);
                    
                    // El nuevo activo guarda su estado original y pasa a 'Prestado'
                    $nuevaTecnologia = Tecnologia::findOrFail($this->tecnologia_id);
                    $prestamo->update(['estado_previo' => $nuevaTecnologia->estado]);
                    $nuevaTecnologia->update(['estado' => 'Prestado']);
                }
            } else {
                // Nuevo Préstamo
                $tecnologia = Tecnologia::lockForUpdate()->findOrFail($this->tecnologia_id);
                $estadoPrevio = $tecnologia->estado;

                $prestamo = Prestamo::create([
                    'tecnologia_id'            => $this->tecnologia_id,
                    'area_origen'              => $this->area_origen,
                    'area_destino'             => $this->area_destino,
                    'responsable'              => !empty($this->responsable) ? trim($this->responsable) : null,
                    'fecha_prestamo'           => $this->fecha_prestamo,
                    'fecha_devolucion_pactada' => $this->fecha_devolucion_pactada,
                    'fecha_devolucion_real'    => null,
                    'estado_previo'            => $estadoPrevio,
                ]);

                // Activo pasa a 'Prestado'
                $tecnologia->update(['estado' => 'Prestado']);

                $this->prestamoSeleccionadoId = $prestamo->id;
            }

            // Guardar archivos acumulados en almacenamiento LOCAL
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
        session()->flash('mensaje', $this->isEditing ? '¡Préstamo actualizado correctamente!' : '¡Préstamo registrado con éxito!');
    }

    public function devolverActivo(int $prestamoId): void
    {
        DB::transaction(function () use ($prestamoId) {
            $prestamo = Prestamo::with('tecnologia')->findOrFail($prestamoId);

            $prestamo->update([
                'fecha_devolucion_real' => Carbon::now()->format('Y-m-d')
            ]);

            $prestamo->tecnologia->update([
                'estado' => $prestamo->estado_previo
            ]);
        });

        session()->flash('mensaje', 'Activo devuelto y restaurado a su estado original.');
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
                // Si se elimina un préstamo activo, restaurar el activo a su estado previo
                if (is_null($prestamo->fecha_devolucion_real) && $prestamo->tecnologia_id) {
                    Tecnologia::where('id', $prestamo->tecnologia_id)->update(['estado' => $prestamo->estado_previo]);
                }

                // Eliminar archivos del disco local
                foreach ($prestamo->archivos as $doc) {
                    if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                        Storage::disk('local')->delete($doc->ruta_archivo);
                    }
                }

                $prestamo->delete();

                // Reajustar panel de detalle si la fila seleccionada fue eliminada
                if ($this->prestamoSeleccionadoId === $this->prestamoAEliminarId) {
                    $siguiente = Prestamo::latest('id')->first();
                    $this->prestamoSeleccionadoId = $siguiente?->id;
                }
            }
        });

        $this->mostrarModalEliminar = false;
        $this->prestamoAEliminarId = null;
        session()->flash('mensaje', 'Registro de préstamo y sus archivos eliminados del sistema.');
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
            'area_destino',
            'responsable',
            'archivos',
            'nuevosArchivos',
            'archivosExistentes',
            'searchTecnologia',
            'isEditing',
            'prestamoId'
        ]);
        $this->fecha_prestamo = Carbon::now()->format('Y-m-d');
        $this->fecha_devolucion_pactada = Carbon::now()->addDays(7)->format('Y-m-d');
        $this->area_origen = 'TI';
        $this->resetErrorBag();
    }

    // =========================================================================
    // 6. RENDERIZADO REACTIVO (PAGINACIÓN DE 8 Y CÁLCULO DE MORAS)
    // =========================================================================
    public function render()
    {
        // Activo actual del préstamo en edición (para permitir volver a seleccionarlo)
        $activoActualId = ($this->isEditing && $this->prestamoId) 
            ? Prestamo::find($this->prestamoId)?->tecnologia_id 
            : null;

        // 1. Buscador asistido en modal: Solo activos 'Disponible' o 'Asignado' (máx 6 resultados)
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

        // 2. Historial de Préstamos (Paginación estricta de 8)
        $prestamos = Prestamo::with(['tecnologia', 'archivos'])
            ->when($this->searchPrestamo, function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('nombre', 'like', '%' . $this->searchPrestamo . '%')
                      ->orWhere('codigo_vin', 'like', '%' . $this->searchPrestamo . '%');
                })
                ->orWhere('area_destino', 'like', '%' . $this->searchPrestamo . '%')
                ->orWhere('responsable', 'like', '%' . $this->searchPrestamo . '%');
            })
            ->latest('id')
            ->paginate(8);

        // 3. Lógica matemática de días de retraso
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

        // 4. Panel de Inspección Derecho (Fila seleccionada)
        $detallePrestamo = null;
        if ($this->prestamoSeleccionadoId) {
            $detallePrestamo = Prestamo::with(['tecnologia', 'archivos'])->find($this->prestamoSeleccionadoId);
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

        return view('livewire.inventario.prestamo-t-e-c', [
            'prestamos'            => $prestamos,
            'detallePrestamo'      => $detallePrestamo,
            'tecnologiasSugeridas' => $tecnologiasSugeridas,
            'areasOrigen'          => self::AREAS_ORIGEN,
        ]);
    }
}