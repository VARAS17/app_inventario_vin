<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\entregas_personal;
use App\Models\entrega_detalle;
use App\Models\utiles;
use App\Models\movimientos_utiles;
use App\Models\Personal;
use Illuminate\Support\Facades\DB;
use Exception;

class EntregaPersonal extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    // Modales
    public $isOpenCrear = false;
    public $isOpenDetalle = false;

    // Filtros de búsqueda
    public $search = '';

    // Formulario de Entrega
    public $personal_id;
    public $fecha_entrega;
    public $observaciones;
    public $itemsEntrega = []; // [['util_id' => 1, 'nombre' => '...', 'unidad' => '...', 'stock_actual' => 10, 'cantidad' => 1]]

    // Combobox Mixto de Útiles
    public $searchUtil = '';
    public $mostrarDropdownUtil = false;

    // Entrega seleccionada para ver el vale
    public $entregaSeleccionada;

    public function mount()
    {
        $this->fecha_entrega = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        $entregas = entregas_personal::with(['personal.area', 'detalles.util'])
            ->whereHas('personal', function ($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('apellido', 'like', '%' . $this->search . '%');
            })
            ->orderBy('fecha_entrega', 'desc')
            ->latest('id')
            ->paginate(10);

        $listaPersonal = Personal::with('area')->orderBy('nombre', 'asc')->get();

        // IDs que ya están en la tabla de entrega (para excluirlos del Combobox)
        $idsSeleccionados = array_column($this->itemsEntrega, 'util_id');

        // Útiles con stock disponible (>0) que aún no hayan sido seleccionados
        $utilesDisponibles = utiles::where('stock_actual', '>', 0)
            ->whereNotIn('id', $idsSeleccionados)
            ->when($this->searchUtil, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->searchUtil . '%')
                        ->orWhere('marca', 'like', '%' . $this->searchUtil . '%');
                });
            })
            ->orderBy('nombre', 'asc')
            ->take(20)
            ->get();

        return view('livewire.inventario.entrega-personal', [
            'entregas'          => $entregas,
            'listaPersonal'     => $listaPersonal,
            'utilesDisponibles' => $utilesDisponibles
        ]);
    }

    // --- COMBOBOX MIXTO ---

    public function abrirModalCrear()
    {
        $this->reset(['personal_id', 'observaciones', 'searchUtil']);
        $this->fecha_entrega = date('Y-m-d');
        $this->itemsEntrega = [];
        $this->mostrarDropdownUtil = false;
        $this->isOpenCrear = true;
    }

    public function toggleDropdownUtil($estado = null)
    {
        $this->mostrarDropdownUtil = is_null($estado) ? !$this->mostrarDropdownUtil : $estado;
    }

    public function seleccionarUtil($utilId)
    {
        $util = utiles::findOrFail($utilId);

        // Se agrega a la lista de entrega con cantidad inicial 1
        $this->itemsEntrega[] = [
            'util_id'      => $util->id,
            'nombre'       => $util->nombre,
            'marca'        => $util->marca ?? 'S/M',
            'unidad'       => $util->unidad,
            'stock_actual' => $util->stock_actual,
            'cantidad'     => 1,
        ];

        // Limpiar el buscador y cerrar el dropdown
        $this->searchUtil = '';
        $this->mostrarDropdownUtil = false;
    }

    public function eliminarFilaItem($index)
    {
        // Al quitarlo, automáticamente vuelve a aparecer disponible en el combobox
        unset($this->itemsEntrega[$index]);
        $this->itemsEntrega = array_values($this->itemsEntrega);
    }

    // --- GUARDAR ENTREGA ---

    public function guardarEntrega()
    {
        $this->validate([
            'personal_id'                 => 'required|exists:personal,id',
            'fecha_entrega'               => 'required|date',
            'itemsEntrega'                => 'required|array|min:1',
            'itemsEntrega.*.util_id'      => 'required|exists:utiles,id',
            'itemsEntrega.*.cantidad'     => 'required|integer|min:1',
        ], [
            'personal_id.required'        => 'Seleccione al personal que recibe los útiles.',
            'itemsEntrega.min'            => 'Debe agregar al menos un útil a la entrega.',
            'itemsEntrega.*.cantidad.min' => 'La cantidad mínima es 1.',
        ]);

        // Validación estricta de stock disponible en armario
        foreach ($this->itemsEntrega as $item) {
            $util = utiles::findOrFail($item['util_id']);
            if ($item['cantidad'] > $util->stock_actual) {
                session()->flash('error', "Stock insuficiente para '{$util->nombre}'. En armario solo quedan: {$util->stock_actual} {$util->unidad}.");
                return;
            }
        }

        try {
            DB::beginTransaction();

            $persona = Personal::findOrFail($this->personal_id);

            // 1. Cabecera del vale de entrega
            $entrega = entregas_personal::create([
                'personal_id'   => $this->personal_id,
                'fecha_entrega' => $this->fecha_entrega,
                'observaciones' => $this->observaciones,
            ]);

            // 2. Guardar detalles y descontar stock
            foreach ($this->itemsEntrega as $item) {
                $cant = (int)$item['cantidad'];
                $util = utiles::findOrFail($item['util_id']);

                entrega_detalle::create([
                    'entrega_id'         => $entrega->id,
                    'util_id'            => $util->id,
                    'cantidad_entregada' => $cant,
                ]);

                // Descontar del stock real
                $stockAnterior = $util->stock_actual;
                $stockNuevo = $stockAnterior - $cant;
                $util->update(['stock_actual' => $stockNuevo]);

                // Asiento en Kardex
                movimientos_utiles::create([
                    'util_id'         => $util->id,
                    'tipo'            => 'Egreso',
                    'cantidad'        => $cant,
                    'stock_anterior'  => $stockAnterior,
                    'stock_nuevo'     => $stockNuevo,
                    'referencia_tipo' => 'Entrega',
                    'referencia_id'   => $entrega->id,
                    'descripcion'     => 'Entrega a ' . $persona->nombre . ' ' . $persona->apellido,
                    'fecha'           => $this->fecha_entrega,
                ]);
            }

            DB::commit();
            session()->flash('message', 'Entrega registrada y stock descontado con éxito.');
            $this->isOpenCrear = false;

        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al procesar la entrega: ' . $e->getMessage());
        }
    }

    public function verDetalle($id)
    {
        $this->entregaSeleccionada = entregas_personal::with([
            'personal.area',
            'detalles.util'
        ])->findOrFail($id);

        $this->isOpenDetalle = true;
    }

    public function cerrarModal($modal)
    {
        $this->$modal = false;
        $this->resetErrorBag();
    }
}