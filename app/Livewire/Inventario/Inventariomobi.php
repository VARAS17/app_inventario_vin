<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Mobiliario;
use App\Models\MobiliarioArchivo;

class Inventariomobi extends Component
{
    use WithFileUploads, WithPagination;

    #[Layout('layouts.app')]

    // Buscador y Paginación fija de 10
    public string $search = '';
    public string $filtroEstado = 'activos'; // 'activos' | 'baja' | 'todos'
    protected int $perPage = 10;

    // Item seleccionado para el panel inferior
    public ?int $selectedId = null;

    // Control del Modal Formulario
    public bool $isOpenModal = false;
    public bool $isEditMode = false;
    public ?int $mobiliario_id = null;

    // Campos del Formulario
    public string $codigo_vin = '';
    public ?string $codigo_inventario_unt = null;
    public string $nombre = '';
    public ?string $proveedor = null;
    public ?string $descripcion = null;
    public string $estado = 'Disponible'; // Blindado: siempre nace en Disponible
    public string $fecha_ingreso = '';
    public $foto = null; // Foto nueva temporal
    public ?string $foto_existente = null; // Ruta de foto ya guardada

    // Gestión de Archivos Incrementales con Botón (+)
    public $tempFile = null;
    public array $archivosNuevos = [];
    public $archivosExistentes = [];

    // Modal de Confirmación de Eliminación
    public bool $modalEliminar = false;
    public ?int $idAEliminar = null;

    // Visor Integrado de Archivos (Base64)
    public bool $modalPreview = false;
    public ?string $previewUrl = null;
    public ?string $previewType = null;
    public ?string $previewName = null;

    public function mount(): void
    {
        $this->fecha_ingreso = now()->format('Y-m-d');

        // Selecciona por defecto el primer mueble para inspeccionar de inmediato
        $primerMueble = Mobiliario::latest('id')->first();
        if ($primerMueble) {
            $this->selectedId = $primerMueble->id;
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'codigo_vin' => [
                'required',
                'string',
                Rule::unique('mobiliarios', 'codigo_vin')->ignore($this->mobiliario_id),
            ],
            'codigo_inventario_unt' => 'nullable|string|max:100',
            'nombre'                => 'required|string|max:150',
            'proveedor'             => 'nullable|string|max:150',
            'descripcion'           => 'nullable|string|max:500',
            'fecha_ingreso'         => 'required|date',
            'foto'                  => 'nullable|image|max:5120', // Foto opcional máx 5MB
            'archivosNuevos.*'      => 'nullable|file|max:20480',  // Documentos máx 20MB
        ];
    }

    protected $messages = [
        'codigo_vin.required'     => 'El código único es obligatorio.',
        'codigo_vin.unique'       => 'Este código ya se encuentra registrado.',
        'nombre.required'         => 'El nombre del mobiliario es obligatorio.',
        'fecha_ingreso.required'  => 'La fecha de ingreso es obligatoria.',
        'fecha_ingreso.date'      => 'Ingrese una fecha válida.',
        'foto.image'              => 'El archivo debe ser una imagen válida.',
        'foto.max'                => 'La imagen no debe superar los 5MB.',
        'archivosNuevos.*.max'    => 'Cada documento no debe superar los 20MB.',
    ];

    // ==========================================
    // SELECCIÓN Y PANEL DE INSPECCIÓN
    // ==========================================
    public function selectItem(int $id): void
    {
        $this->selectedId = ($this->selectedId === $id) ? null : $id;
    }

    #[Computed]
    public function selectedMobiliario()
    {
        if (!$this->selectedId) return null;

        return Mobiliario::with(['archivos', 'ultimaAsignacion.areaDestino', 'ultimaAsignacion.personal'])
            ->find($this->selectedId);
    }

    /**
     * Reconstruye cronológicamente los últimos 3 movimientos
     */
    #[Computed]
    public function ultimosMovimientos()
    {
        if (!$this->selectedId) return collect();

        $mob = Mobiliario::with([
            'asignaciones.areaDestino',
            'asignaciones.personal',
            'salida.areaOrigen'
        ])->find($this->selectedId);

        if (!$mob) return collect();

        $eventos = collect();

        // 1. Transferencias
        foreach ($mob->asignaciones as $asig) {
            $custodio = $asig->personal 
                ? "Custodio: {$asig->personal->nombre} {$asig->personal->apellido}" 
                : 'Uso Común (Área General)';

            $eventos->push([
                'tipo'   => 'Transferencia',
                'badge'  => 'bg-blue-100 text-blue-800 border-blue-300',
                'fecha'  => Carbon::parse($asig->fecha_traspaso),
                'titulo' => "Ubicado en " . ($asig->areaDestino->nombre ?? 'Área General'),
                'detalle'=> $custodio,
            ]);
        }

        // 2. Salida definitiva / Baja
        if ($mob->salida) {
            $eventos->push([
                'tipo'   => 'Baja',
                'badge'  => 'bg-rose-100 text-rose-800 border-rose-300',
                'fecha'  => Carbon::parse($mob->salida->fecha_salida),
                'titulo' => "Baja: {$mob->salida->tipo_baja}",
                'detalle'=> "Destino: " . $mob->salida->destino_final,
            ]);
        }

        // 3. Alta original
        if ($mob->fecha_ingreso) {
            $eventos->push([
                'tipo'   => 'Ingreso',
                'badge'  => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'fecha'  => Carbon::parse($mob->fecha_ingreso),
                'titulo' => 'Alta en Inventario',
                'detalle'=> 'Proveedor: ' . ($mob->proveedor ?? 'Sin registrar'),
            ]);
        }

        return $eventos->sortByDesc('fecha')->take(3)->values();
    }

    /**
     * Helper para renderizar fotos locales sin symlink en NativePHP
     */
    public function getFotoBase64(?string $ruta): ?string
    {
        if (!$ruta || !Storage::disk('local')->exists($ruta)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($ruta);
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('local')->get($ruta));
    }

    // ==========================================
    // GESTIÓN DE ARCHIVOS CON BOTÓN (+)
    // ==========================================
    public function updatedTempFile(): void
    {
        $this->validate(['tempFile' => 'file|max:20480']);
        $this->archivosNuevos[] = $this->tempFile;
        $this->reset('tempFile');
    }

    public function eliminarArchivoNuevo(int $index): void
    {
        if (isset($this->archivosNuevos[$index])) {
            unset($this->archivosNuevos[$index]);
            $this->archivosNuevos = array_values($this->archivosNuevos);
        }
    }

    public function eliminarArchivoExistente(int $id): void
    {
        $doc = MobiliarioArchivo::findOrFail($id);

        if (Storage::disk('local')->exists($doc->ruta_archivo)) {
            Storage::disk('local')->delete($doc->ruta_archivo);
        }

        $doc->delete();
        $this->archivosExistentes = MobiliarioArchivo::where('mobiliario_id', $this->mobiliario_id)->get();
    }

    public function eliminarFotoExistente(): void
    {
        if ($this->foto_existente && Storage::disk('local')->exists($this->foto_existente)) {
            Storage::disk('local')->delete($this->foto_existente);
        }

        if ($this->mobiliario_id) {
            Mobiliario::where('id', $this->mobiliario_id)->update(['foto' => null]);
        }

        $this->foto_existente = null;
    }

    // ==========================================
    // OPERACIONES CRUD
    // ==========================================
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->codigo_vin = Mobiliario::generarSiguienteCodigoVin();
        $this->fecha_ingreso = now()->format('Y-m-d');
        $this->estado = 'Disponible'; // Nace Disponible en almacén
        $this->isOpenModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $this->isEditMode = true;

        $mob = Mobiliario::with('archivos')->findOrFail($id);
        $this->mobiliario_id         = $mob->id;
        $this->codigo_vin            = $mob->codigo_vin;
        $this->codigo_inventario_unt = $mob->codigo_inventario_unt;
        $this->nombre                = $mob->nombre;
        $this->proveedor             = $mob->proveedor;
        $this->descripcion           = $mob->descripcion;
        $this->estado                = $mob->estado;
        $this->fecha_ingreso         = $mob->fecha_ingreso ? Carbon::parse($mob->fecha_ingreso)->format('Y-m-d') : '';
        $this->foto_existente        = $mob->foto;
        $this->archivosExistentes    = $mob->archivos;

        $this->isOpenModal = true;
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $data = [
                'codigo_vin'            => $this->codigo_vin,
                'codigo_inventario_unt' => !empty($this->codigo_inventario_unt) ? trim($this->codigo_inventario_unt) : null,
                'nombre'                => trim($this->nombre),
                'proveedor'             => !empty($this->proveedor) ? trim($this->proveedor) : null,
                'descripcion'           => !empty($this->descripcion) ? trim($this->descripcion) : null,
                'fecha_ingreso'         => $this->fecha_ingreso,
            ];

            if ($this->isEditMode) {
                $mobiliario = Mobiliario::findOrFail($this->mobiliario_id);

                if ($this->foto) {
                    if ($mobiliario->foto && Storage::disk('local')->exists($mobiliario->foto)) {
                        Storage::disk('local')->delete($mobiliario->foto);
                    }
                    $data['foto'] = $this->foto->store('mobiliarios', 'local');
                }

                $mobiliario->update($data);
                session()->flash('message', 'Mobiliario actualizado con éxito.');
            } else {
                $data['foto'] = $this->foto ? $this->foto->store('mobiliarios', 'local') : null;
                $data['estado'] = 'Disponible'; // Siempre nace en Disponible

                $mobiliario = Mobiliario::create($data);
                session()->flash('message', 'Mobiliario registrado con código: ' . $mobiliario->codigo_vin);
            }

            // Guardar archivos de evidencia adjuntos
            if (!empty($this->archivosNuevos)) {
                foreach ($this->archivosNuevos as $archivo) {
                    $ruta = $archivo->store('mobiliarios_archivos', 'local');

                    MobiliarioArchivo::create([
                        'nombre_archivo' => $archivo->getClientOriginalName(),
                        'ruta_archivo'   => $ruta,
                        'mobiliario_id'  => $mobiliario->id,
                    ]);
                }
            }

            $this->selectedId = $mobiliario->id;
        });

        $this->closeModal();
    }

    public function confirmarEliminar(int $id): void
    {
        $mobiliario = Mobiliario::with(['asignaciones', 'salida'])->findOrFail($id);

        // BLOQUEO ESTRICTO DE SEGURIDAD HISTÓRICA
        $tieneHistorial = $mobiliario->asignaciones->isNotEmpty() || !is_null($mobiliario->salida);

        if ($tieneHistorial) {
            session()->flash('error', 'Acción bloqueada: No se puede eliminar este mueble porque tiene un historial de transferencias o baja registrado. Para retirarlo use el submódulo de Bajas.');
            return;
        }

        $this->idAEliminar = $id;
        $this->modalEliminar = true;
    }

    public function eliminar(): void
    {
        if (!$this->idAEliminar) return;

        DB::transaction(function () {
            $mobiliario = Mobiliario::with('archivos')->findOrFail($this->idAEliminar);

            // Borrar foto física
            if ($mobiliario->foto && Storage::disk('local')->exists($mobiliario->foto)) {
                Storage::disk('local')->delete($mobiliario->foto);
            }

            // Borrar archivos físicos
            foreach ($mobiliario->archivos as $doc) {
                if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                    Storage::disk('local')->delete($doc->ruta_archivo);
                }
            }

            $mobiliario->delete();
        });

        if ($this->selectedId === $this->idAEliminar) {
            $this->selectedId = Mobiliario::latest('id')->first()?->id;
        }

        $this->modalEliminar = false;
        $this->idAEliminar = null;
        session()->flash('message', 'Registro eliminado correctamente.');
    }

    // ==========================================
    // VISOR INTEGRADO (BASE64)
    // ==========================================
    public function abrirPrevisualizador(int $id): void
    {
        $archivo = MobiliarioArchivo::findOrFail($id);

        if (!Storage::disk('local')->exists($archivo->ruta_archivo)) {
            session()->flash('error', 'El archivo no existe en el disco local.');
            return;
        }

        $mime = Storage::disk('local')->mimeType($archivo->ruta_archivo);
        $contenido = Storage::disk('local')->get($archivo->ruta_archivo);

        $this->previewName = $archivo->nombre_archivo;
        $this->previewUrl = 'data:' . $mime . ';base64,' . base64_encode($contenido);

        if (str_contains($mime, 'image')) {
            $this->previewType = 'image';
        } elseif (str_contains($mime, 'pdf')) {
            $this->previewType = 'pdf';
        } else {
            $this->previewType = 'other';
        }

        $this->modalPreview = true;
    }

    public function cerrarPrevisualizador(): void
    {
        $this->reset(['modalPreview', 'previewUrl', 'previewType', 'previewName']);
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->isOpenModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'mobiliario_id',
            'codigo_vin',
            'codigo_inventario_unt',
            'nombre',
            'proveedor',
            'descripcion',
            'estado',
            'fecha_ingreso',
            'foto',
            'foto_existente',
            'tempFile',
            'archivosNuevos',
            'archivosExistentes'
        ]);
        $this->resetValidation();
    }

    /**
     * Exporta el catálogo a CSV compatible con Excel
     */
    public function exportar()
    {
        $query = Mobiliario::with([
            'asignaciones' => function ($q) {
                $q->latest('id');
            },
            'asignaciones.areaDestino',
            'asignaciones.personal'
        ])
        ->when($this->filtroEstado === 'activos', function ($q) {
            $q->where('estado', '!=', 'De baja');
        })
        ->when($this->filtroEstado === 'baja', function ($q) {
            $q->where('estado', 'De baja');
        })
        ->when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('codigo_vin', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo_inventario_unt', 'like', '%' . $this->search . '%')
                  ->orWhere('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('proveedor', 'like', '%' . $this->search . '%')
                  ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            });
        })
        ->orderBy('id', 'asc');

        $mobiliarios = $query->get();
        $filename = now()->format('Y-m-d') . '_inventario_mobiliario.csv';

        $callback = function () use ($mobiliarios) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para Excel
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['REPORTE GENERAL DE INVENTARIO DE MOBILIARIO'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' - Sistema Patrimonial VIN UNT'], ';');
            fputcsv($file, [], ';');

            fputcsv($file, [
                'Código Único',
                'Cód. Patrimonial UNT',
                'Nombre del Mueble',
                'Descripción / Características',
                'Estado',
                'Fecha Ingreso',
                'Proveedor',
                'Ubicación / Custodio Actual'
            ], ';');

            foreach ($mobiliarios as $m) {
                $ubicacionCustodio = 'Almacén Central (Disponible)';

                if ($m->estado === 'Asignado') {
                    $ultAsig = $m->asignaciones->first();
                    $areaNom = $ultAsig?->areaDestino?->nombre ?? 'Área General';
                    $persona = $ultAsig?->personal 
                        ? "{$ultAsig->personal->nombre} {$ultAsig->personal->apellido}" 
                        : 'Uso Común';
                    $ubicacionCustodio = "{$areaNom} - Custodio: {$persona}";
                } elseif ($m->estado === 'De baja') {
                    $ubicacionCustodio = 'Dado de baja definitivamente';
                }

                fputcsv($file, [
                    $m->codigo_vin,
                    $m->codigo_inventario_unt ?? 'S/C',
                    $m->nombre,
                    $m->descripcion ?? '—',
                    $m->estado,
                    $m->fecha_ingreso ? Carbon::parse($m->fecha_ingreso)->format('d/m/Y') : '—',
                    $m->proveedor ?? '—',
                    $ubicacionCustodio
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

public function render()
    {
        $mobiliarios = Mobiliario::query()
            ->when($this->filtroEstado === 'activos', function ($q) {
                $q->where('estado', '!=', 'De baja');
            })
            ->when($this->filtroEstado === 'baja', function ($q) {
                $q->where('estado', 'De baja');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo_vin', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo_inventario_unt', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('proveedor', 'like', '%' . $this->search . '%')
                      ->orWhere('descripcion', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        return view('livewire.inventario.inventariomobi', [
            'mobiliarios' => $mobiliarios
        ]);
    }
}