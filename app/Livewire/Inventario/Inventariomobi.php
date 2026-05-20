<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Mobiliario;
use App\Models\Personal;
use Illuminate\Support\Facades\Storage;

class Inventariomobi extends Component
{
    use WithFileUploads;

    #[Layout('layouts.app')]

    public $nombre, $material, $color, $estado = 'Bueno', $lugar = 'Oficina principal', $personal_id, $mueble_id;
    public $imagen; 
    public $imagen_actual; 
    public $search = '';
    public $isOpen = false;

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3',
            'material' => 'required',
            'color' => 'required',
            'estado' => 'required',
            'lugar' => 'required',
            'personal_id' => 'nullable|exists:personal,id',
            'imagen' => 'nullable|image|max:2048',
        ];
    }

    public function render()
    {
        $muebles = Mobiliario::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('material', 'like', '%' . $this->search . '%')
                      ->orWhere('lugar', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        return view('livewire.inventario.inventariomobi', [
            'muebles' => $muebles,
            'personal_list' => Personal::all()
        ]);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $mueble = Mobiliario::findOrFail($id);
        $this->mueble_id = $id;
        $this->nombre = $mueble->nombre;
        $this->material = $mueble->material;
        $this->color = $mueble->color;
        $this->estado = $mueble->estado;
        $this->lugar = $mueble->lugar;
        $this->personal_id = $mueble->personal_id;
        $this->imagen_actual = $mueble->imagen; 

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        $datos = [
            'nombre' => $this->nombre,
            'material' => $this->material,
            'color' => $this->color,
            'estado' => $this->estado,
            'lugar' => $this->lugar,
            'personal_id' => $this->personal_id ?: null,
        ];

        if ($this->imagen) {
            // Eliminar imagen anterior del disco 'local'
            if ($this->mueble_id) {
                $muebleAnterior = Mobiliario::find($this->mueble_id);
                if ($muebleAnterior && $muebleAnterior->imagen) {
                    Storage::disk('local')->delete($muebleAnterior->imagen);
                }
            }
            // Guardar en disco 'local' (storage/app/mobiliarios)
            $datos['imagen'] = $this->imagen->store('mobiliarios', 'local');
        }

        Mobiliario::updateOrCreate(['id' => $this->mueble_id], $datos);

        $this->dispatch('mueble-guardado', 
            msg: $this->mueble_id ? 'Mueble actualizado correctamente' : 'Mueble registrado con éxito'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        $mueble = Mobiliario::find($id);
        
        // Borrar del disco 'local'
        if ($mueble->imagen) {
            Storage::disk('local')->delete($mueble->imagen);
        }

        $mueble->delete();
        $this->dispatch('mueble-guardado', msg: 'Mueble eliminado correctamente');
    }

    public function openModal() { $this->isOpen = true; }
    
    public function closeModal() { 
        $this->isOpen = false; 
        $this->resetInputFields();
    }

    private function resetInputFields() {
        $this->nombre = ''; $this->material = ''; $this->color = '';
        $this->estado = 'Bueno'; $this->lugar = 'Oficina principal';
        $this->personal_id = ''; $this->mueble_id = '';
        $this->imagen = null; $this->imagen_actual = null;
    }
}