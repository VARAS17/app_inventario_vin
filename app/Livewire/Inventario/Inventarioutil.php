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

    // Propiedades CRUD (Se agregó $marca)
    public $nombre, $cantidad, $marca, $unidad = 'Unidad', $util_id;
    public $descripcion = ''; 

    // Propiedades para el flujo de Movimiento Rápido
    public $isOpenMovimiento = false;
    public $tipoMovimiento = ''; 
    public $cantidadMovimiento;
    public $descripcionMovimiento = '';
    public $articuloSeleccionado; 

    // Propiedades de búsqueda e Historial
    public $search = '';
    public $isOpen = false;        
    public $isHistoryOpen = false; 
    public $historial = [];        

    // Reglas de validación actualizadas
    protected $rules = [
        'nombre' => 'required|min:3',
        'marca' => 'required|min:2', // Nueva regla
        'cantidad' => 'required|numeric|min:0',
        'unidad' => 'required',
    ];

    public function render()
    {
        // Se actualizó la búsqueda para incluir también la marca si se desea
        $utiles = Util::where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('marca', 'like', '%' . $this->search . '%')
            ->latest()
            ->get();

        return view('livewire.inventario.inventarioutil', [
            'utiles' => $utiles
        ]);
    }

    // --- FLUJO DE MOVIMIENTO RÁPIDO ---
    public function abrirModalMovimiento($id)
    {
        $this->articuloSeleccionado = Util::findOrFail($id);
        $this->tipoMovimiento = ''; 
        $this->cantidadMovimiento = null;
        $this->descripcionMovimiento = '';
        $this->isOpenMovimiento = true;
    }

    public function procesarMovimiento()
    {
        $this->validate([
            'tipoMovimiento' => 'required|in:Ingreso,Egreso',
            'cantidadMovimiento' => 'required|numeric|min:1',
            'descripcionMovimiento' => 'required|min:3',
        ], [
            'tipoMovimiento.required' => 'Debe seleccionar Ingreso o Egreso.',
            'descripcionMovimiento.required' => 'Debe indicar un motivo para el historial.'
        ]);

        if ($this->tipoMovimiento === 'Egreso' && $this->cantidadMovimiento > $this->articuloSeleccionado->cantidad) {
            $this->addError('cantidadMovimiento', 'No hay suficiente stock. Stock actual: ' . $this->articuloSeleccionado->cantidad);
            return;
        }

        DB::transaction(function () {
            if ($this->tipoMovimiento === 'Ingreso') {
                $this->articuloSeleccionado->increment('cantidad', $this->cantidadMovimiento);
            } else {
                $this->articuloSeleccionado->decrement('cantidad', $this->cantidadMovimiento);
            }

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
        $this->marca = $articulo->marca; // Cargar marca
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
                    'marca' => $this->marca, // Guardar marca
                    'cantidad' => $this->cantidad,
                    'unidad' => $this->unidad,
                ]);

                if ($diferencia != 0) {
                    $util->movimientos()->create([
                        'tipo' => $diferencia > 0 ? 'Ingreso' : 'Egreso',
                        'cantidad' => abs($diferencia),
                        'descripcion' => $this->descripcion ?: 'Actualización manual de datos',
                        'fecha' => now(),
                    ]);
                }
            } else {
                $util = Util::create([
                    'nombre' => $this->nombre,
                    'marca' => $this->marca, // Guardar marca
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
        $this->marca = ''; // Resetear marca
        $this->cantidad = '';
        $this->unidad = 'Unidad';
        $this->descripcion = '';
        $this->util_id = '';
    }

    public function exportar()
    {
        $utiles = \App\Models\Util::with('movimientos')
            ->where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('marca', 'like', '%' . $this->search . '%')
            ->latest()
            ->get();

        $filename = 'inventario_utiles.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($utiles) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            // ── SECCIÓN 1: STOCK ACTUAL ──
            fputcsv($file, ['=== STOCK ACTUAL ==='], ';');
            fputcsv($file, ['Nombre', 'Marca', 'Cantidad', 'Unidad'], ';');

            foreach ($utiles as $util) {
                fputcsv($file, [
                    $util->nombre,
                    $util->marca,
                    $util->cantidad,
                    $util->unidad,
                ], ';');
            }

            // Fila vacía separadora
            fputcsv($file, [], ';');

            // ── SECCIÓN 2: HISTORIAL DE MOVIMIENTOS ──
            fputcsv($file, ['=== HISTORIAL DE MOVIMIENTOS ==='], ';');
            fputcsv($file, ['Artículo', 'Marca', 'Tipo', 'Cantidad', 'Motivo', 'Fecha'], ';');

            foreach ($utiles as $util) {
                foreach ($util->movimientos as $mov) {
                    fputcsv($file, [
                        $util->nombre,
                        $util->marca,
                        $mov->tipo,
                        $mov->cantidad,
                        $mov->descripcion,
                        $mov->fecha ?? $mov->created_at,
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}