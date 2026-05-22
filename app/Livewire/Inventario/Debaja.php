<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Debaja as DebajaModel; 
use Illuminate\Support\Facades\Storage; // IMPORTANTE: Para manejar el borrado de archivos

class Debaja extends Component
{
    use WithPagination;

    #[Layout('layouts.app')]

    public $search = '';

    public function render()
    {
        // Buscamos en la tabla de bajas
        $bajas = DebajaModel::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('tipo_inventario', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.inventario.debaja', [
            'bajas' => $bajas
        ]);
    }

    public function eliminarPermanente($id)
    {
        $registro = DebajaModel::findOrFail($id);

        // 1. Borrar el archivo físico del disco local si existe
        if ($registro->imagen) {
            // Esto busca en storage/app/ y borra la ruta guardada
            Storage::disk('local')->delete($registro->imagen);
        }

        // 2. Borrar el registro de la base de datos
        $registro->delete();

        // 3. Despachar mensaje de éxito (ajusta el nombre del evento si usas otro en tu JS)
        $this->dispatch('mueble-guardado', msg: 'Registro y archivo eliminados correctamente');
    }

    public function exportarCSV()
    {
        $fileName = 'reporte_bajas_' . date('Y-m-d_H-i-s') . '.csv';

        // Cambiamos latest() por orderBy('id', 'asc') para que el ID 1 sea el primero
        $registros = DebajaModel::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('tipo_inventario', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'asc') // <--- ORDENAR POR ID ASCENDENTE
            ->get();

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
            $columns = ['ID', 'ARTICULO', 'CATEGORIA', 'RESPONSABLE', 'FECHA DE BAJA'];
            
            // ';' como separador y chr(0) para quitar comillas
            fputcsv($file, $columns, ';', chr(0));

            foreach ($registros as $reg) {
                // Limpieza básica de datos para no romper columnas
                $fila = [
                    $reg->id,
                    str_replace([';', "\n", "\r"], ' ', $reg->nombre ?? ''),
                    str_replace([';', "\n", "\r"], ' ', $reg->tipo_inventario ?? ''),
                    $reg->personal ? ($reg->personal->nombre . ' ' . $reg->personal->apellido) : 'Sin asignar',
                    $reg->fecha_baja ? $reg->fecha_baja->format('d/m/Y') : $reg->created_at->format('d/m/Y'),
                ];

                fputcsv($file, $fila, ';', chr(0));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}