<?php

namespace App\Livewire\Inventario;

use App\Models\Tecnologia;
use App\Models\salidas as Salida;
use App\Models\salidas_archivos as SalidaArchivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

class SalidaTEC extends Component
{
    use WithFileUploads;

    #[Layout('layouts.app')]

    // Filtro de búsqueda
    public string $search = '';

    // Estado del modal y activo seleccionado
    public bool $modalAbierto = false;
    public ?Tecnologia $tecnologiaSeleccionada = null;

    // Campos del formulario de salida
    public ?int $tecnologia_id = null;
    public string $motivo = '';
    public string $area_destino = '';
    public string $responsable = '';
    public string $fecha_salida = '';

    // Archivos adjuntos (múltiples)
    public array $archivos = [];

    protected function rules(): array
    {
        return [
            'tecnologia_id' => 'required|exists:tecnologias,id',
            'motivo'        => 'required|string|min:3|max:255',
            'area_destino'  => 'required|string|min:2|max:255',
            'responsable'   => 'required|string|min:3|max:255',
            'fecha_salida'  => 'required|date',
            'archivos.*'    => 'nullable|file|max:20480', // Máx 20MB por archivo
        ];
    }

    protected $messages = [
        'tecnologia_id.required' => 'Debes seleccionar un activo.',
        'motivo.required'        => 'El motivo de salida es obligatorio.',
        'area_destino.required'  => 'Indica el área de destino.',
        'responsable.required'   => 'El nombre del responsable es obligatorio.',
        'fecha_salida.required'  => 'La fecha de salida es obligatoria.',
        'archivos.*.max'         => 'Cada archivo no debe superar los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_salida = now()->format('Y-m-d');
    }

    // Abrir formulario con el activo seleccionado
    public function seleccionarActivo(int $id): void
    {
        $this->tecnologiaSeleccionada = Tecnologia::findOrFail($id);
        $this->tecnologia_id = $this->tecnologiaSeleccionada->id;
        $this->modalAbierto = true;
    }

    public function cerrarModal(): void
    {
        $this->modalAbierto = false;
        $this->resetearFormulario();
    }

    public function eliminarArchivoTemporal(int $index): void
    {
        unset($this->archivos[$index]);
        $this->archivos = array_values($this->archivos);
    }

    public function guardarSalida(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Crear el registro de Salida
            $salida = Salida::create([
                'tecnologia_id' => $this->tecnologia_id,
                'motivo'        => $this->motivo,
                'area_destino'  => $this->area_destino,
                'responsable'   => $this->responsable,
                'fecha_salida'  => $this->fecha_salida,
            ]);

            // 2. Guardar archivos localmente y registrarlos
            if (!empty($this->archivos)) {
                foreach ($this->archivos as $archivo) {
                    $nombreOriginal = $archivo->getClientOriginalName();
                    // Se guarda en el disco local: storage/app/salidas_adjuntos
                    $ruta = $archivo->store('salidas_adjuntos', 'local');

                    SalidaArchivo::create([
                        'salida_id'      => $salida->id,
                        'nombre_archivo' => $nombreOriginal,
                        'ruta_archivo'   => $ruta,
                    ]);
                }
            }

            // 3. Actualizar el estado del activo a 'De baja'
            $this->tecnologiaSeleccionada->update([
                'estado' => 'De baja'
            ]);
        });

        session()->flash('mensaje', '¡Salida registrada con éxito! El activo ha sido dado de baja.');
        $this->cerrarModal();
    }

    private function resetearFormulario(): void
    {
        $this->reset([
            'tecnologia_id',
            'tecnologiaSeleccionada',
            'motivo',
            'area_destino',
            'responsable',
            'archivos'
        ]);
        $this->fecha_salida = now()->format('Y-m-d');
        $this->resetValidation();
    }

    // Helper para asegurar que la imagen renderice en Base64
    public function getFotoSrc(string $foto): string
    {
        if (str_starts_with($foto, 'data:image')) {
            return $foto;
        }

        // Si es una ruta local en storage, la lee y convierte a Base64
        if (Storage::disk('local')->exists($foto)) {
            $contenido = Storage::disk('local')->get($foto);
            $mime = Storage::disk('local')->mimeType($foto) ?? 'image/jpeg';
            return 'data:' . $mime . ';base64,' . base64_encode($contenido);
        }

        // Si ya es un Base64 plano sin prefijo
        return 'data:image/jpeg;base64,' . $foto;
    }

    public function render()
    {
        $activos = Tecnologia::where('estado', '!=', 'De baja')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo_vin', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('serie', 'like', '%' . $this->search . '%');
                });
            })
            ->oldest()
            ->get();

        return view('livewire.inventario.salida-t-e-c', [
            'activos' => $activos
        ]);
    }
}