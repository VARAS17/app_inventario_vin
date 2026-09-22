<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Area;
use Livewire\Attributes\Layout;


class AreasVIN extends Component
{

    use WithPagination;
    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $area_id = null;
    public $nombre = '';

    // Búsqueda y control del modal
    public $search = '';
    public $isModalOpen = false;

    // Resetear página al buscar
    public function updatingSearch()
    {
        $this->resetPage();
    }

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:100|unique:areas,nombre,' . $this->area_id,
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre del área es obligatorio.',
        'nombre.unique'   => 'Esta área ya existe.',
        'nombre.max'      => 'El nombre no debe superar los 100 caracteres.',
    ];

    public function render()
    {
        $areas = Area::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        return view('livewire.areas-v-i-n', [
            'areas' => $areas,
        ]);
    }

    /**
     * Abrir modal para crear
     */
    public function create()
    {
        $this->resetInputFields();
        $this->isModalOpen = true;
    }

    /**
     * Guardar o actualizar área
     */
    public function store()
    {
        $this->validate();

        Area::updateOrCreate(
            ['id' => $this->area_id],
            ['nombre' => trim($this->nombre)]
        );

        session()->flash('message', $this->area_id ? 'Área actualizada con éxito.' : 'Área creada con éxito.');

        $this->closeModal();
    }

    /**
     * Cargar datos para editar
     */
    public function edit($id)
    {
        $area = Area::findOrFail($id);
        $this->area_id = $area->id;
        $this->nombre  = $area->nombre;

        $this->isModalOpen = true;
    }

    /**
     * Eliminar área
     */
    public function delete($id)
    {
        Area::findOrFail($id)->delete();
        session()->flash('message', 'Área eliminada con éxito.');
    }

    /**
     * Cerrar modal
     */
    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    /**
     * Limpiar campos
     */
    private function resetInputFields()
    {
        $this->nombre = '';
        $this->area_id = null;
        $this->resetErrorBag();
    }
}