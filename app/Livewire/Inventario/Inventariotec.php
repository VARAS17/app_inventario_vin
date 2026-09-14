<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Tecnologia;
use App\Models\tecnologias_archivos;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class Inventariotec extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Layout('layouts.app')]

    public string $search = '';
    protected int $perPage = 8;

    // Item seleccionado (con sus archivos cargados)
    public ?int $selectedId = null;
    public ?Tecnologia $selectedTecnologia = null;

    // Control del Modal
    public bool $isOpenModal = false;
    public bool $isEditMode = false;
    public ?int $tecnologia_id = null;

    // Campos del Formulario
    public string $codigo_vin = '';
    public string $nombre = '';
    public string $marca = '';
    public ?string $serie = null;
    public string $estado = 'Disponible';
    public string $fecha_ingreso = '';
    public string $proveedor = '';
    public $foto = null;
    public ?string $foto_existente = null;

    // PROPIEDADES PARA MÚLTIPLES PDFS
    public array $nuevos_archivos = [];        // [['nombre' => '', 'archivo' => null], ...]
    public $archivos_existentes = [];          // Colección al editar

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'codigo_vin' => [
                'required',
                'string',
                Rule::unique('tecnologias', 'codigo_vin')->ignore($this->tecnologia_id),
            ],
            'nombre'        => 'required|string|max:150',
            'marca'         => 'required|string|max:100',
            'serie'         => 'nullable|string|max:100',
            'estado'        => ['required', Rule::in(['Disponible', 'Asignado', 'En Mantenimiento'])],
            'fecha_ingreso' => 'required|date',
            'proveedor'     => 'required|string|max:150',
            'foto'          => $this->isEditMode 
                                ? 'nullable|image|max:5120' 
                                : 'required|image|max:5120',
            // Reglas para los PDFs que se agreguen
            'nuevos_archivos.*.nombre'  => 'required|string|max:150',
            'nuevos_archivos.*.archivo' => 'required|file|mimes:pdf|max:10240', // Máx 10MB c/u
        ];
    }

    protected $messages = [
        'codigo_vin.required' => 'El código VIN es obligatorio.',
        'codigo_vin.unique'   => 'Este código VIN ya está registrado.',
        'nombre.required'     => 'El nombre del equipo es obligatorio.',
        'marca.required'      => 'La marca es obligatoria.',
        'estado.required'     => 'Selecciona un estado válido.',
        'fecha_ingreso.date'  => 'Ingresa una fecha de ingreso válida.',
        'foto.required'       => 'Debes adjuntar una foto del equipo.',
        'foto.image'          => 'El archivo debe ser una imagen válida.',
        'foto.max'            => 'La imagen no debe superar los 5MB.',
        'nuevos_archivos.*.nombre.required'  => 'El nombre del documento es obligatorio.',
        'nuevos_archivos.*.archivo.required' => 'Debes adjuntar el archivo PDF.',
        'nuevos_archivos.*.archivo.mimes'    => 'El documento debe ser exclusivamente formato PDF.',
        'nuevos_archivos.*.archivo.max'      => 'El PDF no debe superar los 10MB.',
    ];

    /**
     * Métodos para la gestión dinámica de PDFs en el Formulario
     */
    public function addArchivoInput(): void
    {
        $this->nuevos_archivos[] = ['nombre' => '', 'archivo' => null];
    }

    public function removeArchivoInput(int $index): void
    {
        unset($this->nuevos_archivos[$index]);
        $this->nuevos_archivos = array_values($this->nuevos_archivos);
    }

    public function eliminarArchivoExistente(int $archivoId): void
    {
        $doc = tecnologias_archivos::findOrFail($archivoId);
        
        // Borrar el archivo físico del disco interno
        if (Storage::disk('local')->exists($doc->ruta_archivo)) {
            Storage::disk('local')->delete($doc->ruta_archivo);
        }
        
        $doc->delete();
        
        // Refrescar lista de archivos existentes en el modal
        $this->archivos_existentes = tecnologias_archivos::where('tecnologia_id', $this->tecnologia_id)->get();

        // Si este equipo estaba seleccionado abajo, refrescar su detalle
        if ($this->selectedId === $this->tecnologia_id) {
            $this->selectedTecnologia->load('archivos');
        }
    }

    /**
     * Cargar equipo seleccionado con sus archivos adjuntos
     */
    public function selectItem(int $id): void
    {
        $this->selectedId = $id;
        $this->selectedTecnologia = Tecnologia::with('archivos')->find($id);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->codigo_vin = Tecnologia::generarSiguienteCodigoVin();
        $this->fecha_ingreso = now()->format('Y-m-d');
        $this->isOpenModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $this->isEditMode = true;

        $tec = Tecnologia::with('archivos')->findOrFail($id);
        $this->tecnologia_id       = $tec->id;
        $this->codigo_vin          = $tec->codigo_vin;
        $this->nombre              = $tec->nombre;
        $this->marca               = $tec->marca;
        $this->serie               = $tec->serie;
        $this->estado              = $tec->estado;
        $this->fecha_ingreso       = $tec->fecha_ingreso 
            ? Carbon::parse($tec->fecha_ingreso)->format('Y-m-d') 
            : '';
        $this->proveedor           = $tec->proveedor;
        $this->foto_existente      = $tec->foto;
        $this->archivos_existentes = $tec->archivos;

        $this->isOpenModal = true;
    }

    public function save(): void
    {
        $validatedData = $this->validate();

        if ($this->isEditMode) {
            $tecnologia = Tecnologia::findOrFail($this->tecnologia_id);

            if ($this->foto) {
                if ($tecnologia->foto && Storage::disk('local')->exists($tecnologia->foto)) {
                    Storage::disk('local')->delete($tecnologia->foto);
                }
                $validatedData['foto'] = $this->foto->store('tecnologias', 'local');
            } else {
                unset($validatedData['foto']);
            }

            $tecnologia->update($validatedData);
            session()->flash('message', 'Equipo tecnológico actualizado exitosamente.');
        } else {
            $validatedData['foto'] = $this->foto->store('tecnologias', 'local');
            $tecnologia = Tecnologia::create($validatedData);

            session()->flash('message', 'Equipo registrado con código: ' . $tecnologia->codigo_vin);
        }

        // GUARDAR LOS NUEVOS PDFs SUBIDOS
        if (!empty($this->nuevos_archivos)) {
            foreach ($this->nuevos_archivos as $item) {
                if (isset($item['archivo']) && $item['archivo']) {
                    $rutaPdf = $item['archivo']->store('tecnologias_archivos', 'local');
                    
                    tecnologias_archivos::create([
                        'nombre_archivo'        => $item['nombre'],
                        'ruta_archivo'  => $rutaPdf,
                        'tecnologia_id' => $tecnologia->id,
                    ]);
                }
            }
        }

        // Refrescar el panel inferior si estaba seleccionado
        if ($this->selectedId === $tecnologia->id) {
            $this->selectedTecnologia = Tecnologia::with('archivos')->find($this->selectedId);
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $tecnologia = Tecnologia::with('archivos')->findOrFail($id);

        // 1. Borrar la foto del disco
        if ($tecnologia->foto && Storage::disk('local')->exists($tecnologia->foto)) {
            Storage::disk('local')->delete($tecnologia->foto);
        }

        // 2. Borrar físicamente todos los PDFs vinculados en el disco
        foreach ($tecnologia->archivos as $archivo) {
            if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                Storage::disk('local')->delete($archivo->ruta_archivo);
            }
        }

        $tecnologia->delete();

        if ($this->selectedId === $id) {
            $this->selectedId = null;
            $this->selectedTecnologia = null;
        }

        session()->flash('message', 'Registro y archivos eliminados correctamente.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->isOpenModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'tecnologia_id',
            'codigo_vin',
            'nombre',
            'marca',
            'serie',
            'estado',
            'fecha_ingreso',
            'proveedor',
            'foto',
            'foto_existente',
            'nuevos_archivos',
            'archivos_existentes'
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        $tecnologias = Tecnologia::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo_vin', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('serie', 'like', '%' . $this->search . '%')
                      ->orWhere('proveedor', 'like', '%' . $this->search . '%')
                      ->orWhere('estado', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        return view('livewire.inventario.inventariotec', [
            'tecnologias' => $tecnologias
        ]);
    }
}