<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination; // Importante para paginación
use App\Models\Mobiliario;
use App\Models\Personal;
use App\Models\Debaja; 
use Illuminate\Support\Facades\Storage;

class Inventariomobi extends Component
{
    use WithFileUploads;
    use WithPagination; // Habilitar paginación

    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $nombre, $material, $color, $estado = 'Bueno', $lugar = 'Oficina principal', $personal_id, $mueble_id;
    public $imagen; 
    public $imagen_actual; 
    
    // Propiedades de búsqueda y filtros
    public $search = '';
    public $filtroLugar = '';
    public $filtroPersonal = '';
    
    public $isOpen = false;

    // Resetear paginación cuando cambian los filtros
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFiltroLugar() { $this->resetPage(); }
    public function updatingFiltroPersonal() { $this->resetPage(); }

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3',
            'material' => 'required',
            'color' => 'required',
            'estado' => 'required|in:Bueno,Regular,A la basura',
            'lugar' => 'required',
            'personal_id' => 'nullable|exists:personal,id',
            'imagen' => 'nullable|image|max:2048',
        ];
    }

    public function render()
    {
        // Consulta base con relaciones
        $query = Mobiliario::with('personal');

        // Filtro por búsqueda general (Nombre o Material)
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('material', 'like', '%' . $this->search . '%');
            });
        }

        // Filtro por Lugar
        if ($this->filtroLugar) {
            $query->where('lugar', $this->filtroLugar);
        }

        // Filtro por Encargado (Personal)
        if ($this->filtroPersonal) {
            $query->where('personal_id', $this->filtroPersonal);
        }

        // Obtener lista de lugares únicos para el select del filtro (opcional, o puedes hardcodearlos)
        $lugaresDisponibles = Mobiliario::select('lugar')->distinct()->pluck('lugar');

        return view('livewire.inventario.inventariomobi', [
            'muebles' => $query->latest()->paginate(10), // Paginación de 10
            'personal_list' => Personal::all(),
            'lugares_list' => $lugaresDisponibles
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

        if ($this->estado === 'A la basura') {
            $rutaImagen = $this->imagen_actual;
            if ($this->imagen) {
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

            if ($this->mueble_id) {
                $muebleActivo = Mobiliario::find($this->mueble_id);
                if ($muebleActivo) { $muebleActivo->delete(); }
            }

            $msg = 'Artículo movido al historial de bajas correctamente.';
        } else {
            $datos = [
                'nombre'      => $this->nombre,
                'material'    => $this->material,
                'color'       => $this->color,
                'estado'      => $this->estado,
                'lugar'       => $this->lugar,
                'personal_id' => $this->personal_id ?: null,
            ];

            if ($this->imagen) {
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
        if ($mueble && $mueble->imagen) {
            Storage::disk('local')->delete($mueble->imagen);
        }
        if ($mueble) { $mueble->delete(); }
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
        $this->lugar = 'Oficina principal'; 
        $this->personal_id = ''; 
        $this->mueble_id = '';
        $this->imagen = null; 
        $this->imagen_actual = null;
    }

    public function exportar()
    {
        // Aplicamos los mismos filtros que en el render para que el reporte sea fiel a lo que ve el usuario
        $query = Mobiliario::with('personal');

        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('material', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filtroLugar) {
            $query->where('lugar', $this->filtroLugar);
        }

        if ($this->filtroPersonal) {
            $query->where('personal_id', $this->filtroPersonal);
        }

        $muebles = $query->get();

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