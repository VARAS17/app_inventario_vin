<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use Livewire\WithPagination; // 1. Importar el Trait
use App\Models\Tecnologia;
use App\Models\Personal;
use App\Models\Debaja; 
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class Inventariotec extends Component
{
    use WithFileUploads;
    use WithPagination; // 2. Usar el Trait dentro de la clase

    #[Layout('layouts.app')]

    // Propiedades del formulario
    public $nombre, $marca, $serie, $estado = 'En funcionamiento', $lugar = 'Oficina Principal', $personal_id, $equipo_id;
    public $imagen; 
    public $imagen_actual; 
    
    // Propiedades de Búsqueda y Filtros
    public $search = ''; 
    public $filterPersonal = ''; 
    public $filterLugar = '';    
    
    public $isOpen = false;

    // 3. Resetear la paginación automáticamente cuando cambian los filtros
    public function updatingSearch() { $this->resetPage(); }
    public function updatingFilterPersonal() { $this->resetPage(); }
    public function updatingFilterLugar() { $this->resetPage(); }

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3',
            'marca' => 'required',
            'serie' => 'nullable',
            'estado' => 'required|in:En funcionamiento,Guardado,En Mantenimiento,Reparacion,Dar de Baja',
            'lugar' => 'required',
            'personal_id' => 'nullable|exists:personal,id',
            'imagen' => 'nullable|image|max:2048',
        ];
    }

    /**
     * Renderiza la vista con los datos paginados
     */
    public function render()
    {
        return view('livewire.inventario.inventariotec', [
            'equipos' => $this->getFilteredQuery()->latest()->paginate(10), 
            'personales' => Personal::all(),
            'lugaresDisponibles' => Tecnologia::select('lugar')->distinct()->pluck('lugar')
        ]);
    }

    /**
     * Lógica de consulta compartida para render y exportación
     */
    public function getFilteredQuery()
    {
        return Tecnologia::with('personal')
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $searchTerm = '%' . $this->search . '%';
                    $q->where('nombre', 'like', $searchTerm)
                      ->orWhere('marca', 'like', $searchTerm)
                      ->orWhere('serie', 'like', $searchTerm);
                });
            })
            ->when($this->filterPersonal, function($query) {
                $query->where('personal_id', $this->filterPersonal);
            })
            ->when($this->filterLugar, function($query) {
                $query->where('lugar', $this->filterLugar);
            });
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

        DB::transaction(function () {
            if ($this->estado === 'Dar de Baja') {
                // --- CASO: MOVER A LA TABLA DE BAJAS ---
                $rutaImagen = $this->imagen_actual;
                if ($this->imagen) {
                    $rutaImagen = $this->imagen->store('tecnologia', 'local');
                }

                Debaja::create([
                    'nombre'          => $this->nombre,
                    'tipo_inventario' => 'Tecnología',
                    'motivo'          => 'Dar de Baja',
                    'fecha_baja'      => now(),
                    'personal_id'     => $this->personal_id ?: null,
                    'imagen'          => $rutaImagen,
                    'detalles'        => [
                        'marca' => $this->marca,
                        'serie' => $this->serie,
                        'lugar' => $this->lugar,
                    ],
                ]);

                // Si existía en la tabla activa, lo borramos
                if ($this->equipo_id) {
                    $equipoActivo = Tecnologia::find($this->equipo_id);
                    if ($equipoActivo) $equipoActivo->delete();
                }

                $msg = 'Equipo tecnológico movido al historial de bajas.';
            } else {
                // --- CASO: REGISTRO O ACTUALIZACIÓN NORMAL ---
                $datos = [
                    'nombre'      => $this->nombre,
                    'marca'       => $this->marca,
                    'serie'       => $this->serie,
                    'estado'      => $this->estado,
                    'lugar'       => $this->lugar,
                    'personal_id' => $this->personal_id ?: null,
                ];

                if ($this->imagen) {
                    // Borrar imagen anterior si existe
                    if ($this->equipo_id && $this->imagen_actual) {
                        Storage::disk('local')->delete($this->imagen_actual);
                    }
                    $datos['imagen'] = $this->imagen->store('tecnologia', 'local');
                }

                Tecnologia::updateOrCreate(['id' => $this->equipo_id], $datos);
                $msg = $this->equipo_id ? 'Equipo actualizado correctamente' : 'Equipo registrado con éxito';
            }

            $this->dispatch('mueble-guardado', msg: $msg);
            $this->closeModal();
        });
    }

    public function eliminar($id)
    {
        $equipo = Tecnologia::find($id);
        if ($equipo) {
            if ($equipo->imagen) {
                Storage::disk('local')->delete($equipo->imagen);
            }
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
        $this->lugar = 'Oficina Principal'; 
        $this->personal_id = ''; 
        $this->equipo_id = '';
        $this->imagen = null;
        $this->imagen_actual = null;
    }

    public function exportar()
    {
        // En exportación usamos ->get() para obtener todos los registros filtrados
        $equipos = $this->getFilteredQuery()->get();

        $filename = 'reporte_inventario_tecno_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($equipos) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8 para Excel
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