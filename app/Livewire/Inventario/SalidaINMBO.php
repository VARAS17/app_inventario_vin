<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Mobiliario;
use App\Models\Area;
use App\Models\MobiliarioAsignacion as Asignacion;
use App\Models\MobiliarioSalida as Salida;
use App\Models\MobiliarioSalidaArchivo as SalidaArchivo;
use Carbon\Carbon;

class SalidaINMBO extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // Buscador y Paginación
    public string $search = '';
    protected int $perPage = 8;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Panel Master-Detail colapsable
    public ?int $selectedSalidaId = null;

    public function toggleSelect(int $id): void
    {
        $this->selectedSalidaId = ($this->selectedSalidaId === $id) ? null : $id;
    }

    public function cerrarDetalle(): void
    {
        $this->selectedSalidaId = null;
    }

    // Modales
    public bool $modalFormulario = false;
    public bool $modalEliminar = false;
    public bool $modalPreview = false;

    // Campos del Formulario (Crear / Editar)
    public ?int $salida_id = null;
    public ?int $mobiliario_id = null;
    public ?Mobiliario $mobiliarioSeleccionado = null;
    public string $searchMobiliario = '';

    public ?int $area_origen_id = null; // Autocompletado y bloqueado
    public string $tipo_baja = 'Deterioro irreparable';
    public string $destino_final = '';
    public ?string $responsable_recepcion = null;
    public string $fecha_salida = '';

    // Gestión de Archivos Incrementales (+)
    public $tempFile;
    public array $archivosNuevos = [];
    public $archivosExistentes = [];

    // Variables de Eliminación y Preview
    public ?int $salidaIdAEliminar = null;

    public ?string $previewUrl = null;
    public ?string $previewType = null;
    public ?string $previewName = null;

    protected function rules(): array
    {
        return [
            'mobiliario_id'         => 'required|exists:mobiliarios,id',
            'area_origen_id'        => 'required|exists:areas,id',
            'tipo_baja'             => 'required|string|max:100',
            'destino_final'         => 'required|string|max:255',
            'responsable_recepcion' => 'nullable|string|max:255',
            'fecha_salida'          => 'required|date',
            'archivosNuevos.*'      => 'nullable|file|max:20480', // Máx 20MB
        ];
    }

    protected $messages = [
        'mobiliario_id.required'  => 'Debe vincular un mueble para dar de baja.',
        'area_origen_id.required' => 'El área de procedencia es obligatoria.',
        'tipo_baja.required'      => 'Especifique el motivo de la baja.',
        'destino_final.required'  => 'Indique el destino físico final del activo.',
        'fecha_salida.required'   => 'La fecha de salida es obligatoria.',
        'archivosNuevos.*.max'    => 'El archivo adjunto no debe exceder los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_salida = now()->format('Y-m-d');

        // Seleccionar por defecto el primer registro para el panel inferior
        $primera = Salida::latest('id')->first();
        if ($primera) {
            $this->selectedSalidaId = $primera->id;
        }
    }

    // ==========================================
    // BUSCADOR ASISTIDO CON DETECCIÓN DE ORIGEN
    // ==========================================
    #[Computed]
    public function mobiliariosEncontrados()
    {
        if (strlen(trim($this->searchMobiliario)) < 1) {
            return collect();
        }

        return Mobiliario::whereIn('estado', ['Disponible', 'Asignado'])
            ->whereDoesntHave('salida', function ($s) {
                if ($this->salida_id) {
                    $s->where('id', '!=', $this->salida_id);
                }
            })
            ->where(function ($query) {
                $query->where('codigo_vin', 'like', "%{$this->searchMobiliario}%")
                    ->orWhere('nombre', 'like', "%{$this->searchMobiliario}%")
                    ->orWhere('codigo_inventario_unt', 'like', "%{$this->searchMobiliario}%");
            })
            ->limit(5)
            ->get();
    }

    public function seleccionarMobiliario(int $id): void
    {
        $this->mobiliario_id = $id;
        $this->mobiliarioSeleccionado = Mobiliario::find($id);
        $this->searchMobiliario = '';

        if ($this->mobiliarioSeleccionado) {
            // DETECCIÓN AUTOMÁTICA DEL ÁREA DE PROCEDENCIA
            if ($this->mobiliarioSeleccionado->estado === 'Disponible') {
                $this->area_origen_id = Area::where('nombre', 'ALMACEN')->first()?->id ?? Area::first()?->id;
            } elseif ($this->mobiliarioSeleccionado->estado === 'Asignado') {
                $ultAsignacion = Asignacion::where('mobiliario_id', $this->mobiliario_id)->latest('id')->first();
                $this->area_origen_id = $ultAsignacion?->area_destino_id;
            }
        }
    }

    public function cambiarMobiliario(): void
    {
        $this->mobiliario_id = null;
        $this->mobiliarioSeleccionado = null;
        $this->area_origen_id = null;
        $this->searchMobiliario = '';
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
        $archivo = SalidaArchivo::find($id);
        if ($archivo) {
            if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                Storage::disk('local')->delete($archivo->ruta_archivo);
            }
            $archivo->delete();
            $this->archivosExistentes = SalidaArchivo::where('salida_id', $this->salida_id)->get();
        }
    }

    // ==========================================
    // OPERACIONES CRUD
    // ==========================================
    public function abrirModalCrear(): void
    {
        $this->resetFormulario();
        $this->modalFormulario = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetFormulario();
        $salida = Salida::with(['archivos', 'areaOrigen', 'mobiliario'])->findOrFail($id);

        $this->salida_id              = $salida->id;
        $this->mobiliario_id          = $salida->mobiliario_id;
        $this->mobiliarioSeleccionado = $salida->mobiliario;
        $this->area_origen_id         = $salida->area_origen_id;
        $this->tipo_baja              = $salida->tipo_baja;
        $this->destino_final          = $salida->destino_final;
        $this->responsable_recepcion  = $salida->responsable_recepcion;
        $this->fecha_salida           = $salida->fecha_salida ? $salida->fecha_salida->format('Y-m-d') : '';
        $this->archivosExistentes     = $salida->archivos;

        $this->modalFormulario = true;
    }

    public function cerrarModal(): void
    {
        $this->modalFormulario = false;
        $this->resetFormulario();
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            $salida = Salida::updateOrCreate(
                ['id' => $this->salida_id],
                [
                    'mobiliario_id'         => $this->mobiliario_id,
                    'area_origen_id'        => $this->area_origen_id,
                    'tipo_baja'             => trim($this->tipo_baja),
                    'destino_final'         => trim($this->destino_final),
                    'responsable_recepcion' => !empty($this->responsable_recepcion) ? trim($this->responsable_recepcion) : null,
                    'fecha_salida'          => $this->fecha_salida,
                ]
            );

            // Almacenar archivos físicos en disco local
            foreach ($this->archivosNuevos as $archivo) {
                $ruta = $archivo->store('mobiliarios_salidas_archivos', 'local');

                SalidaArchivo::create([
                    'salida_id'      => $salida->id,
                    'nombre_archivo' => $archivo->getClientOriginalName(),
                    'ruta_archivo'   => $ruta,
                ]);
            }

            // El mueble pasa irreversiblemente a 'De baja'
            $this->mobiliarioSeleccionado->update(['estado' => 'De baja']);

            $this->selectedSalidaId = $salida->id;
        });

        $this->modalFormulario = false;
        $this->resetFormulario();
        session()->flash('success', 'Baja definitiva y disposición final registradas con éxito.');
    }

    public function resetFormulario(): void
    {
        $this->reset([
            'salida_id',
            'mobiliario_id',
            'mobiliarioSeleccionado',
            'searchMobiliario',
            'area_origen_id',
            'destino_final',
            'responsable_recepcion',
            'tempFile',
            'archivosNuevos',
            'archivosExistentes'
        ]);
        $this->tipo_baja = 'Deterioro irreparable';
        $this->fecha_salida = now()->format('Y-m-d');
        $this->resetErrorBag();
    }

    // ==========================================
    // CONFIRMACIÓN Y ELIMINACIÓN FÍSICA (ROLLBACK)
    // ==========================================
    public function confirmarEliminar(int $id): void
    {
        $this->salidaIdAEliminar = $id;
        $this->modalEliminar = true;
    }

    public function eliminarSalida(): void
    {
        if (!$this->salidaIdAEliminar) return;

        DB::transaction(function () {
            $salida = Salida::with(['archivos', 'mobiliario'])->findOrFail($this->salidaIdAEliminar);

            // Borrar archivos locales
            foreach ($salida->archivos as $archivo) {
                if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                    Storage::disk('local')->delete($archivo->ruta_archivo);
                }
            }

            // ROLLBACK: Restaurar estado previo
            if ($salida->mobiliario) {
                $tieneAsignacion = Asignacion::where('mobiliario_id', $salida->mobiliario_id)->exists();
                $nuevoEstado = $tieneAsignacion ? 'Asignado' : 'Disponible';
                $salida->mobiliario->update(['estado' => $nuevoEstado]);
            }

            $salida->delete();
        });

        if ($this->selectedSalidaId === $this->salidaIdAEliminar) {
            $this->selectedSalidaId = Salida::latest('id')->first()?->id;
        }

        $this->modalEliminar = false;
        $this->salidaIdAEliminar = null;
        session()->flash('success', 'Registro de salida eliminado. El mueble fue restaurado a su estado previo.');
    }

    // ==========================================
    // VISOR INTEGRADO (BASE64)
    // ==========================================
    public function abrirPrevisualizador(int $archivoId): void
    {
        $archivo = SalidaArchivo::findOrFail($archivoId);

        if (!Storage::disk('local')->exists($archivo->ruta_archivo)) {
            session()->flash('error', 'El archivo no existe físicamente en el disco local.');
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

    #[Computed]
    public function salidaSeleccionada()
    {
        if (!$this->selectedSalidaId) return null;

        return Salida::with(['mobiliario', 'archivos', 'areaOrigen'])->find($this->selectedSalidaId);
    }

    public function getFotoBase64(?string $ruta): ?string
    {
        if (!$ruta || !Storage::disk('local')->exists($ruta)) {
            return null;
        }

        $mime = Storage::disk('local')->mimeType($ruta);
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('local')->get($ruta));
    }

    /**
     * Exporta el registro de bajas a CSV compatible con Excel
     */
    public function exportar()
    {
        $query = Salida::with(['mobiliario', 'archivos', 'areaOrigen'])
            ->when($this->search, function ($query) {
                $query->whereHas('mobiliario', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_vin', 'like', "%{$this->search}%");
                })
                ->orWhereHas('areaOrigen', function ($ao) {
                    $ao->where('nombre', 'like', "%{$this->search}%");
                })
                ->orWhere('tipo_baja', 'like', "%{$this->search}%")
                ->orWhere('destino_final', 'like', "%{$this->search}%")
                ->orWhere('responsable_recepcion', 'like', "%{$this->search}%");
            })
            ->latest('id');

        $salidas = $query->get();
        $filename = now()->format('Y-m-d') . '_bajas_mobiliario.csv';

        $callback = function () use ($salidas) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

            fputcsv($file, ['REPORTE OFICIAL DE BAJAS DEFINITIVAS DE MOBILIARIO'], ';');
            fputcsv($file, ['Sistema Patrimonial VIN UNT - Fecha: ' . now()->format('d/m/Y H:i:s')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, [
                'Código Único',
                'Nombre del Mueble',
                'Área Procedencia',
                'Causal / Tipo de Baja',
                'Destino Físico Final',
                'Receptor / Firmante',
                'Fecha de Salida',
                'Sustento Documental'
            ], ';');

            foreach ($salidas as $s) {
                $docsCount = $s->archivos->count();
                $sustento = $docsCount > 0 ? "Con Expediente ({$docsCount})" : 'Sin archivos';

                fputcsv($file, [
                    $s->mobiliario?->codigo_vin ?? '—',
                    $s->mobiliario?->nombre ?? '—',
                    $s->areaOrigen?->nombre ?? 'ALMACEN',
                    $s->tipo_baja,
                    $s->destino_final,
                    $s->responsable_recepcion ?? 'No especificado',
                    $s->fecha_salida ? Carbon::parse($s->fecha_salida)->format('d/m/Y') : '—',
                    $sustento
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
        $salidas = Salida::with(['mobiliario', 'archivos', 'areaOrigen'])
            ->where(function ($query) {
                $query->whereHas('mobiliario', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_vin', 'like', "%{$this->search}%");
                })
                ->orWhereHas('areaOrigen', function ($ao) {
                    $ao->where('nombre', 'like', "%{$this->search}%");
                })
                ->orWhere('tipo_baja', 'like', "%{$this->search}%")
                ->orWhere('destino_final', 'like', "%{$this->search}%")
                ->orWhere('responsable_recepcion', 'like', "%{$this->search}%");
            })
            ->latest('id')
            ->paginate($this->perPage);

        $areas = Area::orderBy('nombre', 'asc')->get();

        return view('livewire.inventario.salida-i-n-m-b-o', [
            'salidas' => $salidas,
            'areas'   => $areas
        ]);
    }
}