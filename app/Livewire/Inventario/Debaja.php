<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Debaja as DebajaModel; 
use App\Models\Personal;
use Illuminate\Support\Facades\Storage;

class Debaja extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    // Propiedades de búsqueda y filtros
    public $search = '';
    public $filterPersonal = '';
    public $filterCategoria = '';

    // Resetear la página cuando cambian los filtros
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterPersonal() { $this->resetPage(); }
    public function updatingFilterCategoria() { $this->resetPage(); }

    /**
     * Centralizamos la consulta para que render() y exportar() 
     * manejen siempre los mismos filtros.
     */
    public function getFilteredQuery()
    {
        return DebajaModel::with('personal')
            ->when($this->search, function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%');
            })
            ->when($this->filterPersonal, function($query) {
                $query->where('personal_id', $this->filterPersonal);
            })
            ->when($this->filterCategoria, function($query) {
                $query->where('tipo_inventario', $this->filterCategoria);
            });
    }

    public function render()
    {
        $bajas = $this->getFilteredQuery()->latest()->paginate(10);

        return view('livewire.inventario.debaja', [
            'bajas' => $bajas,
            'personal_list' => Personal::orderBy('nombre')->get(),
            // Obtenemos las categorías únicas que existen en la tabla de bajas
            'categorias' => DebajaModel::select('tipo_inventario')->distinct()->pluck('tipo_inventario')
        ]);
    }

    public function eliminarPermanente($id)
    {
        $registro = DebajaModel::findOrFail($id);

        if ($registro->imagen) {
            Storage::disk('local')->delete($registro->imagen);
        }

        $registro->delete();

        $this->dispatch('mueble-guardado', msg: 'Registro y archivo eliminados correctamente');
    }

    public function exportarCSV()
    {
        $fileName = 'reporte_bajas_' . date('Y-m-d_H-i-s') . '.csv';

        // Usamos la misma consulta filtrada para el reporte
        $registros = $this->getFilteredQuery()->orderBy('id', 'asc')->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($registros) {
            $file = fopen('php://output', 'w');
            
            // BOM para tildes
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Cabeceras
            $columns = ['ID', 'ARTICULO', 'CATEGORIA', 'RESPONSABLE', 'FECHA DE BAJA', 'MOTIVO'];
            fputcsv($file, $columns, ';');

            foreach ($registros as $reg) {
                $fila = [
                    $reg->id,
                    str_replace([';', "\n", "\r"], ' ', $reg->nombre ?? ''),
                    $reg->tipo_inventario,
                    $reg->personal ? ($reg->personal->nombre . ' ' . $reg->personal->apellido) : 'Sin asignar',
                    $reg->fecha_baja ? $reg->fecha_baja->format('d/m/Y') : $reg->created_at->format('d/m/Y'),
                    $reg->motivo
                ];
                fputcsv($file, $fila, ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}