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
use App\Models\Tecnologia;
use App\Models\tecnologias_archivos;

class Inventariotec extends Component
{
    use WithFileUploads, WithPagination;

    #[Layout('layouts.app')]

    // Buscador y Paginación fija de 8
    public string $search = '';
    public string $filtroEstado = 'activos'; // 'activos' | 'baja' | 'todos'
    protected int $perPage = 10;

    // Item seleccionado para el panel inferior
    public ?int $selectedId = null;

    // Control del Modal Formulario
    public bool $isOpenModal = false;
    public bool $isEditMode = false;
    public ?int $tecnologia_id = null;

    // Campos del Formulario
    public string $codigo_vin = '';
    public string $nombre = '';
    public string $marca = '';
    public ?string $serie = null;
    public string $estado = 'Disponible'; // Blindado
    public string $fecha_ingreso = '';
    public string $proveedor = '';
    public $foto = null;
    public ?string $foto_existente = null;

    // Gestión Estandarizada de Archivos con Botón (+)
    public $tempFile;
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
                Rule::unique('tecnologias', 'codigo_vin')->ignore($this->tecnologia_id),
            ],
            'nombre'           => 'required|string|max:150',
            'marca'            => 'required|string|max:100',
            'serie'            => 'nullable|string|max:100',
            'fecha_ingreso'    => 'required|date',
            'proveedor'        => 'required|string|max:150',
            'foto'             => $this->isEditMode ? 'nullable|image|max:5120' : 'required|image|max:5120',
            'archivosNuevos.*' => 'nullable|file|max:20480', // Máx 20MB
        ];
    }

    protected $messages = [
        'codigo_vin.required'  => 'El código VIN es obligatorio.',
        'codigo_vin.unique'    => 'Este código VIN ya se encuentra registrado.',
        'nombre.required'      => 'El nombre del equipo es obligatorio.',
        'marca.required'       => 'La marca es obligatoria.',
        'fecha_ingreso.date'   => 'Ingrese una fecha válida.',
        'proveedor.required'   => 'El proveedor o número de factura es obligatorio.',
        'foto.required'        => 'Debe adjuntar una foto del equipo.',
        'foto.image'           => 'El archivo debe ser una imagen válida.',
        'foto.max'             => 'La imagen no debe superar los 5MB.',
        'archivosNuevos.*.max' => 'Cada documento no debe superar los 20MB.',
    ];

    // ==========================================
    // SELECCIÓN Y CONSULTA INFERIOR
    // ==========================================
    public function selectItem(int $id): void
    {
        $this->selectedId = ($this->selectedId === $id) ? null : $id;
    }

    #[Computed]
    public function selectedTecnologia()
    {
        if (!$this->selectedId) return null;

        return Tecnologia::with('archivos')->find($this->selectedId);
    }

    /**
     * Reconstruye los 3 movimientos más recientes para la columna inferior derecha
     */
    #[Computed]
    public function ultimosMovimientos()
    {
        if (!$this->selectedId) return collect();

        $tec = Tecnologia::with([
            'asignaciones.areaDestino',
            'asignaciones.personal',
            'prestamos.areaOrigen',
            'mantenimientos.areaOrigen',
            'salida.areaOrigen'
        ])->find($this->selectedId);

        if (!$tec) return collect();

        $eventos = collect();

        // 1. Asignaciones
        foreach ($tec->asignaciones as $asig) {
            $persona = $asig->personal ? "{$asig->personal->nombre} {$asig->personal->apellido}" : 'Área general';
            $eventos->push([
                'tipo'   => 'Asignación',
                'badge'  => 'bg-blue-100 text-blue-800 border-blue-300',
                'fecha'  => Carbon::parse($asig->fecha_traspaso),
                'titulo' => "Traspaso a " . ($asig->areaDestino->nombre ?? 'Oficina'),
                'detalle'=> "Custodio: {$persona}",
            ]);
        }

        // 2. Préstamos
        foreach ($tec->prestamos as $prest) {
            $estadoRet = $prest->fecha_devolucion_real ? 'Devuelto' : 'En curso';
            $eventos->push([
                'tipo'   => 'Préstamo',
                'badge'  => 'bg-amber-100 text-amber-800 border-amber-300',
                'fecha'  => Carbon::parse($prest->fecha_prestamo),
                'titulo' => "Salida a {$prest->area_destino}",
                'detalle'=> "Resp: " . ($prest->responsable ?? 'Externo') . " ({$estadoRet})",
            ]);
        }

        // 3. Mantenimientos (Con indicador de estado: En Taller, Reparado o Irreparable)
        foreach ($tec->mantenimientos as $mant) {
            if ($mant->fecha_ingreso) {
                $estadoTexto = $mant->quedo_operativo 
                    ? 'Finalizado (Operativo)' 
                    : 'Finalizado (Irreparable)';
                $badgeColor = $mant->quedo_operativo 
                    ? 'bg-emerald-100 text-emerald-800 border-emerald-300' 
                    : 'bg-rose-100 text-rose-800 border-rose-300';
                $detalleTexto = "Retornó el " . Carbon::parse($mant->fecha_ingreso)->format('d/m/Y') . ": " . ($mant->solucion ? substr($mant->solucion, 0, 45) . '...' : 'Trabajo concluido');
            } else {
                $estadoTexto = 'En Taller actualmente';
                $badgeColor = 'bg-orange-100 text-orange-800 border-orange-300';
                $detalleTexto = "Falla: " . substr($mant->motivo, 0, 45) . '... (Pendiente de retorno)';
            }

            $eventos->push([
                'tipo'   => 'Mantenimiento',
                'badge'  => $badgeColor,
                'fecha'  => Carbon::parse($mant->fecha_envio),
                'titulo' => "Servicio {$mant->tipo} — [{$estadoTexto}]",
                'detalle'=> $detalleTexto,
            ]);
        }

        // 4. Salida definitiva
        if ($tec->salida) {
            $eventos->push([
                'tipo'   => 'Baja',
                'badge'  => 'bg-rose-100 text-rose-800 border-rose-300',
                'fecha'  => Carbon::parse($tec->salida->fecha_salida),
                'titulo' => "Baja: {$tec->salida->tipo_baja}",
                'detalle'=> "Destino: " . $tec->salida->destino_final,
            ]);
        }

        // 5. Ingreso original (siempre existe)
        if ($tec->fecha_ingreso) {
            $eventos->push([
                'tipo'   => 'Ingreso',
                'badge'  => 'bg-emerald-100 text-emerald-800 border-emerald-300',
                'fecha'  => Carbon::parse($tec->fecha_ingreso),
                'titulo' => 'Alta en Inventario',
                'detalle'=> 'Proveedor: ' . ($tec->proveedor ?? '—'),
            ]);
        }

        // Retorna los 3 más recientes
        return $eventos->sortByDesc('fecha')->take(3)->values();
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
        $doc = tecnologias_archivos::findOrFail($id);

        if (Storage::disk('local')->exists($doc->ruta_archivo)) {
            Storage::disk('local')->delete($doc->ruta_archivo);
        }

        $doc->delete();
        $this->archivosExistentes = tecnologias_archivos::where('tecnologia_id', $this->tecnologia_id)->get();
    }

    // ==========================================
    // OPERACIONES CRUD
    // ==========================================
    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->isEditMode = false;
        $this->codigo_vin = Tecnologia::generarSiguienteCodigoVin();
        $this->fecha_ingreso = now()->format('Y-m-d');
        $this->estado = 'Disponible'; // Siempre nace Disponible
        $this->isOpenModal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->resetForm();
        $this->isEditMode = true;

        $tec = Tecnologia::with('archivos')->findOrFail($id);
        $this->tecnologia_id      = $tec->id;
        $this->codigo_vin         = $tec->codigo_vin;
        $this->nombre             = $tec->nombre;
        $this->marca              = $tec->marca;
        $this->serie              = $tec->serie;
        $this->estado             = $tec->estado; // Solo lectura
        $this->fecha_ingreso      = $tec->fecha_ingreso ? Carbon::parse($tec->fecha_ingreso)->format('Y-m-d') : '';
        $this->proveedor          = $tec->proveedor;
        $this->foto_existente     = $tec->foto;
        $this->archivosExistentes = $tec->archivos;

        $this->isOpenModal = true;
    }

    public function save(): void
    {
        $validatedData = $this->validate();

        DB::transaction(function () use ($validatedData) {
            if ($this->isEditMode) {
                $tecnologia = Tecnologia::findOrFail($this->tecnologia_id);

                if ($this->foto) {
                    if ($tecnologia->foto && Storage::disk('local')->exists($tecnologia->foto)) {
                        Storage::disk('local')->delete($tecnologia->foto);
                    }
                    $validatedData['foto'] = $this->foto->store('tecnologias', 'local');
                } else {
                    unset($validatedData['foto']);
                }

                // El estado no se puede alterar manualmente aquí
                unset($validatedData['estado']);

                $tecnologia->update($validatedData);
                session()->flash('message', 'Equipo tecnológico actualizado con éxito.');
            } else {
                $validatedData['foto'] = $this->foto->store('tecnologias', 'local');
                $validatedData['estado'] = 'Disponible'; // Garantía de estado inicial

                $tecnologia = Tecnologia::create($validatedData);
                session()->flash('message', 'Equipo registrado con código: ' . $tecnologia->codigo_vin);
            }

            // Guardar archivos con su nombre original automáticamente
            if (!empty($this->archivosNuevos)) {
                foreach ($this->archivosNuevos as $archivo) {
                    $ruta = $archivo->store('tecnologias_archivos', 'local');

                    tecnologias_archivos::create([
                        'nombre_archivo' => $archivo->getClientOriginalName(),
                        'ruta_archivo'   => $ruta,
                        'tecnologia_id'  => $tecnologia->id,
                    ]);
                }
            }

            $this->selectedId = $tecnologia->id;
        });

        $this->closeModal();
    }

    public function confirmarEliminar(int $id): void
    {
        $tecnologia = Tecnologia::with(['asignaciones', 'prestamos', 'mantenimientos', 'salida'])->findOrFail($id);

        // BLOQUEO DE SEGURIDAD PARA AUDITORÍA
        $tieneHistorial = $tecnologia->asignaciones->isNotEmpty() 
            || $tecnologia->prestamos->isNotEmpty() 
            || $tecnologia->mantenimientos->isNotEmpty() 
            || !is_null($tecnologia->salida);

        if ($tieneHistorial) {
            session()->flash('error', 'Acción bloqueada: No se puede eliminar este equipo porque cuenta con registros históricos. Para retirarlo del inventario debe registrar su salida en el módulo de Salidas.');
            return;
        }

        $this->idAEliminar = $id;
        $this->modalEliminar = true;
    }

    public function eliminar(): void
    {
        if (!$this->idAEliminar) return;

        DB::transaction(function () {
            $tecnologia = Tecnologia::with('archivos')->findOrFail($this->idAEliminar);

            // Borrar foto física
            if ($tecnologia->foto && Storage::disk('local')->exists($tecnologia->foto)) {
                Storage::disk('local')->delete($tecnologia->foto);
            }

            // Borrar archivos físicos
            foreach ($tecnologia->archivos as $doc) {
                if (Storage::disk('local')->exists($doc->ruta_archivo)) {
                    Storage::disk('local')->delete($doc->ruta_archivo);
                }
            }

            $tecnologia->delete();
        });

        if ($this->selectedId === $this->idAEliminar) {
            $this->selectedId = null;
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
        $archivo = tecnologias_archivos::findOrFail($id);

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
            'tecnologia_id',
            'codigo_vin',
            'nombre',
            'marca',
            'serie',
            'estado',
            'fecha_ingreso',
            'proveedor',
            'foto',
            'foto_existente',
            'tempFile',
            'archivosNuevos',
            'archivosExistentes'
        ]);
        $this->resetValidation();
    }
        /**
     * Exporta el inventario tecnológico a CSV compatible con Excel
         */
    public function exportar()
    {
        // 1. Consulta optimizada con los mismos filtros de la pantalla
        $query = Tecnologia::with([
            'asignaciones' => function ($q) {
                $q->latest('id'); // Trae la asignación más reciente para saber quién lo tiene
            },
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
                  ->orWhere('nombre', 'like', '%' . $this->search . '%')
                  ->orWhere('marca', 'like', '%' . $this->search . '%')
                  ->orWhere('serie', 'like', '%' . $this->search . '%')
                  ->orWhere('proveedor', 'like', '%' . $this->search . '%');
            });
        })
        ->orderBy('id', 'asc');

        $tecnologias = $query->get();

        // 2. Nombre del archivo: FECHA_tecnologico.csv
        $filename = now()->format('Y-m-d') . '_tecnologico.csv';

        // 3. Generación en Streaming (sin tocar disco del servidor)
        $callback = function () use ($tecnologias) {
            $file = fopen('php://output', 'w');

            // BOM UTF-8 para que Excel respete tildes y caracteres en español
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Fila 1: Título Institucional solicitado
            fputcsv($file, ['REPORTE GENERAL DE INVENTARIO TECNOLOGICO'], ';');

            // Fila 2: Subtítulo con fecha y hora de emisión
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' - Sistema Patrimonial VIN UNT'], ';');

            // Fila 3: Separador en blanco
            fputcsv($file, [], ';');

            // Fila 4: Encabezados exactos de columnas
            fputcsv($file, [
                'Código VIN',
                'Nombre del Equipo',
                'Marca',
                'Número de Serie',
                'Estado',
                'Fecha de Ingreso',
                'Proveedor',
                'Custodio Asignado'
            ], ';');

            // Filas de Datos
            foreach ($tecnologias as $t) {
                
                // Determinación del Custodio según el estado
                $custodio = 'Sin asignar (Almacén)';
                if ($t->estado === 'Asignado') {
                    $ultAsig = $t->asignaciones->first();
                    $custodio = $ultAsig && $ultAsig->personal 
                        ? $ultAsig->personal->nombre . ' ' . $ultAsig->personal->apellido 
                        : 'Área general';
                } elseif ($t->estado === 'Prestado') {
                    $custodio = 'En préstamo temporal';
                } elseif ($t->estado === 'En Mantenimiento') {
                    $custodio = 'En taller técnico';
                } elseif ($t->estado === 'De baja') {
                    $custodio = 'Dado de baja definitivamente';
                }

                fputcsv($file, [
                    $t->codigo_vin,
                    $t->nombre,
                    $t->marca,
                    $t->serie ?? 'S/N',
                    $t->estado,
                    $t->fecha_ingreso ? Carbon::parse($t->fecha_ingreso)->format('d/m/Y') : '—',
                    $t->proveedor,
                    $custodio
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
        $tecnologias = Tecnologia::query()
            // Filtro por Estado
            ->when($this->filtroEstado === 'activos', function ($q) {
                $q->where('estado', '!=', 'De baja');
            })
            ->when($this->filtroEstado === 'baja', function ($q) {
                $q->where('estado', 'De baja');
            })
            // Buscador
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('codigo_vin', 'like', '%' . $this->search . '%')
                      ->orWhere('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('marca', 'like', '%' . $this->search . '%')
                      ->orWhere('serie', 'like', '%' . $this->search . '%')
                      ->orWhere('proveedor', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'asc')
            ->paginate($this->perPage);

        return view('livewire.inventario.inventariotec', [
            'tecnologias' => $tecnologias
        ]);
    }
}