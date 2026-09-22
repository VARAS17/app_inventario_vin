<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\Mobiliario;
use App\Models\Personal;
use App\Models\Debaja; 
use Illuminate\Support\Facades\Storage;
use Exception;

class Inventariomobi extends Component
{
    use WithFileUploads;
    use WithPagination;

    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $mueble_id, $nombre, $material, $color, $estado = 'Bueno', $lugar = 'Oficina principal', $personal_id;
    public $imagen; 
    public $imagen_actual; 
    
    // Filtros
    public $search = '';
    public $filtroLugar = '';
    public $filtroPersonal = '';
    
    // Estado del modal
    public $isOpen = false;

    /**
     * Reglas de validación
     */
    protected function rules()
    {
        return [
            'nombre'      => 'required|min:3|max:100',
            'material'    => 'required|string',
            'color'       => 'required|string',
            'estado'      => 'required|in:Bueno,Regular,A la basura',
            'lugar'       => 'required|string',
            'personal_id' => 'nullable|exists:personal,id',
            'imagen'      => 'nullable|image|max:2048', // Máximo 2MB
        ];
    }

    /**
     * Mensajes de error personalizados
     */
    protected function messages()
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.min'      => 'El nombre debe tener al menos 3 caracteres.',
            'material.required' => 'El material es obligatorio.',
            'color.required'    => 'El color es obligatorio.',
            'estado.required'   => 'Seleccione el estado actual.',
            'lugar.required'    => 'La ubicación es obligatoria.',
            'imagen.image'      => 'El archivo debe ser una imagen.',
            'imagen.max'        => 'La imagen no debe pesar más de 2MB.',
        ];
    }

    /**
     * Resetear paginación cuando cambian los filtros
     */
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFiltroLugar() { $this->resetPage(); }
    public function updatingFiltroPersonal() { $this->resetPage(); }

    /**
     * Validación en tiempo real
     */
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        $query = Mobiliario::with('personal');

        // Búsqueda por nombre o material
        if ($this->search) {
            $query->where(function($q) {
                $q->where('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('material', 'like', '%' . $this->search . '%');
            });
        }

        // Filtros específicos
        if ($this->filtroLugar) {
            $query->where('lugar', $this->filtroLugar);
        }

        if ($this->filtroPersonal) {
            $query->where('personal_id', $this->filtroPersonal);
        }

        // Obtener lista de lugares únicos que ya existen en la base de datos
        $lugaresDisponibles = Mobiliario::select('lugar')->distinct()->pluck('lugar');

        return view('livewire.inventario.inventariomobi', [
            'muebles'       => $query->latest()->paginate(10),
            'personal_list' => Personal::orderBy('nombre')->get(),
            'lugares_list'  => $lugaresDisponibles
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
        $this->mueble_id     = $id;
        $this->nombre        = $mueble->nombre;
        $this->material      = $mueble->material;
        $this->color         = $mueble->color;
        $this->estado        = $mueble->estado;
        $this->lugar         = $mueble->lugar;
        $this->personal_id   = $mueble->personal_id;
        $this->imagen_actual = $mueble->imagen; 

        $this->openModal();
    }

    public function guardar()
    {
        $this->validate();

        try {
            if ($this->estado === 'A la basura') {
                $this->procesarBaja();
                $msg = 'Artículo movido al historial de bajas correctamente.';
            } else {
                $this->procesarPersistencia();
                $msg = $this->mueble_id ? 'Mueble actualizado con éxito.' : 'Mueble registrado con éxito.';
            }

            $this->dispatch('mueble-guardado', msg: $msg);
            $this->closeModal();
            $this->resetInputFields();

        } catch (Exception $e) {
            session()->flash('error', 'Error al procesar: ' . $e->getMessage());
        }
    }

    private function procesarBaja()
    {
        // Si hay una nueva imagen subida, usar esa, si no, la actual
        $rutaFinal = $this->imagen ? $this->imagen->store('mobiliarios', 'local') : $this->imagen_actual;

        Debaja::create([
            'nombre'          => $this->nombre,
            'tipo_inventario' => 'Mobiliario',
            'motivo'          => 'A la basura',
            'fecha_baja'      => now(),
            'personal_id'     => $this->personal_id ?: null,
            'imagen'          => $rutaFinal,
            'detalles'        => [
                'material' => $this->material,
                'color'    => $this->color,
                'lugar'    => $this->lugar,
            ],
        ]);

        // Si el mueble existía en la tabla principal, se elimina
        if ($this->mueble_id) {
            $mueble = Mobiliario::find($this->mueble_id);
            if ($mueble) $mueble->delete();
        }
    }

    private function procesarPersistencia()
    {
        $datos = [
            'nombre'      => $this->nombre,
            'material'    => $this->material,
            'color'       => $this->color,
            'estado'      => $this->estado,
            'lugar'       => $this->lugar,
            'personal_id' => $this->personal_id ?: null,
        ];

        // Manejo de imagen
        if ($this->imagen) {
            // Eliminar imagen anterior si existe y se está subiendo una nueva
            if ($this->mueble_id && $this->imagen_actual) {
                Storage::disk('local')->delete($this->imagen_actual);
            }
            $datos['imagen'] = $this->imagen->store('mobiliarios', 'local');
        }

        Mobiliario::updateOrCreate(['id' => $this->mueble_id], $datos);
    }

    public function eliminar($id)
    {
        try {
            $mueble = Mobiliario::findOrFail($id);
            if ($mueble->imagen) {
                Storage::disk('local')->delete($mueble->imagen);
            }
            $mueble->delete();
            $this->dispatch('mueble-guardado', msg: 'Registro eliminado permanentemente.');
        } catch (Exception $e) {
            $this->dispatch('mueble-error', msg: 'No se pudo eliminar el registro.');
        }
    }

    public function exportar()
    {
        $query = Mobiliario::with('personal');

        // Aplicar los mismos filtros que en la tabla
        if ($this->search) {
            $query->where('nombre', 'like', '%' . $this->search . '%');
        }
        if ($this->filtroLugar) {
            $query->where('lugar', $this->filtroLugar);
        }
        if ($this->filtroPersonal) {
            $query->where('personal_id', $this->filtroPersonal);
        }

        $muebles = $query->get();
        $filename = 'reporte_mobiliario_' . now()->format('Y-m-d_His') . '.csv';

        $callback = function() use ($muebles) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8
            fputcsv($file, ['ID', 'Nombre', 'Material', 'Color', 'Estado', 'Ubicación', 'Responsable'], ';');

            foreach ($muebles as $m) {
                fputcsv($file, [
                    $m->id,
                    $m->nombre,
                    $m->material,
                    $m->color,
                    $m->estado,
                    $m->lugar,
                    $m->personal ? $m->personal->nombre . ' ' . $m->personal->apellido : 'Sin asignar'
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    public function openModal() { $this->isOpen = true; }
    
    public function closeModal() 
    { 
        $this->isOpen = false; 
        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetInputFields() 
    {
        $this->mueble_id     = null;
        $this->nombre        = ''; 
        $this->material      = ''; 
        $this->color         = '';
        $this->estado        = 'Bueno'; 
        $this->lugar         = 'Oficina principal'; 
        $this->personal_id   = ''; 
        $this->imagen        = null; 
        $this->imagen_actual = null;
    }
}