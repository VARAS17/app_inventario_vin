<?php

namespace App\Livewire\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Tecnologia;
use App\Models\Area;
use App\Models\asignaciones as Asignacion;
use App\Models\mantenimiento as Mantenimiento;
use App\Models\salidas as Salida;
use App\Models\salidas_archivos as SalidaArchivo;

class SalidaTEC extends Component
{
    use WithPagination, WithFileUploads;

    #[Layout('layouts.app')]

    // ==========================================
    // 1. BUSCADOR Y PAGINACIÓN ESTRICTA (8)
    // ==========================================
    public string $search = '';
    protected int $perPage = 8;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // 2. PANEL MASTER-DETAIL COLAPSABLE
    // ==========================================
    public ?int $selectedSalidaId = null;

    public function toggleSelect(int $id): void
    {
        $this->selectedSalidaId = ($this->selectedSalidaId === $id) ? null : $id;
    }

    public function cerrarDetalle(): void
    {
        $this->selectedSalidaId = null;
    }

    // ==========================================
    // 3. ESTADOS DE MODALES
    // ==========================================
    public bool $modalFormulario = false;
    public bool $modalEliminar = false;
    public bool $modalPreview = false;

    // ==========================================
    // 4. CAMPOS DEL FORMULARIO (CREAR / EDITAR)
    // ==========================================
    public ?int $salida_id = null;
    public ?int $tecnologia_id = null;
    public ?Tecnologia $tecnologiaSeleccionada = null;
    public string $searchTecnologia = '';

    public ?int $area_origen_id = null; // Autocompletado y bloqueado
    public string $tipo_baja = 'Obsolescencia';
    public string $destino_final = '';
    public ?string $responsable_recepcion = null;
    public string $fecha_salida = '';

    // Gestión de Archivos Incrementales (+)
    public $tempFile;
    public array $archivosNuevos = [];
    public $archivosExistentes = [];

    // ==========================================
    // 5. VARIABLES DE ELIMINACIÓN Y PREVIEW
    // ==========================================
    public ?int $salidaIdAEliminar = null;

    public ?string $previewUrl = null;
    public ?string $previewType = null;
    public ?string $previewName = null;

    // ==========================================
    // VALIDACIONES
    // ==========================================
    protected function rules(): array
    {
        return [
            'tecnologia_id'          => 'required|exists:tecnologias,id',
            'area_origen_id'         => 'required|exists:areas,id',
            'tipo_baja'              => 'required|string|max:100',
            'destino_final'          => 'required|string|max:255',
            'responsable_recepcion'  => 'nullable|string|max:255',
            'fecha_salida'           => 'required|date',
            'archivosNuevos.*'       => 'nullable|file|max:20480', // Máximo 20MB
        ];
    }

    protected $messages = [
        'tecnologia_id.required'  => 'Debe vincular un activo para dar de baja.',
        'area_origen_id.required' => 'El área de procedencia es obligatoria.',
        'tipo_baja.required'      => 'Especifique el tipo o motivo de la baja.',
        'destino_final.required'  => 'Indique el destino físico final del activo.',
        'fecha_salida.required'   => 'La fecha de salida es obligatoria.',
        'archivosNuevos.*.max'    => 'El archivo no debe exceder los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_salida = now()->format('Y-m-d');

        // Seleccionar por defecto el primer registro para el panel de inspección
        $primera = Salida::latest('id')->first();
        if ($primera) {
            $this->selectedSalidaId = $primera->id;
        }
    }

    // ==========================================
    // BUSCADOR ASISTIDO CON DETECCIÓN DE ORIGEN
    // ==========================================
    #[Computed]
    public function tecnologiasEncontradas()
    {
        if (strlen(trim($this->searchTecnologia)) < 1) {
            return collect();
        }

        return Tecnologia::where(function ($q) {
                $q->whereIn('estado', ['Disponible', 'Asignado', 'De baja']);
            })
            ->whereDoesntHave('salida', function ($s) { // <-- AQUÍ: en singular 'salida'
                if ($this->salida_id) {
                    $s->where('id', '!=', $this->salida_id);
                }
            })
            ->where(function ($query) {
                $query->where('codigo_vin', 'like', "%{$this->searchTecnologia}%")
                    ->orWhere('nombre', 'like', "%{$this->searchTecnologia}%")
                    ->orWhere('serie', 'like', "%{$this->searchTecnologia}%")
                    ->orWhere('marca', 'like', "%{$this->searchTecnologia}%");
            })
            ->limit(5)
            ->get();
    }

    public function seleccionarTecnologia(int $id): void
    {
        $this->tecnologia_id = $id;
        $this->tecnologiaSeleccionada = Tecnologia::find($id);
        $this->searchTecnologia = '';

        if ($this->tecnologiaSeleccionada) {
            // DETECCIÓN INTELIGENTE DEL ÁREA DE PROCEDENCIA
            if ($this->tecnologiaSeleccionada->estado === 'Disponible') {
                $this->area_origen_id = Area::where('nombre', 'ALMACEN')->first()?->id;
            } elseif ($this->tecnologiaSeleccionada->estado === 'Asignado') {
                $ultAsignacion = Asignacion::where('tecnologia_id', $this->tecnologia_id)->latest('id')->first();
                $this->area_origen_id = $ultAsignacion?->area_destino_id;
            } elseif ($this->tecnologiaSeleccionada->estado === 'De baja') {
                // Viene de Mantenimiento como irreparable
                $ultMant = Mantenimiento::where('tecnologia_id', $this->tecnologia_id)->latest('id')->first();
                $this->area_origen_id = $ultMant?->area_origen_id ?? Area::where('nombre', 'VIN')->first()?->id;
                $this->tipo_baja = 'Daño irreparable (Mantenimiento)';
            }
        }
    }

    public function cambiarTecnologia(): void
    {
        $this->tecnologia_id = null;
        $this->tecnologiaSeleccionada = null;
        $this->area_origen_id = null;
        $this->searchTecnologia = '';
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
    // OPERACIONES CRUD (CREAR, EDITAR, GUARDAR)
    // ==========================================
    public function abrirModalCrear(): void
    {
        $this->resetFormulario();
        $this->modalFormulario = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetFormulario();
        $salida = Salida::with(['archivos', 'areaOrigen'])->findOrFail($id);

        $this->salida_id              = $salida->id;
        $this->tecnologia_id          = $salida->tecnologia_id;
        $this->tecnologiaSeleccionada = Tecnologia::find($salida->tecnologia_id);
        $this->area_origen_id         = $salida->area_origen_id;
        $this->tipo_baja              = $salida->tipo_baja;
        $this->destino_final          = $salida->destino_final;
        $this->responsable_recepcion  = $salida->responsable_recepcion;
        $this->fecha_salida           = $salida->fecha_salida ? $salida->fecha_salida->format('Y-m-d') : '';
        $this->archivosExistentes     = $salida->archivos;

        $this->modalFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Crear o Actualizar Salida
            $salida = Salida::updateOrCreate(
                ['id' => $this->salida_id],
                [
                    'tecnologia_id'         => $this->tecnologia_id,
                    'area_origen_id'        => $this->area_origen_id,
                    'tipo_baja'             => trim($this->tipo_baja),
                    'destino_final'         => trim($this->destino_final),
                    'responsable_recepcion' => !empty($this->responsable_recepcion) ? trim($this->responsable_recepcion) : null,
                    'fecha_salida'          => $this->fecha_salida,
                ]
            );

            // 2. Almacenar archivos físicos en disco local
            foreach ($this->archivosNuevos as $archivo) {
                $ruta = $archivo->store('salidas_archivos', 'local');

                SalidaArchivo::create([
                    'salida_id'      => $salida->id,
                    'nombre_archivo' => $archivo->getClientOriginalName(),
                    'ruta_archivo'   => $ruta,
                ]);
            }

            // 3. El activo pasa irreversiblemente a 'De baja'
            $this->tecnologiaSeleccionada->update(['estado' => 'De baja']);

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
            'tecnologia_id',
            'tecnologiaSeleccionada',
            'searchTecnologia',
            'area_origen_id',
            'destino_final',
            'responsable_recepcion',
            'tempFile',
            'archivosNuevos',
            'archivosExistentes'
        ]);
        $this->tipo_baja = 'Obsolescencia';
        $this->fecha_salida = now()->format('Y-m-d');
        $this->resetErrorBag();
    }

    // ==========================================
    // CONFIRMACIÓN Y ELIMINACIÓN FÍSICA
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
            $salida = Salida::with(['archivos', 'tecnologia'])->findOrFail($this->salidaIdAEliminar);

            // 1. Eliminar archivos locales
            foreach ($salida->archivos as $archivo) {
                if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                    Storage::disk('local')->delete($archivo->ruta_archivo);
                }
            }

            // 2. Si se cancela la baja, el activo vuelve a 'Disponible' en almacén
            if ($salida->tecnologia) {
                $salida->tecnologia->update(['estado' => 'Disponible']);
            }

            // 3. Eliminar registro
            $salida->delete();
        });

        if ($this->selectedSalidaId === $this->salidaIdAEliminar) {
            $this->selectedSalidaId = Salida::latest('id')->first()?->id;
        }

        $this->modalEliminar = false;
        $this->salidaIdAEliminar = null;
        session()->flash('success', 'Registro de salida eliminado. El activo volvió a estar Disponible.');
    }

    // ==========================================
    // VISOR INTEGRADO (PREVIEWER BASE64)
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

    // ==========================================
    // HELPERS COMPUTADOS PARA LA VISTA
    // ==========================================
    #[Computed]
    public function salidaSeleccionada()
    {
        if (!$this->selectedSalidaId) return null;

        return Salida::with(['tecnologia', 'archivos', 'areaOrigen'])->find($this->selectedSalidaId);
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
     * Exporta el registro de bajas definitivas y disposición final a CSV/Excel
     */
    public function exportar()
    {
        // 1. Consulta aplicando los mismos criterios de búsqueda del módulo
        $query = Salida::with(['tecnologia', 'archivos', 'areaOrigen'])
            ->when($this->search, function ($query) {
                $query->whereHas('tecnologia', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_vin', 'like', "%{$this->search}%")
                      ->orWhere('marca', 'like', "%{$this->search}%");
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

        // 2. Nombre del archivo dinámico
        $filename = now()->format('Y-m-d') . '_bajas_definitivas.csv';

        // 3. Streaming en memoria limpia con BOM UTF-8
        $callback = function () use ($salidas) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para Excel en español
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados Institucionales de Control Patrimonial
            fputcsv($file, ['REPORTE OFICIAL DE BAJAS DEFINITIVAS Y DISPOSICION FINAL DE ACTIVOS'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' | Control de Bajas y Desincorporación'], ';');
            fputcsv($file, [], ';'); // Separador

            // Columnas de Auditoría
            fputcsv($file, [
                'Código VIN',
                'Equipo / Activo',
                'Marca',
                'Número de Serie',
                'Área de Procedencia',
                'Causal / Tipo de Baja',
                'Destino Físico Final',
                'Receptor / Firmante',
                'Fecha de Salida',
                'Sustento Documental (Actas/Resoluciones)'
            ], ';');

            // Filas de Datos
            foreach ($salidas as $s) {
                $docsCount = $s->archivos->count();
                $sustento = $docsCount > 0 ? "Con Expediente ({$docsCount} doc)" : 'Sin Acta adjunta';

                fputcsv($file, [
                    $s->tecnologia?->codigo_vin ?? '—',
                    $s->tecnologia?->nombre ?? '—',
                    $s->tecnologia?->marca ?? '—',
                    $s->tecnologia?->serie ?? 'S/N',
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

    // ==========================================
    // RENDER PRINCIPAL
    // ==========================================
    public function render()
    {
        $salidas = Salida::with(['tecnologia', 'archivos', 'areaOrigen'])
            ->where(function ($query) {
                $query->whereHas('tecnologia', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_vin', 'like', "%{$this->search}%")
                      ->orWhere('marca', 'like', "%{$this->search}%");
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

        return view('livewire.inventario.salida-t-e-c', [
            'salidas' => $salidas,
            'areas'   => $areas
        ]);
    }
}