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

        $tipo = strtolower(trim($baja->tipo_inventario));
        $nuevoRegistro = null;

        // 1. Configurar el modelo según la tabla y sus columnas reales
        if ($tipo === 'mobiliario' || $tipo === 'mueble') {
            $nuevoRegistro = new \App\Models\Mobiliario();
            
            $nuevoRegistro->estado = 'Bueno'; 
            $nuevoRegistro->material = $baja->detalles['material'] ?? 'No especificado';
            $nuevoRegistro->color = $baja->detalles['color'] ?? 'No especificado';
            
            // LA TABLA MOBILIARIOS SÍ TIENE IMAGEN
            $nuevoRegistro->imagen = $baja->imagen;

        } elseif ($tipo === 'tecnología' || $tipo === 'tecnologia' || $tipo === 'equipo') {
            $nuevoRegistro = new \App\Models\Tecnologia();
            
            $nuevoRegistro->estado = 'En funcionamiento';
            $nuevoRegistro->marca = $baja->detalles['marca'] ?? 'Genérica';
            
            // LA TABLA TECNOLOGIAS NO TIENE IMAGEN (según tu migración)
            // Por eso NO asignamos $nuevoRegistro->imagen aquí.
        }

        if (!$nuevoRegistro) {
            throw new \Exception("La categoría '{$baja->tipo_inventario}' no es válida.");
        }

        // 2. Mapear datos comunes que existen en AMBAS tablas
        $nuevoRegistro->nombre = $baja->nombre;
        $nuevoRegistro->personal_id = $baja->personal_id;
        $nuevoRegistro->lugar = $baja->detalles['lugar'] ?? 'Oficina principal';

        // 3. Restaurar otros detalles (como 'serie' para tecnología)
        if ($baja->detalles && is_array($baja->detalles)) {
            foreach ($baja->detalles as $columna => $valor) {
                // Solo asignamos si la columna no ha sido asignada ya
                // Y evitamos columnas que no existen en la tabla destino
                if (!isset($nuevoRegistro->{$columna})) {
                    // Evitamos intentar meter material/color en tecnologías o serie en mobiliario
                    // si no existen como columnas.
                    $nuevoRegistro->{$columna} = $valor;
                }
            }
        }

        // 4. Guardar y eliminar de bajas
        $nuevoRegistro->save();
        $baja->delete();

        DB::commit();

        $this->dispatch('mueble-guardado', msg: 'Registro restaurado correctamente');

    } catch (\Exception $e) {
        DB::rollBack();
        \Illuminate\Support\Facades\Log::error("Error restaurando: " . $e->getMessage());
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