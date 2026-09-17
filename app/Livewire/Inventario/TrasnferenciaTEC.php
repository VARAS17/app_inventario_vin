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
use App\Models\asignaciones as Asignacion;
use App\Models\asignaciones_archivos as AsignacionArchivo;

class TrasnferenciaTEC extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // Buscadores reactivos
    public string $searchTecnologia = '';
    public string $searchPersonal = '';
    public string $searchHistorial = '';

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

    // Campos del formulario
    public ?int $tecnologia_id = null;
    public ?int $personal_id = null;
    public string $area_origen = 'VIN';
    public string $area_destino = '';
    public string $fecha_traspaso = '';

    // Gestión de Archivos
    public array $nuevosArchivos = []; // Receptor del input file (+)
    public array $archivos = [];       // Archivos temporales acumulados
    public $archivosExistentes = [];   // Archivos guardados en BD durante edición

    // Previsualizador modal de archivos
    public bool $mostrarModalPreview = false;
    public ?string $previewSrc = null;
    public ?string $previewNombre = null;
    public ?string $previewTipo = null; // 'imagen' | 'pdf' | 'otro'

    // Opciones del Enum
    public const AREAS_DESTINO = [
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
            'tecnologia_id'   => 'required|exists:tecnologias,id',
            'personal_id'     => 'nullable|exists:personal,id',
            'area_origen'     => 'required|in:VIN',
            'area_destino'    => 'required|in:' . implode(',', self::AREAS_DESTINO),
            'fecha_traspaso'  => 'required|date',
            'archivos.*'      => 'nullable|file|max:15360', // Máx 15MB por archivo
        ];
    }

    protected $messages = [
        'tecnologia_id.required'  => 'Debe seleccionar un activo tecnológico disponible.',
        'area_destino.required'   => 'Debe seleccionar un área de destino válida.',
        'fecha_traspaso.required' => 'La fecha del traspaso es obligatoria.',
        'archivos.*.max'          => 'Cada archivo adjunto no debe superar los 15MB.',
    ];

    public function mount(): void
    {
        $this->fecha_traspaso = now()->toDateString();

        // Seleccionar por defecto la primera asignación para el panel inferior
        $primera = Asignacion::latest('id')->first();
        if ($primera) {
            $this->asignacionSeleccionadaId = $primera->id;
        }
    }

    // =========================================================================
    // MASTER-DETAIL: SELECCIÓN DE FILA EN TABLA
    // =========================================================================
    public function seleccionarParaDetalle(int $id): void
    {
        $this->asignacionSeleccionadaId = $id;
    }

    // =========================================================================
    // SELECCIÓN DE ACTIVOS Y PERSONAL EN MODAL
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
    // GESTIÓN DE ARCHIVOS CON BOTÓN "+" Y ACUMULACIÓN
    // =========================================================================
    public function updatedNuevosArchivos(): void
    {
        $this->validate([
            'nuevosArchivos.*' => 'file|max:15360',
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
    // PREVISUALIZACIÓN DE ARCHIVOS
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
    // OPERACIONES CRUD (GESTIÓN DE ESTADOS DISPONIBLE / ASIGNADO)
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
        $this->asignacionId = $id;

        $asignacion = Asignacion::with(['tecnologia', 'personal', 'archivos'])->findOrFail($id);

        $this->tecnologia_id = $asignacion->tecnologia_id;
        $this->tecnologiaSeleccionada = $asignacion->tecnologia;

        $this->personal_id = $asignacion->personal_id;
        $this->personalSeleccionado = $asignacion->personal;

        $this->area_origen = $asignacion->area_origen;
        $this->area_destino = $asignacion->area_destino;
        $this->fecha_traspaso = $asignacion->fecha_traspaso;

        $this->archivosExistentes = $asignacion->archivos;

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
                    'tecnologia_id'  => $this->tecnologia_id,
                    'personal_id'    => $this->personal_id,
                    'area_origen'    => $this->area_origen,
                    'area_destino'   => $this->area_destino,
                    'fecha_traspaso' => $this->fecha_traspaso,
                ]);

                // Si se cambió de equipo durante la edición
                if ($antiguoTecnologiaId !== $this->tecnologia_id) {
                    // El activo anterior queda libre en almacén
                    Tecnologia::where('id', $antiguoTecnologiaId)->update(['estado' => 'Disponible']);
                    // El nuevo activo pasa a asignado
                    Tecnologia::where('id', $this->tecnologia_id)->update(['estado' => 'Asignado']);
                }
            } else {
                // Crear nueva asignación
                $asignacion = Asignacion::create([
                    'tecnologia_id'  => $this->tecnologia_id,
                    'personal_id'    => $this->personal_id,
                    'area_origen'    => $this->area_origen,
                    'area_destino'   => $this->area_destino,
                    'fecha_traspaso' => $this->fecha_traspaso,
                ]);

                // El activo pasa automáticamente a 'Asignado'
                Tecnologia::where('id', $this->tecnologia_id)->update(['estado' => 'Asignado']);

                $this->asignacionSeleccionadaId = $asignacion->id;
            }

            // Guardar nuevos archivos en disco local
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
        session()->flash('mensaje', $this->isEditing ? '¡Asignación actualizada correctamente!' : '¡Nueva asignación registrada con éxito!');
    }

    public function confirmarEliminar(int $id): void
    {
        $this->asignacionAEliminarId = $id;
        $this->mostrarModalEliminar = true;
    }

    public function eliminar(): void
    {
        if (!$this->asignacionAEliminarId) return;

        $asignacion = Asignacion::with('archivos')->find($this->asignacionAEliminarId);

        if ($asignacion) {
            // El activo vuelve a estar 'Disponible' en almacén VIN
            if ($asignacion->tecnologia_id) {
                Tecnologia::where('id', $asignacion->tecnologia_id)->update(['estado' => 'Disponible']);
            }

            // Eliminar archivos del disco local
            foreach ($asignacion->archivos as $doc) {
                if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                    Storage::disk('local')->delete($doc->ruta_archivo);
                }
            }
            $asignacion->delete();

            // Reajustar vista detallada inferior
            if ($this->asignacionSeleccionadaId === $this->asignacionAEliminarId) {
                $siguiente = Asignacion::latest('id')->first();
                $this->asignacionSeleccionadaId = $siguiente?->id;
            }

            session()->flash('mensaje', 'Asignación eliminada. El equipo vuelve a estar Disponible.');
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
            'area_destino',
            'archivos',
            'nuevosArchivos',
            'archivosExistentes',
            'searchTecnologia',
            'searchPersonal',
            'isEditing',
            'asignacionId'
        ]);
        $this->fecha_traspaso = now()->toDateString();
        $this->area_origen = 'VIN';
        $this->resetErrorBag();
    }

    // =========================================================================
    // RENDERIZADO REACTIVO
    // =========================================================================
    public function render()
    {
        // Activo asignado actual (por si se edita y se vuelve a buscar el mismo)
        $activoActualId = ($this->isEditing && $this->asignacionId) 
            ? Asignacion::find($this->asignacionId)?->tecnologia_id 
            : null;

        // 1. Solo activos DISPONIBLES (o el activo que ya tiene la asignación en edición)
        $tecnologiasSugeridas = collect();
        if (strlen(trim($this->searchTecnologia)) >= 2 && !$this->tecnologiaSeleccionada) {
            $tecnologiasSugeridas = Tecnologia::whereDoesntHave('salida')
                ->where(function ($q) use ($activoActualId) {
                    $q->where('estado', 'Disponible');
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

        // 2. Personal sugerido
        $personalSugerido = collect();
        if (strlen(trim($this->searchPersonal)) >= 2 && !$this->personalSeleccionado) {
            $personalSugerido = Personal::where('nombre', 'like', '%' . $this->searchPersonal . '%')
                ->orWhere('apellido', 'like', '%' . $this->searchPersonal . '%')
                ->orWhere('cargo', 'like', '%' . $this->searchPersonal . '%')
                ->take(6)
                ->get();
        }

        // 3. Historial (Paginación de 8)
        $historial = Asignacion::with(['tecnologia', 'personal', 'archivos'])
            ->when($this->searchHistorial, function ($q) {
                $q->whereHas('tecnologia', function ($t) {
                    $t->where('codigo_vin', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('nombre', 'like', '%' . $this->searchHistorial . '%');
                })->orWhereHas('personal', function ($p) {
                    $p->where('nombre', 'like', '%' . $this->searchHistorial . '%')
                      ->orWhere('apellido', 'like', '%' . $this->searchHistorial . '%');
                });
            })
            ->oldest('id')
            ->paginate(8);

        // 4. Detalle inferior
        $detalleAsignacion = null;
        if ($this->asignacionSeleccionadaId) {
            $detalleAsignacion = Asignacion::with(['tecnologia', 'personal', 'archivos'])->find($this->asignacionSeleccionadaId);
        }

        return view('livewire.inventario.trasnferencia-t-e-c', [
            'tecnologiasSugeridas' => $tecnologiasSugeridas,
            'personalSugerido'     => $personalSugerido,
            'historial'            => $historial,
            'detalleAsignacion'    => $detalleAsignacion,
            'areasDestino'         => self::AREAS_DESTINO,
        ]);
    }
}