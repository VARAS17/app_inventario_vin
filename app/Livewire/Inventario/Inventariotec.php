<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tecnologia;
use App\Models\User;

class Inventariotec extends Component
{
    // Aplicamos el layout de Breeze/Jetstream
    #[Layout('layouts.app')]

    // Propiedades del componente
    public $nombre, $marca, $serie, $estado = 'En funcionamiento', $lugar = 'Oficina principal', $user_id, $equipo_id;
    public $search = ''; 
    public $isOpen = false;

    // Reglas de validación
    protected $rules = [
        'nombre' => 'required|min:3',
        'marca' => 'required',
        'estado' => 'required',
        'lugar' => 'required|in:Oficina principal,Sala de Reuniones,Oficina de comunicaciones,Almacen,Cocina',
    ];

    public function render()
    {
        $equipos = Tecnologia::with('user')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('serie', 'like', '%' . $this->search . '%')
                      ->orWhere('lugar', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        // IMPORTANTE: La vista ahora está en livewire.inventario.inventariotec
        return view('livewire.inventario.inventariotec', [
            'equipos' => $equipos,
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
        $equipo = Tecnologia::findOrFail($id);
        $this->equipo_id = $id;
        $this->nombre = $equipo->nombre;
        $this->marca = $equipo->marca;
        $this->serie = $equipo->serie;
        $this->estado = $equipo->estado;
        $this->lugar = $equipo->lugar; 
        $this->user_id = $equipo->user_id;

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        Tecnologia::updateOrCreate(['id' => $this->equipo_id], [
            'nombre' => $this->nombre,
            'marca' => $this->marca,
            'serie' => $this->serie,
            'estado' => $this->estado,
            'lugar' => $this->lugar, 
            'user_id' => $this->user_id ?: null,
        ]);

        session()->flash('message', $this->equipo_id ? 'Equipo actualizado.' : 'Equipo creado.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        Tecnologia::find($id)->delete();
        session()->flash('message', 'Equipo eliminado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields() {
        $this->nombre = ''; 
        $this->marca = ''; 
        $this->serie = '';
        $this->estado = 'En funcionamiento'; 
        $this->lugar = 'Oficina principal'; 
        $this->user_id = ''; 
        $this->equipo_id = '';
    }
}