<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\requerimientos_anuales;
use App\Models\requerimientos_detalles;
use App\Models\recepciones_abastecimientos;
use App\Models\recepcion_detalles;
use App\Models\utiles;
use App\Models\movimientos_utiles;
use Illuminate\Support\Facades\DB;
use Exception;

class RequerimientoAnual extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Layout('layouts.app')]

    // Modales y Vistas
    public $isOpenCrear = false;
    public $isOpenDetalle = false;
    public $isOpenRecepcion = false;

    // Formulario Requerimiento Anual
    public $anio;
    public $codigo;
    public $documento_anual;
    public $observaciones;
    public $itemsRequerimiento = []; // [['util_id' => 1, 'nombre' => '...', 'unidad' => '...', 'marca' => '...', 'cantidad' => 1]]

    // Buscador Mixto de Útiles
    public $searchUtil = '';
    public $mostrarDropdownUtil = false;

    // Requerimiento seleccionado para ver o recibir
    public $requerimientoSeleccionado;

    // Formulario Recepción de Lote
    public $numero_documento;
    public $documento_recepcion;
    public $fecha_recepcion;
    public $obs_recepcion;
    public $cantidadesRecibidas = [];

    public function mount()
    {
        $this->anio = date('Y');
        $this->fecha_recepcion = date('Y-m-d');
    }

    public function render()
    {
        $requerimientos = requerimientos_anuales::with(['detalles.util', 'recepciones.detalles'])
            ->orderBy('anio', 'desc')
            ->paginate(5);

        // Obtener los IDs que ya fueron agregados para EXCLUIRLOS de la lista
        $idsSeleccionados = array_column($this->itemsRequerimiento, 'util_id');

        // Útiles disponibles para el buscador (excluyendo los ya elegidos)
        $utilesDisponibles = utiles::whereNotIn('id', $idsSeleccionados)
            ->when($this->searchUtil, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('nombre', 'like', '%' . $this->searchUtil . '%')
                        ->orWhere('marca', 'like', '%' . $this->searchUtil . '%');
                });
            })
            ->orderBy('nombre', 'asc')
            ->take(20) // Limita a 20 para máxima velocidad
            ->get();

        return view('livewire.inventario.requerimiento-anual', [
            'requerimientos'    => $requerimientos,
            'utilesDisponibles' => $utilesDisponibles
        ]);
    }

    // --- LÓGICA DEL BUSCADOR MIXTO ---

    public function abrirModalCrear()
    {
        $this->reset(['codigo', 'documento_anual', 'observaciones', 'searchUtil']);
        $this->anio = date('Y');
        $this->itemsRequerimiento = [];
        $this->mostrarDropdownUtil = false;
        $this->isOpenCrear = true;
    }

    public function seleccionarUtil($utilId)
    {
        $util = utiles::findOrFail($utilId);

        // Se agrega a la tabla con cantidad inicial de 1
        $this->itemsRequerimiento[] = [
            'util_id'  => $util->id,
            'nombre'   => $util->nombre,
            'marca'    => $util->marca ?? 'S/M',
            'unidad'   => $util->unidad,
            'cantidad' => 1,
        ];

        // Limpiar el buscador y cerrar el dropdown
        $this->searchUtil = '';
        $this->mostrarDropdownUtil = false;
    }

    public function eliminarFilaItem($index)
    {
        // Al eliminarlo de la lista, automáticamente vuelve a aparecer en el buscador
        unset($this->itemsRequerimiento[$index]);
        $this->itemsRequerimiento = array_values($this->itemsRequerimiento);
    }

    public function toggleDropdownUtil($estado = null)
    {
        $this->mostrarDropdownUtil = is_null($estado) ? !$this->mostrarDropdownUtil : $estado;
    }

    public function guardarRequerimiento()
    {
        $this->validate([
            'anio'                          => 'required|integer|digits:4|unique:requerimientos_anuales,anio',
            'codigo'                        => 'nullable|string|max:100',
            'documento_anual'               => 'nullable|file|mimes:pdf|max:10240',
            'itemsRequerimiento'            => 'required|array|min:1',
            'itemsRequerimiento.*.util_id'  => 'required|exists:utiles,id',
            'itemsRequerimiento.*.cantidad' => 'required|integer|min:1',
        ], [
            'anio.unique' => 'Ya existe un requerimiento para este año.',
            'itemsRequerimiento.min' => 'Debe agregar al menos un útil al requerimiento.',
            'itemsRequerimiento.*.cantidad.min' => 'La cantidad mínima es 1.',
        ]);

        try {
            DB::beginTransaction();

            $docPath = null;
            if ($this->documento_anual) {
                $docPath = $this->documento_anual->store('requerimientos', 'local');
            }

            $req = requerimientos_anuales::create([
                'anio'           => $this->anio,
                'codigo'         => $this->codigo,
                'documento_path' => $docPath,
                'estado'         => 'Abierto',
                'observaciones'  => $this->observaciones,
            ]);

            foreach ($this->itemsRequerimiento as $item) {
                requerimientos_detalles::create([
                    'requerimiento_anual_id' => $req->id,
                    'util_id'                => $item['util_id'],
                    'cantidad_solicitada'    => $item['cantidad'],
                ]);
            }

            DB::commit();
            session()->flash('message', 'Requerimiento anual registrado con éxito.');
            $this->isOpenCrear = false;

        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    // --- DETALLES Y RECEPCIÓN DE LOTES (Se mantienen iguales) ---

    public function verDetalle($id)
    {
        $this->requerimientoSeleccionado = requerimientos_anuales::with([
            'detalles.util',
            'recepciones.detalles.util'
        ])->findOrFail($id);

        $this->isOpenDetalle = true;
    }

    public function abrirModalRecepcion($id)
        {
            // Cargar detalles del pedido y también las recepciones pasadas
            $this->requerimientoSeleccionado = requerimientos_anuales::with([
                'detalles.util',
                'recepciones.detalles'
            ])->findOrFail($id);

            $this->numero_documento = '';
            $this->documento_recepcion = null;
            $this->fecha_recepcion = date('Y-m-d');
            $this->obs_recepcion = '';
            $this->cantidadesRecibidas = [];

            foreach ($this->requerimientoSeleccionado->detalles as $det) {
                $this->cantidadesRecibidas[$det->util_id] = 0;
            }

            $this->isOpenRecepcion = true;
        }

    public function guardarRecepcion()
    {
        $this->validate([
            'numero_documento'    => 'required|string|max:100',
            'fecha_recepcion'     => 'required|date',
            'documento_recepcion' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
        ]);

        $hayItems = false;
        foreach ($this->cantidadesRecibidas as $utilId => $cant) {
            if ((int)$cant > 0) {
                $hayItems = true;
                break;
            }
        }

        if (!$hayItems) {
            session()->flash('error', 'Indique cantidad recibida en al menos un útil.');
            return;
        }

        try {
            DB::beginTransaction();

            $docPath = null;
            if ($this->documento_recepcion) {
                $docPath = $this->documento_recepcion->store('recepciones', 'local');
            }

            $recepcion = recepciones_abastecimientos::create([
                'requerimiento_anual_id' => $this->requerimientoSeleccionado->id,
                'numero_documento'       => $this->numero_documento,
                'documento_path'         => $docPath,
                'fecha_recepcion'        => $this->fecha_recepcion,
                'observaciones'          => $this->obs_recepcion,
            ]);

            foreach ($this->cantidadesRecibidas as $utilId => $cantidad) {
                $cant = (int)$cantidad;
                if ($cant > 0) {
                    recepcion_detalles::create([
                        'recepcion_id'      => $recepcion->id,
                        'util_id'           => $utilId,
                        'cantidad_recibida' => $cant,
                    ]);

                    $util = utiles::findOrFail($utilId);
                    $stockAnterior = $util->stock_actual;
                    $stockNuevo = $stockAnterior + $cant;
                    $util->update(['stock_actual' => $stockNuevo]);

                    movimientos_utiles::create([
                        'util_id'         => $utilId,
                        'tipo'            => 'Ingreso',
                        'cantidad'        => $cant,
                        'stock_anterior'  => $stockAnterior,
                        'stock_nuevo'     => $stockNuevo,
                        'referencia_tipo' => 'Recepcion',
                        'referencia_id'   => $recepcion->id,
                        'descripcion'     => 'Llegada de Abastecimiento Doc: ' . $this->numero_documento,
                        'fecha'           => $this->fecha_recepcion,
                    ]);
                }
            }

            DB::commit();
            session()->flash('message', 'Entrega registrada y stock actualizado.');
            $this->isOpenRecepcion = false;

            if ($this->isOpenDetalle) {
                $this->verDetalle($this->requerimientoSeleccionado->id);
            }

        } catch (Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Error al guardar llegada: ' . $e->getMessage());
        }
    }

    public function cerrarModal($modal)
    {
        $this->$modal = false;
        $this->resetErrorBag();
    }
}