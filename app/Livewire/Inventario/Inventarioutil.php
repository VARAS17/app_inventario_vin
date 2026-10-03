<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\utiles;
use App\Models\movimientos_utiles;
use Exception;

class InventarioUtil extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $util_id;
    public $nombre;
    public $marca;
    public $unidad = 'Unidad';
    public $stock_actual = 0;
    public $stock_minimo = 5;

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
            'nombre'       => 'required|string|min:2|max:150',
            'marca'        => 'nullable|string|max:100',
            'unidad'       => 'required|string|max:50',
            'stock_actual' => 'required|integer|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    protected function messages()
    {
        return [
            'nombre.required'       => 'El nombre del útil es obligatorio.',
            'nombre.min'            => 'Debe tener al menos 2 caracteres.',
            'unidad.required'       => 'Debe definir una unidad de medida.',
            'stock_actual.required' => 'Indique el stock actual.',
            'stock_actual.integer'  => 'El stock debe ser un número entero.',
            'stock_minimo.required' => 'Indique el stock mínimo de alerta.',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $utilesList = utiles::where(function ($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('unidad', 'like', '%' . $this->search . '%');
            })
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        return view('livewire.inventario.inventarioutil', [
            'utilesList' => $utilesList
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
        $item = utiles::findOrFail($id);

        $this->util_id      = $item->id;
        $this->nombre       = $item->nombre;
        $this->marca        = $item->marca;
        $this->unidad       = $item->unidad;
        $this->stock_actual = $item->stock_actual;
        $this->stock_minimo = $item->stock_minimo;

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        try {
            $esNuevo = empty($this->util_id);

            $util = utiles::updateOrCreate(
                ['id' => $this->util_id],
                [
                    'nombre'       => $this->nombre,
                    'marca'        => $this->marca,
                    'unidad'       => $this->unidad,
                    'stock_actual' => $this->stock_actual,
                    'stock_minimo' => $this->stock_minimo,
                ]
            );

            // Si es un útil nuevo y se ingresa con stock inicial mayor a 0, se deja registro en Kardex
            if ($esNuevo && $this->stock_actual > 0) {
                movimientos_utiles::create([
                    'util_id'         => $util->id,
                    'tipo'            => 'Saldo Inicial',
                    'cantidad'        => $this->stock_actual,
                    'stock_anterior'  => 0,
                    'stock_nuevo'     => $this->stock_actual,
                    'referencia_tipo' => 'Inventario Inicial',
                    'referencia_id'   => null,
                    'descripcion'     => 'Registro de saldo inicial en el sistema',
                    'fecha'           => now()->toDateString(),
                ]);
            }

            session()->flash('message', $this->util_id ? 'Útil actualizado con éxito.' : 'Útil registrado con éxito.');
            $this->closeModal();
            $this->resetInputFields();

        } catch (Exception $e) {
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    public function eliminar($id)
    {
        try {
            $util = utiles::findOrFail($id);
            $util->delete();
            session()->flash('message', 'Útil eliminado del catálogo.');
        } catch (Exception $e) {
            session()->flash('error', 'No se pudo eliminar el útil porque tiene registros asociados.');
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
        $this->util_id      = null;
        $this->nombre       = '';
        $this->marca        = '';
        $this->unidad       = 'Unidad';
        $this->stock_actual = 0;
        $this->stock_minimo = 5;
    }
}