<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Personal as PersonalModel;
use App\Models\Area; // Importado para cargar el listado de áreas en la vista
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Exception;

class Personal extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $personal_id;
    public $area_id; // <-- NUEVO CAMPO
    public $nombre;
    public $apellido;
    public $cargo;
    public $grado_academico;
    public $correo;
    public $foto_perfil; 
    public $foto_actual;

    // Filtro de búsqueda
    public $search = '';

    // Estado del modal
    public $isOpen = false;

    /**
     * Reglas de validación
     */
    protected function rules()
    {
        return [
            'area_id'         => 'required|exists:areas,id', // <-- VALIDACIÓN DEL ÁREA
            'nombre'          => 'required|min:2|max:100',
            'apellido'        => 'required|min:2|max:100',
            'cargo'           => 'required|string|max:100',
            'grado_academico' => 'required|string|max:50',
            'correo'          => [
                'required',
                'email',
                'max:150',
                Rule::unique('personal', 'correo')->ignore($this->personal_id),
            ],
            'foto_perfil'     => 'nullable|image|max:2048', // Máximo 2MB
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    protected function messages()
    {
        return [
            'area_id.required'         => 'Debe seleccionar un área de trabajo.',
            'area_id.exists'           => 'El área seleccionada no es válida.',
            'nombre.required'          => 'El nombre es obligatorio.',
            'nombre.min'               => 'El nombre debe tener al menos 2 caracteres.',
            'apellido.required'        => 'El apellido es obligatorio.',
            'cargo.required'           => 'El cargo es obligatorio.',
            'grado_academico.required' => 'El grado académico es obligatorio.',
            'correo.required'          => 'El correo electrónico es obligatorio.',
            'correo.email'             => 'Debe ingresar un correo válido.',
            'correo.unique'            => 'Este correo electrónico ya está registrado.',
            'foto_perfil.image'        => 'El archivo debe ser una imagen.',
            'foto_perfil.max'          => 'La imagen no debe pesar más de 2MB.',
        ];
    }

    /**
     * Resetear paginación al buscar
     */
    public function updatingSearch() 
    { 
        $this->resetPage(); 
    }

    /**
     * Validación en tiempo real
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        $personales = PersonalModel::with('area') // Carga la relación para evitar lentitud en la tabla
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('cargo', 'like', '%' . $this->search . '%')
                      ->orWhere('correo', 'like', '%' . $this->search . '%')
                      ->orWhereHas('area', function($q) {
                          $q->where('nombre', 'like', '%' . $this->search . '%');
                      });
            })
            ->latest()
            ->paginate(10);

        // Se envía el listado de áreas para llenar el <select> en la vista
        $areas = Area::orderBy('nombre', 'asc')->get();

        return view('livewire.inventario.personal', [
            'personales' => $personales,
            'areas'      => $areas
        ]);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $this->resetInputFields();
        $persona = PersonalModel::findOrFail($id);

        $this->personal_id     = $persona->id;
        $this->area_id         = $persona->area_id; // <-- CARGAR ÁREA AL EDITAR
        $this->nombre          = $persona->nombre;
        $this->apellido        = $persona->apellido;
        $this->cargo           = $persona->cargo;
        $this->grado_academico = $persona->grado_academico;
        $this->correo          = $persona->correo;
        $this->foto_actual     = $persona->foto_perfil;
        $this->foto_perfil     = null;

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        try {
            // Datos base a persistir
            $datos = [
                'area_id'         => $this->area_id, // <-- GUARDAR O ACTUALIZAR ÁREA
                'nombre'          => $this->nombre,
                'apellido'        => $this->apellido,
                'cargo'           => $this->cargo,
                'grado_academico' => $this->grado_academico,
                'correo'          => $this->correo,
            ];

            // Solo si se subió un nuevo archivo se procesa el almacenamiento
            if ($this->foto_perfil) {
                // Si estamos editando y existía una foto previa, se elimina del disco local
                if ($this->personal_id && $this->foto_actual) {
                    Storage::disk('local')->delete($this->foto_actual);
                }
                // Almacenar en storage/app/perfiles en disco local
                $datos['foto_perfil'] = $this->foto_perfil->store('perfiles', 'local');
            }

            PersonalModel::updateOrCreate(
                ['id' => $this->personal_id], 
                $datos
            );

            $msg = $this->personal_id ? 'Personal actualizado con éxito.' : 'Personal registrado con éxito.';
            session()->flash('message', $msg);

            $this->closeModal();
            $this->resetInputFields();

        } catch (Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            $persona = PersonalModel::findOrFail($id);

            // Eliminar foto del disco local si existe
            if ($persona->foto_perfil) {
                Storage::disk('local')->delete($persona->foto_perfil);
            }

            $persona->delete();
            session()->flash('message', 'Registro eliminado correctamente.');

        } catch (Exception $e) {
            session()->flash('error', 'No se pudo eliminar el registro.');
        }
    }

    public function openModal() 
    { 
        $this->isOpen = true; 
    }

    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetInputFields()
    {
        $this->personal_id     = null;
        $this->area_id         = null; // <-- REINICIAR VALOR
        $this->nombre          = '';
        $this->apellido        = '';
        $this->cargo           = '';
        $this->grado_academico = '';
        $this->correo          = '';
        $this->foto_perfil     = null;
        $this->foto_actual     = null;
    }
}