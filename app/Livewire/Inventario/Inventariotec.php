<?php

namespace App\Livewire\Inventario;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Tecnologia;
use App\Models\Personal;
use App\Models\Debaja; // Importar el modelo de Bajas
use Illuminate\Support\Facades\Storage;

class Inventariotec extends Component
{
    use WithFileUploads;

    #[Layout('layouts.app')]

    // Propiedades
    public $nombre, $marca, $serie, $estado = 'En funcionamiento', $lugar = 'Oficina Principal', $personal_id, $equipo_id;
    public $imagen; 
    public $imagen_actual; 
    public $search = ''; 
    public $isOpen = false;

    // Reglas de validación
    protected function rules()
    {
        return [
            'nombre' => 'required|min:3',
            'marca' => 'required',
            'serie' => 'nullable',
            'estado' => 'required|in:En funcionamiento,Guardado,Malogrado', // Los 3 estados que pediste
            'lugar' => 'required',
            'personal_id' => 'nullable|exists:personal,id',
            'imagen' => 'nullable|image|max:2048',
        ];
    }

    public function render()
    {
        $equipos = Tecnologia::with('personal')
            ->where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('serie', 'like', '%' . $this->search . '%')
                      ->orWhere('lugar', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->get();

        return view('livewire.inventario.inventariotec', [
            'equipos' => $equipos,
            'personales' => Personal::all()
        ]);
    }

    public function crear()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function editar($id)
    {
        $equipo = Tecnologia::findOrFail($id);
        $this->equipo_id = $id;
        $this->nombre = $equipo->nombre;
        $this->marca = $equipo->marca;
        $this->serie = $equipo->serie;
        $this->estado = $equipo->estado;
        $this->lugar = $equipo->lugar; 
        $this->personal_id = $equipo->personal_id;
        $this->imagen_actual = $equipo->imagen; 

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        // --- CASO 1: EL EQUIPO ESTÁ MALOGRADO (SE MUEVE AL TACHO) ---
        if ($this->estado === 'Malogrado') {
            
            $rutaImagen = $this->imagen_actual;
            if ($this->imagen) {
                // Guardar en disco local para app escritorio
                $rutaImagen = $this->imagen->store('tecnologia', 'local');
            }

            Debaja::create([
                'nombre'          => $this->nombre,
                'tipo_inventario' => 'Tecnología',
                'motivo'          => 'Malogrado',
                'fecha_baja'      => now(),
                'personal_id'     => $this->personal_id ?: null,
                'imagen'          => $rutaImagen,
                'detalles'        => [
                    'marca' => $this->marca,
                    'serie' => $this->serie,
                    'lugar' => $this->lugar,
                ],
            ]);

            // Si el equipo existía en la tabla de tecnología, lo eliminamos
            if ($this->equipo_id) {
                $equipoActivo = Tecnologia::find($this->equipo_id);
                if ($equipoActivo) {
                    $equipoActivo->delete();
                }
            }

            $msg = 'Equipo tecnológico movido al historial de bajas.';

        } else {
            // --- CASO 2: GUARDADO NORMAL (EN FUNCIONAMIENTO O GUARDADO) ---
            $datos = [
                'nombre'      => $this->nombre,
                'marca'       => $this->marca,
                'serie'       => $this->serie,
                'estado'      => $this->estado, // Aquí se guarda "En funcionamiento" o "Guardado"
                'lugar'       => $this->lugar,
                'personal_id' => $this->personal_id ?: null,
            ];

            if ($this->imagen) {
                // Borrar imagen anterior del disco local si existe una nueva
                if ($this->equipo_id && $this->imagen_actual) {
                    Storage::disk('local')->delete($this->imagen_actual);
                }
                $datos['imagen'] = $this->imagen->store('tecnologia', 'local');
            }

            Tecnologia::updateOrCreate(['id' => $this->equipo_id], $datos);
            
            $msg = $this->equipo_id ? 'Equipo actualizado correctamente' : 'Equipo registrado con éxito';
        }

        // Despachar alerta (usamos el mismo evento que Mobiliario para ahorrar código JS)
        $this->dispatch('mueble-guardado', msg: $msg);
        $this->closeModal();
        $this->resetInputFields();
    }

    public function eliminar($id)
    {
        $equipo = Tecnologia::find($id);
        
        // Borrar imagen física
        if ($equipo && $equipo->imagen) {
            Storage::disk('local')->delete($equipo->imagen);
        }

        if ($equipo) {
            $equipo->delete();
        }

        $this->dispatch('mueble-guardado', msg: 'Equipo eliminado permanentemente');
    }

    public function openModal() { $this->isOpen = true; }
    
    public function closeModal() { 
        $this->isOpen = false; 
        $this->resetInputFields();
    }

    private function resetInputFields() {
        $this->nombre = ''; 
        $this->marca = ''; 
        $this->serie = '';
        $this->estado = 'En funcionamiento'; 
        $this->lugar = 'Oficina principal'; 
        $this->personal_id = ''; 
        $this->equipo_id = '';
        $this->imagen = null;
        $this->imagen_actual = null;
    }


public function exportar()
{
    $equipos = \App\Models\Tecnologia::with('personal')
        ->where(function($query) {
            $query->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('marca', 'like', '%' . $this->search . '%')
                  ->orWhere('serie', 'like', '%' . $this->search . '%')
                  ->orWhere('lugar', 'like', '%' . $this->search . '%');
        })
        ->get();

    $filename = 'reporte_inventario_tecno_' . date('Y-m-d_H-i-s') . '.csv';
    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"$filename\"",
    ];

    $callback = function() use ($equipos) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
        fputcsv($file, ['Nombre', 'Marca', 'Serie', 'Estado', 'Lugar', 'Asignado a'], ';');
        
        foreach ($equipos as $equipo) {
            fputcsv($file, [
                $equipo->nombre,
                $equipo->marca,
                $equipo->serie ?? 'S/N',
                $equipo->estado,
                $equipo->lugar,
                $equipo->personal 
                    ? $equipo->personal->nombre . ' ' . $equipo->personal->apellido 
                    : 'Sin asignar',
            ], ';');
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}