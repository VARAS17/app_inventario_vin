<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Tecnologia;
use App\Models\mantenimiento;
use App\Models\mantenimiento_archivos;

class MantenimientoTEC extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // ==========================================
    // 1. BUSCADOR Y PAGINACIÓN ESTRICTA (8)
    // ==========================================
    public string $search = '';
    protected int $perPage = 8;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // 2. PANEL MASTER-DETAIL COLAPSABLE
    // ==========================================
    public ?int $selectedMantenimientoId = null;

    public function toggleSelect(int $id): void
    {
        // Si hace clic en la misma fila se deselecciona; si no, se selecciona la nueva
        $this->selectedMantenimientoId = ($this->selectedMantenimientoId === $id) ? null : $id;
    }

    public function cerrarDetalle(): void
    {
        $this->selectedMantenimientoId = null;
    }

    // ==========================================
    // 3. ESTADO DE MODALES
    // ==========================================
    public bool $modalFormulario = false;
    public bool $modalEliminar = false;
    public bool $modalPreview = false;

    // ==========================================
    // 4. CAMPOS DEL FORMULARIO (CREAR / EDITAR)
    // ==========================================
    public ?int $mantenimiento_id = null; // null = Nuevo registro | int = Editar
    public ?int $tecnologia_id = null;
    public ?Tecnologia $tecnologiaSeleccionada = null;
    public string $searchTecnologia = ''; // Buscador asistido en modal

    public string $area_origen = '';
    public string $fecha_envio = '';
    public ?string $fecha_ingreso = null;
    public string $motivo = '';

    // Gestión de Archivos Incrementales (+)
    public $tempFile; // Buffer temporal del botón "+"
    public array $archivosNuevos = []; // Archivos que se van sumando
    public $archivosExistentes = []; // Archivos ya guardados en BD (al editar)

    public array $areas = [
        'TI',
        'Administracion',
        'Imagen',
        'Mesa de partes',
        'Secretaría',
        'Despacho Vicerrectoral'
    ];

    // ==========================================
    // 5. VARIABLES DE ELIMINACIÓN Y PREVIEW
    // ==========================================
    public ?int $mantenimientoIdAEliminar = null;
    
    // Visor Integrado de Archivos
    public ?string $previewUrl = null;
    public ?string $previewType = null; // 'image', 'pdf' o 'other'
    public ?string $previewName = null;

    // ==========================================
    // VALIDACIONES
    // ==========================================
    protected function rules(): array
    {
        return [
            'tecnologia_id'   => 'required|exists:tecnologias,id',
            'area_origen'     => 'required|in:' . implode(',', $this->areas),
            'fecha_envio'     => 'required|date',
            'fecha_ingreso'   => 'nullable|date|after_or_equal:fecha_envio',
            'motivo'          => 'required|string|max:500',
            'archivosNuevos.*'=> 'nullable|file|max:20480', // Máximo 20MB por archivo
        ];
    }

    protected $messages = [
        'tecnologia_id.required'      => 'Debe vincular un activo tecnológico.',
        'area_origen.required'        => 'El área de origen es requerida.',
        'fecha_envio.required'        => 'Indique la fecha de envío.',
        'fecha_ingreso.after_or_equal'=> 'La fecha de retorno no puede ser menor al envío.',
        'motivo.required'             => 'Debe especificar el motivo del mantenimiento.',
        'archivosNuevos.*.max'        => 'El archivo no debe exceder los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_envio = now()->format('Y-m-d');
    }

    // ==========================================
    // FLUJO: BUSCADOR ASISTIDO DE ACTIVOS
    // ==========================================
    #[Computed]
    public function tecnologiasEncontradas()
    {
        if (strlen(trim($this->searchTecnologia)) < 1) {
            return collect();
        }

        return Tecnologia::where('codigo_vin', 'like', "%{$this->searchTecnologia}%")
            ->orWhere('nombre', 'like', "%{$this->searchTecnologia}%")
            ->orWhere('serie', 'like', "%{$this->searchTecnologia}%")
            ->limit(5)
            ->get();
    }

    public function seleccionarTecnologia(int $id): void
    {
        $this->tecnologia_id = $id;
        $this->tecnologiaSeleccionada = Tecnologia::find($id);
        $this->searchTecnologia = '';
    }

    public function cambiarTecnologia(): void
    {
        $this->tecnologia_id = null;
        $this->tecnologiaSeleccionada = null;
        $this->searchTecnologia = '';
    }

    // ==========================================
    // FLUJO: ARCHIVOS INCREMENTALES CON EL BOTÓN "+"
    // ==========================================
    public function updatedTempFile(): void
    {
        $this->validate([
            'tempFile' => 'file|max:20480'
        ]);

        // Se agrega sin sobreescribir los existentes
        $this->archivosNuevos[] = $this->tempFile;
        $this->reset('tempFile');
    }

    public function eliminarArchivoNuevo(int $index): void
    {
        if (isset($this->archivosNuevos[$index])) {
            unset($this->archivosNuevos[$index]);
            $this->archivosNuevos = array_values($this->archivosNuevos);
        }
    }

    public function eliminarArchivoExistente(int $id): void
    {
        $archivo = mantenimiento_archivos::find($id);
        if ($archivo) {
            // Borrado del disco local
            if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                Storage::disk('local')->delete($archivo->ruta_archivo);
            }
            $archivo->delete();
            $this->archivosExistentes = mantenimiento_archivos::where('mantenimiento_id', $this->mantenimiento_id)->get();
        }
    }

    // ==========================================
    // FLUJO: CREACIÓN / EDICIÓN (MODAL)
    // ==========================================
    public function abrirModalCrear(): void
    {
        $this->resetFormulario();
        $this->modalFormulario = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetFormulario();
        $mantenimiento = mantenimiento::with('archivos')->findOrFail($id);

        $this->mantenimiento_id = $mantenimiento->id;
        $this->tecnologia_id = $mantenimiento->tecnologia_id;
        $this->tecnologiaSeleccionada = Tecnologia::find($mantenimiento->tecnologia_id);
        $this->area_origen = $mantenimiento->area_origen;
        $this->fecha_envio = $mantenimiento->fecha_envio;
        $this->fecha_ingreso = $mantenimiento->fecha_ingreso;
        $this->motivo = $mantenimiento->motivo;
        $this->archivosExistentes = $mantenimiento->archivos;

        $this->modalFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Guardar o Actualizar Cabecera de Mantenimiento
            $mantenimiento = mantenimiento::updateOrCreate(
                ['id' => $this->mantenimiento_id],
                [
                    'tecnologia_id' => $this->tecnologia_id,
                    'area_origen'   => $this->area_origen,
                    'fecha_envio'   => $this->fecha_envio,
                    'fecha_ingreso' => $this->fecha_ingreso ?: null,
                    'motivo'        => $this->motivo,
                ]
            );

            // 2. Guardar físicamente los archivos nuevos en storage/app/mantenimientos
            foreach ($this->archivosNuevos as $archivo) {
                $ruta = $archivo->store('mantenimientos', 'local');

                mantenimiento_archivos::create([
                    'mantenimiento_id' => $mantenimiento->id,
                    'nombre_archivo'   => $archivo->getClientOriginalName(),
                    'ruta_archivo'     => $ruta,
                ]);
            }

            // 3. Si no tiene fecha de retorno, el bien pasa a 'En Mantenimiento'
            // Si ya tiene fecha de ingreso (retornó), puede volver a 'Disponible'
            $nuevoEstado = $this->fecha_ingreso ? 'Disponible' : 'En Mantenimiento';
            $this->tecnologiaSeleccionada->update(['estado' => $nuevoEstado]);

            // Mantener seleccionado el registro recién creado/editado en la tabla
            $this->selectedMantenimientoId = $mantenimiento->id;
        });

        $this->modalFormulario = false;
        $this->resetFormulario();
        session()->flash('success', 'Registro de mantenimiento procesado correctamente.');
    }

    public function resetFormulario(): void
    {
        $this->reset([
            'mantenimiento_id',
            'tecnologia_id',
            'tecnologiaSeleccionada',
            'searchTecnologia',
            'area_origen',
            'fecha_ingreso',
            'motivo',
            'tempFile',
            'archivosNuevos',
            'archivosExistentes'
        ]);
        $this->fecha_envio = now()->format('Y-m-d');
        $this->resetErrorBag();
    }

    // ==========================================
    // FLUJO: CONFIRMACIÓN Y ELIMINACIÓN FÍSICA
    // ==========================================
    public function confirmarEliminar(int $id): void
    {
        $this->mantenimientoIdAEliminar = $id;
        $this->modalEliminar = true;
    }

    public function eliminarMantenimiento(): void
    {
        if (!$this->mantenimientoIdAEliminar) return;

        DB::transaction(function () {
            $mantenimiento = mantenimiento::with('archivos', 'tecnologia')->findOrFail($this->mantenimientoIdAEliminar);

            // 1. Eliminación física en el disco local para no dejar basura
            foreach ($mantenimiento->archivos as $archivo) {
                if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                    Storage::disk('local')->delete($archivo->ruta_archivo);
                }
            }

            // 2. Devolver el activo a Disponible si estaba en mantenimiento
            if ($mantenimiento->tecnologia && $mantenimiento->tecnologia->estado === 'En Mantenimiento') {
                $mantenimiento->tecnologia->update(['estado' => 'Disponible']);
            }

            // 3. Eliminar de SQLite
            $mantenimiento->delete();
        });

        // Si estaba seleccionado en la columna de inspección, cerrarlo
        if ($this->selectedMantenimientoId === $this->mantenimientoIdAEliminar) {
            $this->selectedMantenimientoId = null;
        }

        $this->modalEliminar = false;
        $this->mantenimientoIdAEliminar = null;
        session()->flash('success', 'Mantenimiento y sus archivos locales eliminados correctamente.');
    }

    // ==========================================
    // FLUJO: VISOR INTEGRADO (PREVIEWER BASE64)
    // ==========================================
    public function abrirPrevisualizador(int $archivoId): void
    {
        $archivo = mantenimiento_archivos::findOrFail($archivoId);

        if (!Storage::disk('local')->exists($archivo->ruta_archivo)) {
            session()->flash('error', 'El archivo no existe físicamente en el disco local.');
            return;
        }

        $mime = Storage::disk('local')->mimeType($archivo->ruta_archivo);
        $contenido = Storage::disk('local')->get($archivo->ruta_archivo);

        $this->previewName = $archivo->nombre_archivo;
        $this->previewUrl = 'data:' . $mime . ';base64,' . base64_encode($contenido);

        if (str_contains($mime, 'image')) {
            $this->previewType = 'image';
        } elseif (str_contains($mime, 'pdf')) {
            $this->previewType = 'pdf';
        } else {
            $this->previewType = 'other';
        }

        $this->modalPreview = true;
    }

    public function cerrarPrevisualizador(): void
    {
        $this->reset(['modalPreview', 'previewUrl', 'previewType', 'previewName']);
    }

    // ==========================================
    // HELPERS COMPUTADOS PARA LA VISTA
    // ==========================================
    
    // Obtiene el registro seleccionado para el panel derecho
    #[Computed]
    public function mantenimientoSeleccionado()
    {
        if (!$this->selectedMantenimientoId) return null;

        return mantenimiento::with(['tecnologia', 'archivos'])->find($this->selectedMantenimientoId);
    }

    // Resuelve la foto en Base64 de cualquier tecnología dada su ruta relativa
    public function getFotoBase64(?string $ruta): ?string
    {
        if (!$ruta || !Storage::disk('local')->exists($ruta)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($ruta);
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('local')->get($ruta));
    }

    // ==========================================
    // RENDER PRINCIPAL
    // ==========================================
    public function render()
    {
        $mantenimientos = mantenimiento::with(['tecnologia', 'archivos'])
            ->whereHas('tecnologia', function ($q) {
                $q->where('nombre', 'like', "%{$this->search}%")
                  ->orWhere('codigo_vin', 'like', "%{$this->search}%")
                  ->orWhere('marca', 'like', "%{$this->search}%");
            })
            ->orWhere('area_origen', 'like', "%{$this->search}%")
            ->orWhere('motivo', 'like', "%{$this->search}%")
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.inventario.mantenimiento-t-e-c', [
            'mantenimientos' => $mantenimientos
        ]);
    }
}