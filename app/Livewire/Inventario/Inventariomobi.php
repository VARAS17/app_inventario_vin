<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Mobiliario;
use App\Models\Personal;
use App\Models\Debaja; 
use Illuminate\Support\Facades\Storage;

class Inventariomobi extends Component
{
    use WithFileUploads;

    #[Layout('layouts.app')]

    // Valores iniciales respetando las mayúsculas de tu migración
    public $nombre, $material, $color, $estado = 'Bueno', $lugar = 'Oficina Principal', $personal_id, $mueble_id;
    public $imagen; 
    public $imagen_actual; 
    public $search = '';
    public $isOpen = false;

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3',
            'material' => 'required',
            'color' => 'required',
            // Validación exacta para tu ENUM de base de datos
            'estado' => 'required|in:Bueno,Regular,A la basura',
            'lugar' => 'required',
            'personal_id' => 'nullable|exists:personal,id',
            'imagen' => 'nullable|image|max:2048',
        ];
    }

    public function render()
    {
        $muebles = Mobiliario::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('material', 'like', '%' . $this->search . '%')
                      ->orWhere('lugar', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        return view('livewire.inventario.inventariomobi', [
            'muebles' => $muebles,
            'personal_list' => Personal::all()
        ]);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $mueble = Mobiliario::findOrFail($id);
        $this->mueble_id = $id;
        $this->nombre = $mueble->nombre;
        $this->material = $mueble->material;
        $this->color = $mueble->color;
        $this->estado = $mueble->estado;
        $this->lugar = $mueble->lugar;
        $this->personal_id = $mueble->personal_id;
        $this->imagen_actual = $mueble->imagen; 

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        // --- CASO A: EL ARTÍCULO SE DA DE BAJA ---
        if ($this->estado === 'A la basura') {
            
            $rutaImagen = $this->imagen_actual;
            if ($this->imagen) {
                // Si sube imagen nueva, se guarda en disco local
                $rutaImagen = $this->imagen->store('mobiliarios', 'local');
            }

            Debaja::create([
                'nombre'          => $this->nombre,
                'tipo_inventario' => 'Mobiliario',
                'motivo'          => 'A la basura',
                'fecha_baja'      => now(),
                'personal_id'     => $this->personal_id ?: null,
                'imagen'          => $rutaImagen,
                'detalles'        => [
                    'material' => $this->material,
                    'color'    => $this->color,
                    'lugar'    => $this->lugar,
                ],
            ]);

            // Si el mueble existía en la tabla activa, se elimina
            if ($this->mueble_id) {
                $muebleActivo = Mobiliario::find($this->mueble_id);
                if ($muebleActivo) {
                    $muebleActivo->delete();
                }
            }

            $msg = 'Artículo movido al historial de bajas correctamente.';

        } else {
            // --- CASO B: GUARDADO O ACTUALIZACIÓN NORMAL ---
            $datos = [
                'nombre'      => $this->nombre,
                'material'    => $this->material,
                'color'       => $this->color,
                'estado'      => $this->estado,
                'lugar'       => $this->lugar,
                'personal_id' => $this->personal_id ?: null,
            ];

            if ($this->imagen) {
                // Borrar imagen anterior del disco local si existe
                if ($this->mueble_id && $this->imagen_actual) {
                    Storage::disk('local')->delete($this->imagen_actual);
                }
                $datos['imagen'] = $this->imagen->store('mobiliarios', 'local');
            }

            Mobiliario::updateOrCreate(['id' => $this->mueble_id], $datos);
            
            $msg = $this->mueble_id ? 'Mueble actualizado correctamente' : 'Mueble registrado con éxito';
        }

        $this->dispatch('mueble-guardado', msg: $msg);
        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        $mueble = Mobiliario::find($id);
        
        // Borrar archivo físico del disco local
        if ($mueble && $mueble->imagen) {
            Storage::disk('local')->delete($mueble->imagen);
        }

        if ($mueble) {
            $mueble->delete();
        }

        $this->dispatch('mueble-guardado', msg: 'Mueble eliminado permanentemente');
    }

    public function openModal() { $this->isOpen = true; }
    
    public function closeModal() { 
        $this->isOpen = false; 
        $this->resetInputFields();
    }

    private function resetInputFields() {
        $this->nombre = ''; 
        $this->material = ''; 
        $this->color = '';
        $this->estado = 'Bueno'; 
        $this->lugar = 'Oficina principal'; // Coincide con el Enum de tu migración
        $this->personal_id = ''; 
        $this->mueble_id = '';
        $this->imagen = null; 
        $this->imagen_actual = null;
    }

    public function exportar()
{
    $muebles = \App\Models\Mobiliario::with('personal')
        ->where(function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('material', 'like', '%' . $this->search . '%')
                  ->orWhere('lugar', 'like', '%' . $this->search . '%');
        })
        ->get();

    $filename = 'reporte_inventario_mobi_' . date('Y-m-d_H-i-s') . '.csv';
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($muebles) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($file, ['Nombre', 'Material', 'Color', 'Estado', 'Lugar', 'Asignado a'], ';');

        foreach ($muebles as $mueble) {
            fputcsv($file, [
                $mueble->nombre,
                $mueble->material,
                $mueble->color,
                $mueble->estado,
                $mueble->lugar,
                $mueble->personal
                    ? $mueble->personal->nombre . ' ' . $mueble->personal->apellido
                    : 'Sin asignar',
            ], ';');
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}