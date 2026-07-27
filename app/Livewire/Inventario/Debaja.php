<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Debaja as DebajaModel; 
use App\Models\Personal;
use App\Models\Tecnologia; // Asegúrate de importar tus modelos originales
use App\Models\Mobiliario; // Importa otros modelos según tus tipos
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
     * Consulta centralizada con filtros
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
            'categorias' => DebajaModel::select('tipo_inventario')->distinct()->pluck('tipo_inventario')
        ]);
    }

    public function restaurar($id)
    {
        DB::beginTransaction();

        try {
            $baja = DebajaModel::findOrFail($id);
            $nuevoRegistro = null;

            // 1. Identificar modelo y tabla de origen
            // Usamos match para mayor claridad
            $nuevoRegistro = match ($baja->tipo_inventario) {
                'Mueble', 'Mobiliario' => new \App\Models\Mobiliario(),
                'Equipo', 'Tecnologia' => new \App\Models\Tecnologia(),
                default => null,
            };

            if (!$nuevoRegistro) {
                throw new \Exception("No se reconoció el tipo de inventario: " . $baja->tipo_inventario);
            }

            // 2. Mapear datos básicos comunes
            $nuevoRegistro->nombre      = $baja->nombre;
            $nuevoRegistro->personal_id = $baja->personal_id;
            $nuevoRegistro->imagen      = $baja->imagen;
            
            // IMPORTANTE: Definir el estado inicial al volver
            // Ajusta 'Activo' por el nombre de estado que uses (ej: 'Disponible')
            $nuevoRegistro->estado      = 'Activo'; 

            // 3. Restaurar campos específicos (Marca, Serie, Modelo, etc.)
            // Como en la tabla 'debajas' estos datos están en un JSON llamado 'detalles'
            if ($baja->detalles && is_array($baja->detalles)) {
                foreach ($baja->detalles as $columna => $valor) {
                    // Asignamos dinámicamente cada valor a su columna original
                    $nuevoRegistro->{$columna} = $valor;
                }
            }

            // 4. Guardar en tabla original y borrar de bajas
            $nuevoRegistro->save();
            $baja->delete();

            DB::commit();

            $this->dispatch('mueble-guardado', msg: 'El artículo ha vuelto a su inventario original.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Error al deshacer baja: " . $e->getMessage());
            $this->dispatch('mueble-guardado', msg: 'Error: ' . $e->getMessage());
        }
    }

    public function eliminarPermanente($id)
    {
        $registro = DebajaModel::findOrFail($id);

        if ($registro->imagen) {
            Storage::disk('local')->delete($registro->imagen);
        }

        $registro->delete();

        $this->dispatch('mueble-guardado', msg: 'Registro y archivo eliminados definitivamente');
    }

    public function exportarCSV()
    {
        $fileName = 'reporte_bajas_' . date('Y-m-d_H-i-s') . '.csv';
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
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
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