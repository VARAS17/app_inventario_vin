<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Mobiliario;
use App\Models\User;

class Inventariomobi extends Component
{
    // Usamos el atributo para definir el layout de Breeze/Jetstream
    #[Layout('layouts.app')]

    // Propiedades del componente
    public $nombre, $material, $color, $estado = 'Bueno', $lugar = 'Oficina principal', $user_id, $mueble_id;
    public $search = '';
    public $isOpen = false;

    // Reglas de validación
    protected $rules = [
        'nombre' => 'required|min:3',
        'material' => 'required',
        'color' => 'required',
        'estado' => 'required',
        'lugar' => 'required',
    ];

    public function render()
    {
        $muebles = Mobiliario::with('user')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('material', 'like', '%' . $this->search . '%')
                      ->orWhere('lugar', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        // IMPORTANTE: Asegúrate de que la vista esté en resources/views/livewire/inventario/inventariomobi.blade.php
        return view('livewire.inventario.inventariomobi', [
            'muebles' => $muebles,
            'users' => User::all()
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
        $this->user_id = $mueble->user_id;

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        Mobiliario::updateOrCreate(['id' => $this->mueble_id], [
            'nombre' => $this->nombre,
            'material' => $this->material,
            'color' => $this->color,
            'estado' => $this->estado,
            'lugar' => $this->lugar,
            'user_id' => $this->user_id ?: null,
        ]);

        session()->flash('message', $this->mueble_id ? 'Mueble actualizado.' : 'Mueble registrado.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        Mobiliario::find($id)->delete();
        session()->flash('message', 'Mueble eliminado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields() {
        $this->nombre = '';
        $this->material = '';
        $this->color = '';
        $this->estado = 'Bueno';
        $this->lugar = 'Oficina principal';
        $this->user_id = '';
        $this->mueble_id = '';
    }
}