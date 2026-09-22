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
use App\Models\mantenimiento;
use App\Models\mantenimiento_archivos;
use Carbon\Carbon;


class MantenimientoTEC extends Component
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
    public ?int $selectedMantenimientoId = null;

    public function toggleSelect(int $id): void
    {
        $this->selectedMantenimientoId = ($this->selectedMantenimientoId === $id) ? null : $id;
    }

    public function cerrarDetalle(): void
    {
        $this->selectedMantenimientoId = null;
    }

    // ==========================================
    // 3. ESTADO DE MODALES Y MODO OPERATIVO
    // ==========================================
    public bool $modalFormulario = false;
    public bool $modalEliminar = false;
    public bool $modalPreview = false;

    // Interruptor de Modo: true = Registrando Retorno | false = Creando o Editando Salida
    public bool $modoRetorno = false;

    // ==========================================
    // 4. CAMPOS DEL FORMULARIO
    // ==========================================
    public ?int $mantenimiento_id = null;
    public ?int $tecnologia_id = null;
    public ?Tecnologia $tecnologiaSeleccionada = null;
    public string $searchTecnologia = '';

    // Campos de Salida a Taller
    public ?int $area_origen_id = null;
    public string $tipo = 'Correctivo';
    public ?string $taller_proveedor = null;
    public string $fecha_envio = '';
    public string $motivo = '';
    public ?string $estado_previo = null;

    // Campos de Retorno de Taller
    public ?string $fecha_ingreso = null;
    public ?string $solucion = null;
    public bool $quedo_operativo = true;

    // Archivos
    public $tempFile;
    public array $archivosNuevos = [];
    public $archivosExistentes = [];

    public ?int $mantenimientoIdAEliminar = null;
    public ?string $previewUrl = null;
    public ?string $previewType = null;
    public ?string $previewName = null;

    // ==========================================
    // VALIDACIONES CONDICIONALES
    // ==========================================
    protected function rules(): array
    {
        return [
            'tecnologia_id'    => 'required|exists:tecnologias,id',
            'area_origen_id'   => 'required|exists:areas,id',
            'tipo'             => 'required|in:Preventivo,Correctivo',
            'taller_proveedor' => 'nullable|string|max:255',
            'fecha_envio'      => 'required|date',
            'fecha_ingreso'    => 'nullable|date|after_or_equal:fecha_envio', // <-- nullable
            'motivo'           => 'required|string|max:500',
            'solucion'         => 'nullable|string|max:1000',
            'quedo_operativo'  => 'boolean',
            'archivosNuevos.*' => 'nullable|file|max:20480',
        ];
    }
    protected $messages = [
        'tecnologia_id.required'       => 'Debe vincular un activo tecnológico.',
        'area_origen_id.required'      => 'El área de origen es requerida.',
        'fecha_envio.required'         => 'Indique la fecha de envío.',
        'fecha_ingreso.required'       => 'La fecha de retorno a oficina es obligatoria.',
        'fecha_ingreso.after_or_equal' => 'La fecha de retorno no puede ser anterior a la de envío.',
        'motivo.required'              => 'Debe especificar el motivo del mantenimiento.',
        'archivosNuevos.*.max'         => 'El archivo no debe exceder los 20MB.',
    ];

    public function mount(): void
    {
        $this->fecha_envio = now()->format('Y-m-d');
    }

    // ==========================================
    // BUSCADOR ASISTIDO DE ACTIVOS
    // ==========================================
    #[Computed]
    public function tecnologiasEncontradas()
    {
        if (strlen(trim($this->searchTecnologia)) < 1) {
            return collect();
        }

        return Tecnologia::whereDoesntHave('salida')
            ->where(function ($q) {
                $q->where('codigo_vin', 'like', "%{$this->searchTecnologia}%")
                  ->orWhere('nombre', 'like', "%{$this->searchTecnologia}%")
                  ->orWhere('serie', 'like', "%{$this->searchTecnologia}%");
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
            if ($this->tecnologiaSeleccionada->estado === 'Disponible') {
                $areaAlmacen = Area::where('nombre', 'ALMACEN')->first() ?? Area::first();
                $this->area_origen_id = $areaAlmacen?->id;
            } elseif ($this->tecnologiaSeleccionada->estado === 'Asignado') {
                $ultimaAsignacion = Asignacion::where('tecnologia_id', $this->tecnologia_id)->latest('id')->first();
                if ($ultimaAsignacion && $ultimaAsignacion->area_destino_id) {
                    $this->area_origen_id = $ultimaAsignacion->area_destino_id;
                }
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
        unset($this->archivosNuevos[$index]);
        $this->archivosNuevos = array_values($this->archivosNuevos);
    }

    public function eliminarArchivoExistente(int $id): void
    {
        $archivo = mantenimiento_archivos::find($id);
        if ($archivo) {
            if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                Storage::disk('local')->delete($archivo->ruta_archivo);
            }
            $archivo->delete();
            $this->archivosExistentes = mantenimiento_archivos::where('mantenimiento_id', $this->mantenimiento_id)->get();
        }
    }

    // ==========================================
    // FLUJO: APERTURA DE MODALES (3 ACCIONES DISTINTAS)
    // ==========================================
    
    // 1. Botón "+ Enviar a Mantenimiento"
    public function abrirModalCrear(): void
    {
        $this->resetFormulario();
        $this->modoRetorno = false;
        $this->modalFormulario = true;
    }

    // 2. Botón Lápiz "[Editar]" (Solo datos de salida)
    public function abrirModalEditar(int $id): void
    {
        $this->resetFormulario();
        $mantenimiento = mantenimiento::with(['archivos', 'areaOrigen'])->findOrFail($id);

        $this->mantenimiento_id       = $mantenimiento->id;
        $this->tecnologia_id          = $mantenimiento->tecnologia_id;
        $this->tecnologiaSeleccionada = Tecnologia::find($mantenimiento->tecnologia_id);
        $this->area_origen_id         = $mantenimiento->area_origen_id;
        $this->tipo                   = $mantenimiento->tipo ?? 'Correctivo';
        $this->taller_proveedor       = $mantenimiento->taller_proveedor;
        $this->fecha_envio            = $mantenimiento->fecha_envio ? $mantenimiento->fecha_envio->format('Y-m-d') : '';
        $this->motivo                 = $mantenimiento->motivo;
        $this->estado_previo          = $mantenimiento->estado_previo;

        // CARGAR LOS CAMPOS DEL RECUADRO VERDE:
        $this->fecha_ingreso          = $mantenimiento->fecha_ingreso ? $mantenimiento->fecha_ingreso->format('Y-m-d') : null;
        $this->solucion               = $mantenimiento->solucion;
        $this->quedo_operativo        = is_null($mantenimiento->quedo_operativo) ? true : (bool) $mantenimiento->quedo_operativo;
        $this->archivosExistentes     = $mantenimiento->archivos;

        $this->modalFormulario = true;
    }

    // 3. Botón Verde "[Retorno]" (Solo datos de llegada)
    public function abrirModalRetorno(int $id): void
    {
        $this->resetFormulario();
        $mantenimiento = mantenimiento::with(['archivos', 'areaOrigen', 'tecnologia'])->findOrFail($id);

        $this->modoRetorno            = true; // Activa modo recepción
        $this->mantenimiento_id       = $mantenimiento->id;
        $this->tecnologia_id          = $mantenimiento->tecnologia_id;
        $this->tecnologiaSeleccionada = $mantenimiento->tecnologia;
        $this->area_origen_id         = $mantenimiento->area_origen_id;
        $this->tipo                   = $mantenimiento->tipo;
        $this->taller_proveedor       = $mantenimiento->taller_proveedor;
        $this->fecha_envio            = $mantenimiento->fecha_envio ? $mantenimiento->fecha_envio->format('Y-m-d') : '';
        $this->motivo                 = $mantenimiento->motivo;
        $this->estado_previo          = $mantenimiento->estado_previo;

        // Carga o predetermina datos de retorno
        $this->fecha_ingreso          = $mantenimiento->fecha_ingreso ? $mantenimiento->fecha_ingreso->format('Y-m-d') : now()->format('Y-m-d');
        $this->solucion               = $mantenimiento->solucion;
        $this->quedo_operativo        = is_null($mantenimiento->quedo_operativo) ? true : (bool) $mantenimiento->quedo_operativo;
        $this->archivosExistentes     = $mantenimiento->archivos;

        $this->modalFormulario = true;
    }

    // ==========================================
    // GUARDAR (DIFERENCIADO SEGÚN EL MODO)
    // ==========================================
    public function guardar(): void
    {
        $this->validate();

        DB::transaction(function () {
            $estadoPrevio = $this->mantenimiento_id 
                ? ($this->estado_previo ?? 'Disponible')
                : $this->tecnologiaSeleccionada->estado;

            // 1. Guardar TODOS los campos juntos (envío + retorno)
            $mantenimiento = mantenimiento::updateOrCreate(
                ['id' => $this->mantenimiento_id],
                [
                    'tecnologia_id'    => $this->tecnologia_id,
                    'area_origen_id'   => $this->area_origen_id,
                    'tipo'             => $this->tipo,
                    'taller_proveedor' => $this->taller_proveedor,
                    'motivo'           => $this->motivo,
                    'fecha_envio'      => $this->fecha_envio,
                    'estado_previo'    => $estadoPrevio,
                    // CAMPOS DE RETORNO (RECUADRO VERDE):
                    'fecha_ingreso'    => $this->fecha_ingreso ?: null,
                    'solucion'         => $this->solucion ?: null,
                    'quedo_operativo'  => (bool) $this->quedo_operativo,
                ]
            );

            // 2. Transición inteligente del estado del equipo
            if ($this->fecha_ingreso) {
                // SI TIENE FECHA DE RETORNO -> Sale del taller
                if (!$this->quedo_operativo) {
                    $nuevoEstado = 'De baja'; // Se declaró irreparable
                } else {
                    $nuevoEstado = $mantenimiento->estado_previo ?: 'Disponible'; // Vuelve a su estado original
                }
            } else {
                // SI NO TIENE FECHA DE RETORNO -> Sigue en taller
                $nuevoEstado = 'En Mantenimiento';
            }

            $this->tecnologiaSeleccionada->update(['estado' => $nuevoEstado]);

            // 3. Guardar archivos nuevos si se subieron
            foreach ($this->archivosNuevos as $archivo) {
                $ruta = $archivo->store('mantenimientos', 'local');
                mantenimiento_archivos::create([
                    'mantenimiento_id' => $mantenimiento->id,
                    'nombre_archivo'   => $archivo->getClientOriginalName(),
                    'ruta_archivo'     => $ruta,
                ]);
            }

            $this->selectedMantenimientoId = $mantenimiento->id;
        });

        $this->modalFormulario = false;
        $this->resetFormulario();
        session()->flash('success', 'Registro de mantenimiento guardado y estado del equipo actualizado.');
    }

    public function resetFormulario(): void
    {
        $this->reset([
            'mantenimiento_id',
            'tecnologia_id',
            'tecnologiaSeleccionada',
            'searchTecnologia',
            'area_origen_id',
            'tipo',
            'taller_proveedor',
            'fecha_ingreso',
            'motivo',
            'solucion',
            'quedo_operativo',
            'estado_previo',
            'modoRetorno',
            'tempFile',
            'archivosNuevos',
            'archivosExistentes'
        ]);
        $this->fecha_envio = now()->format('Y-m-d');
        $this->tipo = 'Correctivo';
        $this->quedo_operativo = true;
        $this->resetErrorBag();
    }

    // ==========================================
    // ELIMINACIÓN Y VISOR
    // ==========================================
    public function confirmarEliminar(int $id): void
    {
        $this->mantenimientoIdAEliminar = $id;
        $this->modalEliminar = true;
    }

    public function eliminarMantenimiento(): void
    {
        if (!$this->mantenimientoIdAEliminar) return;

        DB::transaction(function () {
            $mantenimiento = mantenimiento::with('archivos', 'tecnologia')->findOrFail($this->mantenimientoIdAEliminar);

            foreach ($mantenimiento->archivos as $archivo) {
                if (Storage::disk('local')->exists($archivo->ruta_archivo)) {
                    Storage::disk('local')->delete($archivo->ruta_archivo);
                }
            }

            if ($mantenimiento->tecnologia && $mantenimiento->tecnologia->estado === 'En Mantenimiento') {
                $mantenimiento->tecnologia->update(['estado' => $mantenimiento->estado_previo ?: 'Disponible']);
            }

            $mantenimiento->delete();
        });

        if ($this->selectedMantenimientoId === $this->mantenimientoIdAEliminar) {
            $this->selectedMantenimientoId = null;
        }

        $this->modalEliminar = false;
        $this->mantenimientoIdAEliminar = null;
        session()->flash('success', 'Mantenimiento eliminado correctamente.');
    }

    public function abrirPrevisualizador(int $archivoId): void
    {
        $archivo = mantenimiento_archivos::findOrFail($archivoId);
        if (!Storage::disk('local')->exists($archivo->ruta_archivo)) {
            session()->flash('error', 'El archivo no existe físicamente en el disco local.');
            return;
        }

        $mime = Storage::disk('local')->mimeType($archivo->ruta_archivo);
        $contenido = Storage::disk('local')->get($archivo->ruta_archivo);

        $this->previewName = $archivo->nombre_archivo;
        $this->previewUrl = 'data:' . $mime . ';base64,' . base64_encode($contenido);
        $this->previewType = str_contains($mime, 'image') ? 'image' : (str_contains($mime, 'pdf') ? 'pdf' : 'other');
        $this->modalPreview = true;
    }

    public function cerrarPrevisualizador(): void
    {
        $this->reset(['modalPreview', 'previewUrl', 'previewType', 'previewName']);
    }

    #[Computed]
    public function mantenimientoSeleccionado()
    {
        if (!$this->selectedMantenimientoId) return null;
        return mantenimiento::with(['tecnologia', 'archivos', 'areaOrigen'])->find($this->selectedMantenimientoId);
    }

    public function getFotoBase64(?string $ruta): ?string
    {
        if (!$ruta || !Storage::disk('local')->exists($ruta)) return null;
        $mime = Storage::disk('local')->mimeType($ruta);
        return 'data:' . $mime . ';base64,' . base64_encode(Storage::disk('local')->get($ruta));
    }

/**
     * Exporta el historial técnico y control de mantenimientos a CSV/Excel
     */
    public function exportar()
    {
        // 1. Consulta respetando los mismos filtros de búsqueda en pantalla
        $query = mantenimiento::with(['tecnologia', 'archivos', 'areaOrigen'])
            ->where(function ($query) {
                $query->whereHas('tecnologia', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_vin', 'like', "%{$this->search}%")
                      ->orWhere('marca', 'like', "%{$this->search}%");
                })
                ->orWhereHas('areaOrigen', function ($ao) {
                    $ao->where('nombre', 'like', "%{$this->search}%");
                })
                ->orWhere('motivo', 'like', "%{$this->search}%")
                ->orWhere('taller_proveedor', 'like', "%{$this->search}%")
                ->orWhere('tipo', 'like', "%{$this->search}%");
            })
            ->latest('id');

        $mantenimientos = $query->get();

        // 2. Nombre de archivo con fecha
        $filename = now()->format('Y-m-d') . '_control_mantenimientos.csv';

        // 3. Streaming nativo con BOM UTF-8
        $callback = function () use ($mantenimientos) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para Excel en español
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezados Institucionales
            fputcsv($file, ['REPORTE DE CONTROL TECNICO Y MANTENIMIENTO DE ACTIVOS'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' | Soporte Técnico y Garantías'], ';');
            fputcsv($file, [], ';'); // Separador

            // Columnas de Auditoría Técnica
            fputcsv($file, [
                'Código VIN',
                'Equipo / Activo',
                'Marca',
                'Número de Serie',
                'Área de Procedencia',
                'Tipo de Servicio',
                'Técnico / Taller Responsable',
                'Motivo / Falla Reportada',
                'Fecha de Envío',
                'Fecha de Retorno',
                'Trabajo Realizado / Solución',
                'Condición Final',
                'Sustento Documental (Informes/Facturas)'
            ], ';');

            // Llenado de Filas
            foreach ($mantenimientos as $m) {
                // Determinación del Estado del Servicio
                if ($m->fecha_ingreso) {
                    $fechaRetornoTexto = Carbon::parse($m->fecha_ingreso)->format('d/m/Y');
                    $condicionFinal = $m->quedo_operativo ? 'Operativo (Reincorporado)' : 'Irreparable (Dado de Baja)';
                } else {
                    $fechaRetornoTexto = 'En Taller actualmente';
                    $condicionFinal = 'En Proceso de Revisión';
                }

                $docsCount = $m->archivos->count();
                $sustento = $docsCount > 0 ? "Con Informe ({$docsCount} doc)" : 'Sin archivo adjunto';

                fputcsv($file, [
                    $m->tecnologia?->codigo_vin ?? '—',
                    $m->tecnologia?->nombre ?? '—',
                    $m->tecnologia?->marca ?? '—',
                    $m->tecnologia?->serie ?? 'S/N',
                    $m->areaOrigen?->nombre ?? 'ALMACEN',
                    $m->tipo,
                    $m->taller_proveedor ?? 'Soporte Interno',
                    $m->motivo,
                    $m->fecha_envio ? Carbon::parse($m->fecha_envio)->format('d/m/Y') : '—',
                    $fechaRetornoTexto,
                    $m->solucion ?? 'Pendiente de diagnóstico final',
                    $condicionFinal,
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
        $mantenimientos = mantenimiento::with(['tecnologia', 'archivos', 'areaOrigen'])
            ->where(function ($query) {
                $query->whereHas('tecnologia', function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                      ->orWhere('codigo_vin', 'like', "%{$this->search}%")
                      ->orWhere('marca', 'like', "%{$this->search}%");
                })
                ->orWhereHas('areaOrigen', function ($ao) {
                    $ao->where('nombre', 'like', "%{$this->search}%");
                })
                ->orWhere('motivo', 'like', "%{$this->search}%")
                ->orWhere('taller_proveedor', 'like', "%{$this->search}%")
                ->orWhere('tipo', 'like', "%{$this->search}%");
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        $areas = Area::orderBy('nombre', 'asc')->get();

        return view('livewire.inventario.mantenimiento-t-e-c', [
            'mantenimientos' => $mantenimientos,
            'areas'          => $areas
        ]);
    }
}