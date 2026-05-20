<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Personal as PersonalModel;
use Illuminate\Support\Facades\Storage;

class Personal extends Component
{
    use WithFileUploads;

    #[Layout('layouts.app')]

    public $nombre, $apellido, $cargo, $grado_academico, $foto_perfil, $personal_id;
    public $foto_actual;
    
    public $search = '';
    public $isOpen = false;

    protected function rules()
    {
        return [
            'nombre' => 'required|string|min:2',
            'apellido' => 'required|string|min:2',
            'cargo' => 'required|string',
            'grado_academico' => 'required',
            'foto_perfil' => $this->foto_perfil instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile 
                             ? 'nullable|image|max:2048' 
                             : 'nullable',
        ];
    }

    public function render()
    {
        $personales = PersonalModel::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('cargo', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.inventario.personal', [
            'personales' => $personales
        ]);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $persona = PersonalModel::findOrFail($id);
        $this->personal_id = $id;
        $this->nombre = $persona->nombre;
        $this->apellido = $persona->apellido;
        $this->cargo = $persona->cargo;
        $this->grado_academico = $persona->grado_academico;
        $this->foto_actual = $persona->foto_perfil;
        $this->foto_perfil = null;

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        $data = [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'cargo' => $this->cargo,
            'grado_academico' => $this->grado_academico,
        ];

        if ($this->foto_perfil) {
            // Eliminamos la foto anterior del disco 'local' si existe
            if ($this->personal_id) {
                $personaExistente = PersonalModel::find($this->personal_id);
                if ($personaExistente && $personaExistente->foto_perfil) {
                    Storage::disk('local')->delete($personaExistente->foto_perfil);
                }
            }
            
            // Guardamos la nueva foto en el disco 'local' (storage/app/perfiles)
            // Al usar 'local', el archivo persiste directamente en el servidor sin enlaces simbólicos
            $path = $this->foto_perfil->store('perfiles', 'local');
            $data['foto_perfil'] = $path;
        }

        PersonalModel::updateOrCreate(['id' => $this->personal_id], $data);

        session()->flash('message', $this->personal_id ? 'Personal actualizado.' : 'Personal registrado.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        $persona = PersonalModel::findOrFail($id);
        
        // Borrar foto del disco 'local'
        if ($persona->foto_perfil) {
            Storage::disk('local')->delete($persona->foto_perfil);
        }

        $persona->delete();
        session()->flash('message', 'Registro eliminado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->apellido = '';
        $this->cargo = '';
        $this->grado_academico = '';
        $this->foto_perfil = null;
        $this->foto_actual = null;
        $this->personal_id = '';
    }
}