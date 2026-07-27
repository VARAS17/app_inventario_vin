<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Util;
use App\Models\MovimientoUtil;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

class Inventarioutil extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $nombre, $cantidad, $marca, $unidad = 'Unidad', $util_id;
    public $descripcion = ''; 

    // Propiedades de Movimientos
    public $isOpenMovimiento = false;
    public $tipoMovimiento = ''; 
    public $cantidadMovimiento;
    public $descripcionMovimiento = '';
    public $articuloSeleccionado; 

    // Propiedades de UI
    public $search = '';
    public $isOpen = false;        
    public $isHistoryOpen = false; 
    public $historial = [];    
        
    // Resetear la página cuando se busca algo nuevo para evitar errores de paginación
    public function updatingSearch()
    {
        $this->resetPage();
    }

    protected $rules = [
        'nombre' => 'required|min:3',
        'marca' => 'required|min:2',
        'cantidad' => 'required|numeric|min:0',
        'unidad' => 'required',
    ];

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'marca.required' => 'La marca es obligatoria.',
        'marca.min' => 'La marca debe tener al menos 2 caracteres.',
        'cantidad.required' => 'La cantidad es obligatoria.',
        'cantidad.numeric' => 'La cantidad debe ser un número.',
        'cantidad.min' => 'La cantidad no puede ser negativa.',
        'unidad.required' => 'La unidad es obligatoria.',
    ];

    public function render()
    {
        $utiles = Util::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10); 

        return view('livewire.inventario.inventarioutil', [
            'utiles' => $utiles
        ]);
    }

    // --- MÉTODOS DE MOVIMIENTOS RÁPIDOS ---

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
            'tipoMovimiento.in' => 'El tipo de movimiento no es válido.',
            'cantidadMovimiento.required' => 'La cantidad es obligatoria.',
            'cantidadMovimiento.min' => 'La cantidad debe ser al menos 1.',
            'descripcionMovimiento.required' => 'Debe indicar un motivo para el historial.',
            'descripcionMovimiento.min' => 'El motivo debe ser más descriptivo.'
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

    // --- MÉTODOS CRUD ---

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
        $this->marca = $articulo->marca;
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
                    'marca' => $this->marca,
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
                    'marca' => $this->marca,
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

        session()->flash('message', $this->util_id ? 'Artículo actualizado con éxito.' : 'Artículo agregado con éxito.');
        $this->closeModal();
    }

    public function eliminar($id)
    {
        try {
            $util = Util::findOrFail($id);
            DB::transaction(function () use ($util) {
                $util->movimientos()->delete();
                $util->delete();
            });
            session()->flash('message', 'Artículo y su historial eliminados.');
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el artículo.');
        }
    }

    public function openModal() { $this->isOpen = true; }
    
    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->resetInputFields();
        $this->resetErrorBag();
    }

    private function resetInputFields()
    {
        $this->nombre = '';
        $this->marca = '';
        $this->cantidad = '';
        $this->unidad = 'Unidad';
        $this->descripcion = '';
        $this->util_id = '';
    }

    // --- EXPORTACIÓN ---

    public function exportar()
    {
        // Obtener los datos con su relación de movimientos
        $utiles = Util::with('movimientos')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        $filename = 'reporte_completo_utiles_' . date('Y-m-d_H-i') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($utiles) {
            $file = fopen('php://output', 'w');
            // BOM para que Excel reconozca tildes y caracteres especiales en UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); 

            // ── SECCIÓN 1: STOCK ACTUAL ──
            fputcsv($file, ['REPORTE DE INVENTARIO ACTUAL'], ';');
            fputcsv($file, ['Nombre', 'Marca', 'Cantidad Actual', 'Unidad'], ';');
            foreach ($utiles as $util) {
                fputcsv($file, [$util->nombre, $util->marca, $util->cantidad, $util->unidad], ';');
            }

            // Espacio separador
            fputcsv($file, [], ';');
            fputcsv($file, [], ';');

            // ── SECCIÓN 2: HISTORIAL DE MOVIMIENTOS ──
            fputcsv($file, ['HISTORIAL DETALLADO DE MOVIMIENTOS'], ';');
            fputcsv($file, ['Artículo', 'Marca', 'Tipo', 'Cantidad', 'Descripción/Motivo', 'Fecha'], ';');
            
            foreach ($utiles as $util) {
                foreach ($util->movimientos as $mov) {
                    fputcsv($file, [
                        $util->nombre,
                        $util->marca,
                        $mov->tipo,
                        $mov->cantidad,
                        $mov->descripcion,
                        $mov->fecha
                    ], ';');
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}