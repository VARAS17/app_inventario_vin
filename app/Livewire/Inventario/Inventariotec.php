<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Tecnologia;
use App\Models\Personal; // Cambiado de User a Personal

class Inventariotec extends Component
{
    #[Layout('layouts.app')]

    // Propiedades del componente
    // Cambiamos user_id por personal_id para ser consistentes con la DB
    public $nombre, $marca, $serie, $estado = 'En funcionamiento', $lugar = 'Oficina principal', $personal_id, $equipo_id;
    public $search = ''; 
    public $isOpen = false;

    protected $rules = [
        'nombre' => 'required|min:3',
        'marca' => 'required',
        'estado' => 'required',
        'lugar' => 'required|in:Oficina principal,Sala de Reuniones,Oficina de comunicaciones,Almacen,Cocina',
        'personal_id' => 'nullable|exists:personal,id', // Validación para asegurar que el ID existe
    ];

    public function render()
    {
        // Cargamos la relación 'personal' definida en el modelo Tecnologia
        $equipos = Tecnologia::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('serie', 'like', '%' . $this->search . '%')
                      ->orWhere('lugar', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        return view('livewire.inventario.inventariotec', [
            'equipos' => $equipos,
            'personales' => Personal::all() // Enviamos la lista de personal a la vista
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
        $this->personal_id = $equipo->personal_id; // Cambiado

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
            'personal_id' => $this->personal_id ?: null, // Cambiado
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
        $this->personal_id = ''; // Cambiado
        $this->equipo_id = '';
    }
}