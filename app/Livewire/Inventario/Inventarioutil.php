<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Util;
use App\Models\MovimientoUtil;
use Illuminate\Support\Facades\DB;

class Inventarioutil extends Component
{
    #[Layout('layouts.app')]

    // Propiedades CRUD (Crear/Editar)
    public $nombre, $cantidad, $unidad = 'Unidad', $util_id;
    public $descripcion = ''; 

    // Propiedades para el flujo de Movimiento Rápido (NUEVO)
    public $isOpenMovimiento = false;
    public $tipoMovimiento = ''; // 'Ingreso' o 'Egreso'
    public $cantidadMovimiento;
    public $descripcionMovimiento = '';
    public $articuloSeleccionado; // Para mostrar info en el modal

    // Propiedades de búsqueda e Historial
    public $search = '';
    public $isOpen = false;        
    public $isHistoryOpen = false; 
    public $historial = [];        

    protected $rules = [
        'nombre' => 'required|min:3',
        'cantidad' => 'required|numeric|min:0',
        'unidad' => 'required',
    ];

    public function render()
    {
        $utiles = Util::where('nombre', 'like', '%' . $this->search . '%')
            ->latest()
            ->get();

        return view('livewire.inventario.inventarioutil', [
            'utiles' => $utiles
        ]);
    }

    // --- FLUJO DE MOVIMIENTO RÁPIDO (INGRESO / EGRESO) ---

    public function abrirModalMovimiento($id)
    {
        $this->articuloSeleccionado = Util::findOrFail($id);
        $this->tipoMovimiento = ''; // Forzamos a elegir uno
        $this->cantidadMovimiento = null;
        $this->descripcionMovimiento = '';
        $this->isOpenMovimiento = true;
    }

    public function procesarMovimiento()
    {
        // Validaciones básicas
        $this->validate([
            'tipoMovimiento' => 'required|in:Ingreso,Egreso',
            'cantidadMovimiento' => 'required|numeric|min:1',
            'descripcionMovimiento' => 'required|min:3',
        ], [
            'tipoMovimiento.required' => 'Debe seleccionar Ingreso o Egreso.',
            'descripcionMovimiento.required' => 'Debe indicar un motivo para el historial.'
        ]);

        // Validación de Stock (No permitir egresos mayores al stock actual)
        if ($this->tipoMovimiento === 'Egreso' && $this->cantidadMovimiento > $this->articuloSeleccionado->cantidad) {
            $this->addError('cantidadMovimiento', 'No hay suficiente stock. Stock actual: ' . $this->articuloSeleccionado->cantidad);
            return;
        }

        DB::transaction(function () {
            // 1. Actualizar el stock en la tabla principal
            if ($this->tipoMovimiento === 'Ingreso') {
                $this->articuloSeleccionado->increment('cantidad', $this->cantidadMovimiento);
            } else {
                $this->articuloSeleccionado->decrement('cantidad', $this->cantidadMovimiento);
            }

            // 2. Registrar el movimiento en el historial
            $this->articuloSeleccionado->movimientos()->create([
                'tipo' => $this->tipoMovimiento,
                'cantidad' => $this->cantidadMovimiento,
                'descripcion' => $this->descripcionMovimiento,
                'fecha' => now(),
            ]);
        });

        session()->flash('message', 'Stock actualizado correctamente.');
        $this->closeMovimientoModal();
    }

    public function closeMovimientoModal()
    {
        $this->isOpenMovimiento = false;
        $this->reset(['tipoMovimiento', 'cantidadMovimiento', 'descripcionMovimiento', 'articuloSeleccionado']);
    }


    // --- MÉTODOS DE HISTORIAL ---

    public function verHistorial($id)
    {
        $util = Util::findOrFail($id);
        $this->historial = $util->movimientos()->orderBy('created_at', 'desc')->get();
        $this->isHistoryOpen = true;
    }

    public function closeHistoryModal()
    {
        $this->isHistoryOpen = false;
        $this->historial = [];
    }


    // --- MÉTODOS DE CRUD TRADICIONAL ---

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $articulo = Util::findOrFail($id);
        $this->util_id = $id;
        $this->nombre = $articulo->nombre;
        $this->cantidad = $articulo->cantidad;
        $this->unidad = $articulo->unidad;
        $this->descripcion = ''; 
        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        DB::transaction(function () {
            if ($this->util_id) {
                $util = Util::find($this->util_id);
                $cantidadAnterior = $util->cantidad;
                $diferencia = $this->cantidad - $cantidadAnterior;

                $util->update([
                    'nombre' => $this->nombre,
                    'cantidad' => $this->cantidad,
                    'unidad' => $this->unidad,
                ]);

                if ($diferencia != 0) {
                    $util->movimientos()->create([
                        'tipo' => $diferencia > 0 ? 'Ingreso' : 'Egreso',
                        'cantidad' => abs($diferencia),
                        'descripcion' => $this->descripcion ?: 'Actualización manual de nombre/unidad',
                        'fecha' => now(),
                    ]);
                }
            } else {
                $util = Util::create([
                    'nombre' => $this->nombre,
                    'cantidad' => $this->cantidad,
                    'unidad' => $this->unidad,
                ]);

                $util->movimientos()->create([
                    'tipo' => 'Ingreso',
                    'cantidad' => $this->cantidad,
                    'descripcion' => $this->descripcion ?: 'Registro inicial',
                    'fecha' => now(),
                ]);
            }
        });

        session()->flash('message', $this->util_id ? 'Artículo actualizado.' : 'Artículo agregado.');
        $this->closeModal();
    }

    public function eliminar($id)
    {
        Util::find($id)->delete();
        session()->flash('message', 'Artículo eliminado correctamente.');
    }

    public function openModal() { $this->isOpen = true; }
    
    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->cantidad = '';
        $this->unidad = 'Unidad';
        $this->descripcion = '';
        $this->util_id = '';
    }
}