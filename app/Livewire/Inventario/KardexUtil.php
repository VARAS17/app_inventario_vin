<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\movimientos_utiles;
use App\Models\utiles;

class KardexUtil extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    // Filtros de búsqueda
    public $search = '';
    public $filtro_util_id = '';
    public $filtro_tipo = '';
    public $fecha_desde = '';
    public $fecha_hasta = '';

    // Modal de Cierre / Reporte
    public $isOpenCierre = false;
    public $anioCierre;

    public function mount()
    {
        $this->anioCierre = date('Y');
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingFiltroUtilId() { $this->resetPage(); }
    public function updatingFiltroTipo() { $this->resetPage(); }
    public function updatingFechaDesde() { $this->resetPage(); }
    public function updatingFechaHasta() { $this->resetPage(); }

    public function limpiarFiltros()
    {
        $this->reset(['search', 'filtro_util_id', 'filtro_tipo', 'fecha_desde', 'fecha_hasta']);
        $this->resetPage();
    }

    public function render()
    {
        $query = movimientos_utiles::with('util')
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('descripcion', 'like', '%' . $this->search . '%')
                        ->orWhereHas('util', function ($u) {
                            $u->where('nombre', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filtro_util_id, function ($q) {
                $q->where('util_id', $this->filtro_util_id);
            })
            ->when($this->filtro_tipo, function ($q) {
                $q->where('tipo', $this->filtro_tipo);
            })
            ->when($this->fecha_desde, function ($q) {
                $q->whereDate('fecha', '>=', $this->fecha_desde);
            })
            ->when($this->fecha_hasta, function ($q) {
                $q->whereDate('fecha', '<=', $this->fecha_hasta);
            });

        // Totales de resumen rápido
        $totalIngresos = (clone $query)->where('tipo', 'Ingreso')->sum('cantidad');
        $totalEgresos = (clone $query)->where('tipo', 'Egreso')->sum('cantidad');

        $movimientos = $query->orderBy('fecha', 'desc')
            ->latest('id')
            ->paginate(15);

        $catalogoUtiles = utiles::orderBy('nombre', 'asc')->get();

        return view('livewire.inventario.kardex-util', [
            'movimientos'    => $movimientos,
            'catalogoUtiles' => $catalogoUtiles,
            'totalIngresos'  => $totalIngresos,
            'totalEgresos'   => $totalEgresos,
        ]);
    }

    public function abrirModalCierre()
    {
        $this->isOpenCierre = true;
    }

    public function cerrarModal($modal)
    {
        $this->$modal = false;
    }
}