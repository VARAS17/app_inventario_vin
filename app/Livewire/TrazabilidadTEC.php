<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Tecnologia;

class TrazabilidadTEC extends Component
{
    #[Layout('layouts.app')]

    // Buscador interactivo de activos
    public string $searchTecnologia = '';
    public ?int $tecnologia_id = null;

    // Orden del Timeline: 'desc' (lo más reciente primero) o 'asc' (historia desde el nacimiento)
    public string $ordenCronologico = 'desc';

    // Visor Integrado de Documentos (Base64)
    public bool $modalPreview = false;
    public ?string $previewUrl = null;
    public ?string $previewType = null;
    public ?string $previewName = null;

    public function mount(?int $id = null): void
    {
        // Si se pasa un ID por URL o parámetro inicial, se carga directamente
        if ($id) {
            $this->seleccionarTecnologia($id);
        }
    }

    // ==========================================
    // 1. BÚSQUEDA Y SELECCIÓN DE ACTIVOS
    // ==========================================
    #[Computed]
    public function tecnologiasEncontradas()
    {
        if (strlen(trim($this->searchTecnologia)) < 1) {
            return collect();
        }

        return Tecnologia::where('codigo_vin', 'like', "%{$this->searchTecnologia}%")
            ->orWhere('nombre', 'like', "%{$this->searchTecnologia}%")
            ->orWhere('serie', 'like', "%{$this->searchTecnologia}%")
            ->orWhere('marca', 'like', "%{$this->searchTecnologia}%")
            ->limit(6)
            ->get();
    }

    public function seleccionarTecnologia(int $id): void
    {
        $this->tecnologia_id = $id;
        $this->searchTecnologia = '';
    }

    public function limpiarSeleccion(): void
    {
        $this->tecnologia_id = null;
        $this->searchTecnologia = '';
    }

    public function cambiarOrden(): void
    {
        $this->ordenCronologico = ($this->ordenCronologico === 'desc') ? 'asc' : 'desc';
    }

    // ==========================================
    // 2. CONSOLIDACIÓN DE LA LÍNEA DE TIEMPO
    // ==========================================
    #[Computed]
    public function activo()
    {
        if (!$this->tecnologia_id) return null;

        return Tecnologia::with([
            'asignaciones.areaOrigen',
            'asignaciones.areaDestino',
            'asignaciones.personal',
            'asignaciones.archivos',
            'prestamos.areaOrigen',
            'prestamos.archivos',
            'mantenimientos.areaOrigen',
            'mantenimientos.archivos',
            'salida.areaOrigen',
            'salida.archivos',
        ])->find($this->tecnologia_id);
    }

    #[Computed]
    public function timeline()
    {
        $tec = $this->activo;
        if (!$tec) return collect();

        $eventos = collect();

        // 1. HITO: INGRESO ORIGINAL AL INVENTARIO
        if ($tec->fecha_ingreso) {
            $eventos->push([
                'tipo'             => 'ingreso',
                'color'            => 'emerald',
                'badge'            => 'Ingreso / Alta Inicial',
                'fecha'            => Carbon::parse($tec->fecha_ingreso),
                'titulo'           => 'Registro Inicial en Inventario',
                'subtitulo'        => 'Adquisición y recepción física del activo',
                'descripcion'      => 'El bien tecnológico ingresó a la oficina bajo el proveedor: ' . ($tec->proveedor ?? 'No especificado') . '.',
                'detalles'         => [
                    'Estado Inicial' => 'Disponible',
                    'Proveedor'      => $tec->proveedor ?? '—',
                ],
                'archivos'         => collect(),
            ]);
        }

        // 2. HITOS: ASIGNACIONES Y TRANSFERENCIAS
        foreach ($tec->asignaciones as $asig) {
            $origen = $asig->areaOrigen?->nombre ?? 'VIN';
            $destino = $asig->areaDestino?->nombre ?? 'Sin definir';
            $persona = $asig->personal 
                ? "{$asig->personal->grado_academico} {$asig->personal->nombre} {$asig->personal->apellido} ({$asig->personal->cargo})"
                : 'Área general (Sin custodio nominal)';

            $eventos->push([
                'tipo'             => 'asignacion',
                'color'            => 'blue',
                'badge'            => 'Transferencia / Asignación',
                'fecha'            => Carbon::parse($asig->fecha_traspaso),
                'titulo'           => "Traspaso de {$origen} a {$destino}",
                'subtitulo'        => "Custodio asignado: {$persona}",
                'descripcion'      => "El bien fue formalmente asignado a {$destino} bajo la responsabilidad directa del personal custodio.",
                'detalles'         => [
                    'Área Origen'   => $origen,
                    'Área Destino'  => $destino,
                    'Custodio'      => $persona,
                ],
                'archivos'         => $asig->archivos,
            ]);
        }

        // 3. HITOS: PRÉSTAMOS EXTERNOS
        foreach ($tec->prestamos as $prest) {
            $origen = $prest->areaOrigen?->nombre ?? 'VIN';
            $retorno = $prest->fecha_devolucion_real
                ? 'Retornado el ' . Carbon::parse($prest->fecha_devolucion_real)->format('d/m/Y')
                : 'Préstamo activo en curso';

            $eventos->push([
                'tipo'             => 'prestamo',
                'color'            => 'amber',
                'badge'            => 'Préstamo Temporal',
                'fecha'            => Carbon::parse($prest->fecha_prestamo),
                'titulo'           => "Préstamo hacia {$prest->area_destino}",
                'subtitulo'        => "Responsable: " . ($prest->responsable ?? 'No registrado'),
                'descripcion'      => "Salida temporal desde {$origen}. Plazo pactado hasta el " . Carbon::parse($prest->fecha_devolucion_pactada)->format('d/m/Y') . ". Estado: {$retorno}.",
                'detalles'         => [
                    'Área Origen'    => $origen,
                    'Destino'        => $prest->area_destino,
                    'Responsable'    => $prest->responsable ?? 'No indicado',
                    'Devolución'     => $retorno,
                    'Observación'    => $prest->observacion_devolucion ?? 'Sin observaciones',
                ],
                'archivos'         => $prest->archivos,
            ]);
        }

        // 4. HITOS: MANTENIMIENTOS
        foreach ($tec->mantenimientos as $mant) {
            $origen = $mant->areaOrigen?->nombre ?? 'VIN';
            $estadoRetorno = $mant->fecha_ingreso
                ? ($mant->quedo_operativo ? 'Reparado y Operativo' : 'Declarado Irreparable')
                : 'Actualmente en revisión técnica';

            $eventos->push([
                'tipo'             => 'mantenimiento',
                'color'            => 'orange',
                'badge'            => "Mantenimiento {$mant->tipo}",
                'fecha'            => Carbon::parse($mant->fecha_envio),
                'titulo'           => "Envío a Mantenimiento ({$mant->tipo})",
                'subtitulo'        => "Técnico/Taller: " . ($mant->taller_proveedor ?? 'Soporte interno'),
                'descripcion'      => "Falla reportada: {$mant->motivo}. Resultado: {$estadoRetorno}.",
                'detalles'         => [
                    'Área de Salida' => $origen,
                    'Motivo'         => $mant->motivo,
                    'Taller'         => $mant->taller_proveedor ?? 'Interno',
                    'Solución'       => $mant->solucion ?? 'Pendiente de informe técnico',
                    'Condición'      => $estadoRetorno,
                ],
                'archivos'         => $mant->archivos,
            ]);
        }

        // 5. HITO: BAJA DEFINITIVA / SALIDA
        if ($tec->salida) {
            $sal = $tec->salida;
            $origen = $sal->areaOrigen?->nombre ?? 'VIN';

            $eventos->push([
                'tipo'             => 'salida',
                'color'            => 'rose',
                'badge'            => 'Baja Definitiva',
                'fecha'            => Carbon::parse($sal->fecha_salida),
                'titulo'           => "Baja Final: {$sal->tipo_baja}",
                'subtitulo'        => "Destino: {$sal->destino_final}",
                'descripcion'      => "El bien salió definitivamente del inventario institucional. Recepcionado por: " . ($sal->responsable_recepcion ?? 'Entidad de destino') . ".",
                'detalles'         => [
                    'Área Origen' => $origen,
                    'Tipo de Baja'=> $sal->tipo_baja,
                    'Destino'     => $sal->destino_final,
                    'Receptor'    => $sal->responsable_recepcion ?? '—',
                ],
                'archivos'         => $sal->archivos,
            ]);
        }

        // Ordenar según preferencia (descendente por defecto)
        return $this->ordenCronologico === 'desc'
            ? $eventos->sortByDesc('fecha')->values()
            : $eventos->sortBy('fecha')->values();
    }

    // ==========================================
    // 3. HELPER: UBICACIÓN Y RESPONSABLE ACTUAL
    // ==========================================
    #[Computed]
    public function ubicacionActual(): array
    {
        $tec = $this->activo;
        if (!$tec) return ['area' => '—', 'custodio' => '—'];

        switch ($tec->estado) {
            case 'Disponible':
                return [
                    'area'     => 'Almacén Central (VIN)',
                    'custodio' => 'En custodia de Almacén',
                ];

            case 'Asignado':
                $ultAsig = $tec->asignaciones->sortByDesc('id')->first();
                return [
                    'area'     => $ultAsig?->areaDestino?->nombre ?? 'Oficina Asignada',
                    'custodio' => $ultAsig?->personal 
                        ? "{$ultAsig->personal->grado_academico} {$ultAsig->personal->nombre} {$ultAsig->personal->apellido}" 
                        : 'Responsable de oficina',
                ];

            case 'Prestado':
                $ultPrest = $tec->prestamos->sortByDesc('id')->first();
                return [
                    'area'     => $ultPrest?->area_destino ?? 'Dependencia Externa',
                    'custodio' => $ultPrest?->responsable ?? 'Responsable de Préstamo',
                ];

            case 'En Mantenimiento':
                $ultMant = $tec->mantenimientos->sortByDesc('id')->first();
                return [
                    'area'     => 'Taller de Soporte / Mantenimiento',
                    'custodio' => $ultMant?->taller_proveedor ?? 'Técnico de Soporte',
                ];

            case 'De baja':
                $sal = $tec->salida;
                return [
                    'area'     => $sal?->destino_final ?? 'Fuera de Servicio / Chatarra',
                    'custodio' => 'Dado de baja definitivamente',
                ];

            default:
                return ['area' => 'No determinada', 'custodio' => '—'];
        }
    }

    // ==========================================
    // 4. VISOR DE EVIDENCIAS EN BASE64
    // ==========================================
    public function previsualizarArchivo(string $rutaArchivo, string $nombreArchivo): void
    {
        if (!Storage::disk('local')->exists($rutaArchivo)) {
            session()->flash('error', 'El documento físico no se encuentra en el almacenamiento local.');
            return;
        }

        $mime = Storage::disk('local')->mimeType($rutaArchivo);
        $contenido = Storage::disk('local')->get($rutaArchivo);

        $this->previewName = $nombreArchivo;
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

    public function cerrarPreview(): void
    {
        $this->reset(['modalPreview', 'previewUrl', 'previewType', 'previewName']);
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
     * Exporta a Excel: Individual (si hay equipo seleccionado) o Global (si no hay ninguno)
     */
    public function exportar()
    {
        return $this->activo ? $this->exportarIndividual() : $this->exportarGlobal();
    }

    /**
     * CASO A: Exportación individual (Hoja de Vida del equipo seleccionado)
     */
    private function exportarIndividual()
    {
        $tec = $this->activo;
        $timeline = $this->timeline;
        $ubicacion = $this->ubicacionActual;

        $filename = $tec->codigo_vin . '_hoja_de_vida_' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($tec, $timeline, $ubicacion) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['HOJA DE VIDA Y EXPEDIENTE DE TRAZABILIDAD DEL ACTIVO'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' | Auditoria Patrimonial'], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['[SITUACION TECNICA Y CUSTODIA ACTUAL]'], ';');
            fputcsv($file, ['Código VIN', $tec->codigo_vin, 'Marca', $tec->marca], ';');
            fputcsv($file, ['Equipo / Nombre', $tec->nombre, 'N° de Serie', $tec->serie ?? 'S/N'], ';');
            fputcsv($file, ['Estado Operativo', $tec->estado, 'Proveedor', $tec->proveedor ?? '—'], ';');
            fputcsv($file, ['Fecha Alta Original', $tec->fecha_ingreso ? Carbon::parse($tec->fecha_ingreso)->format('d/m/Y') : '—', 'Ubicación Física', $ubicacion['area']], ';');
            fputcsv($file, ['Custodio Responsable', $ubicacion['custodio'], '', ''], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['[CRONOLOGIA COMPLETA DE EVENTOS]'], ';');
            fputcsv($file, [
                'N° Evento', 'Fecha', 'Hito / Tipo de Evento', 'Operación Realizada',
                'Descripción Detallada', 'Área / Ubicación Involucrada', 
                'Responsable / Custodio / Técnico', 'Condición / Resultado', 'Sustento Documental'
            ], ';');

            foreach ($timeline as $idx => $ev) {
                $areaTexto = $ev['detalles']['Área Destino'] ?? $ev['detalles']['Destino'] ?? $ev['detalles']['Área de Salida'] ?? $ev['detalles']['Área Origen'] ?? '—';
                $responsableTexto = $ev['detalles']['Custodio'] ?? $ev['detalles']['Responsable'] ?? $ev['detalles']['Taller'] ?? $ev['detalles']['Receptor'] ?? '—';
                $resultado = $ev['detalles']['Condición'] ?? $ev['detalles']['Devolución'] ?? $ev['detalles']['Estado Inicial'] ?? '—';
                $docsCount = $ev['archivos']->count();
                $sustento = $docsCount > 0 ? "Con Acta ({$docsCount} doc)" : 'Sin acta adjunta';

                fputcsv($file, [
                    $idx + 1, $ev['fecha']->format('d/m/Y'), $ev['badge'], $ev['titulo'],
                    $ev['descripcion'], $areaTexto, $responsableTexto, $resultado, $sustento
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * CASO B: Exportación Global (Libro Maestro de toda la historia de todos los equipos)
     */
    private function exportarGlobal()
    {
        $tecnologias = Tecnologia::with([
            'asignaciones.areaOrigen',
            'asignaciones.areaDestino',
            'asignaciones.personal',
            'asignaciones.archivos',
            'prestamos.areaOrigen',
            'prestamos.archivos',
            'mantenimientos.areaOrigen',
            'mantenimientos.archivos',
            'salida.areaOrigen',
            'salida.archivos',
        ])->orderBy('id', 'asc')->get();

        $filename = 'trazabilidad_global_' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($tecnologias) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Encabezado
            fputcsv($file, ['LIBRO MAESTRO DE TRAZABILIDAD INSTITUCIONAL DE ACTIVOS TECNOLOGICOS'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s') . ' | Auditoria General Consolidada'], ';');
            fputcsv($file, [], ';');

            // Columnas de la Sábana Global
            fputcsv($file, [
                'Código VIN',
                'Equipo / Activo',
                'Marca',
                'Número de Serie',
                'Fecha Evento',
                'Tipo de Evento',
                'Área Origen',
                'Área Destino / Destino',
                'Custodio / Responsable / Técnico',
                'Detalle / Motivo / Solución',
                'Estado Resultante',
                'Sustento Documental'
            ], ';');

            // Recorrer cada activo y volcar todos sus hitos históricos
            foreach ($tecnologias as $tec) {
                
                // 1. Ingreso inicial
                if ($tec->fecha_ingreso) {
                    fputcsv($file, [
                        $tec->codigo_vin, $tec->nombre, $tec->marca, $tec->serie ?? 'S/N',
                        Carbon::parse($tec->fecha_ingreso)->format('d/m/Y'),
                        'ALTA INICIAL', '—', 'ALMACEN', 'Almacén Central',
                        'Ingreso por compra/donación. Proveedor: ' . ($tec->proveedor ?? '—'),
                        'Disponible', 'Sin acta inicial'
                    ], ';');
                }

                // 2. Asignaciones / Transferencias
                foreach ($tec->asignaciones as $asig) {
                    $persona = $asig->personal ? "{$asig->personal->nombre} {$asig->personal->apellido}" : 'Área general';
                    $docs = $asig->archivos->count() > 0 ? "Con Acta ({$asig->archivos->count()})" : 'Sin acta';

                    fputcsv($file, [
                        $tec->codigo_vin, $tec->nombre, $tec->marca, $tec->serie ?? 'S/N',
                        Carbon::parse($asig->fecha_traspaso)->format('d/m/Y'),
                        'TRANSFERENCIA', $asig->areaOrigen?->nombre ?? 'ALMACEN', $asig->areaDestino?->nombre ?? '—',
                        $persona, 'Asignación de custodia para funciones institucionales',
                        'Asignado', $docs
                    ], ';');
                }

                // 3. Préstamos
                foreach ($tec->prestamos as $prest) {
                    $retorno = $prest->fecha_devolucion_real ? 'Devuelto el ' . Carbon::parse($prest->fecha_devolucion_real)->format('d/m/Y') : 'En curso';
                    $docs = $prest->archivos->count() > 0 ? "Con Acta ({$prest->archivos->count()})" : 'Sin acta';

                    fputcsv($file, [
                        $tec->codigo_vin, $tec->nombre, $tec->marca, $tec->serie ?? 'S/N',
                        Carbon::parse($prest->fecha_prestamo)->format('d/m/Y'),
                        'PRESTAMO', $prest->areaOrigen?->nombre ?? 'ALMACEN', $prest->area_destino,
                        $prest->responsable ?? 'Externo', 'Salida temporal. Estado: ' . $retorno,
                        'Prestado', $docs
                    ], ';');
                }

                // 4. Mantenimientos
                foreach ($tec->mantenimientos as $mant) {
                    $condicion = $mant->fecha_ingreso ? ($mant->quedo_operativo ? 'Operativo' : 'Irreparable') : 'En Taller';
                    $docs = $mant->archivos->count() > 0 ? "Con Informe ({$mant->archivos->count()})" : 'Sin informe';

                    fputcsv($file, [
                        $tec->codigo_vin, $tec->nombre, $tec->marca, $tec->serie ?? 'S/N',
                        Carbon::parse($mant->fecha_envio)->format('d/m/Y'),
                        'MANTENIMIENTO ' . $mant->tipo, $mant->areaOrigen?->nombre ?? 'ALMACEN', 'Taller Técnico',
                        $mant->taller_proveedor ?? 'Soporte', 'Falla: ' . $mant->motivo . ' | Solución: ' . ($mant->solucion ?? 'En proceso'),
                        $condicion, $docs
                    ], ';');
                }

                // 5. Salida / Baja
                if ($tec->salida) {
                    $sal = $tec->salida;
                    $docs = $sal->archivos->count() > 0 ? "Con Resolución ({$sal->archivos->count()})" : 'Sin acta';

                    fputcsv($file, [
                        $tec->codigo_vin, $tec->nombre, $tec->marca, $tec->serie ?? 'S/N',
                        Carbon::parse($sal->fecha_salida)->format('d/m/Y'),
                        'BAJA DEFINITIVA', $sal->areaOrigen?->nombre ?? 'ALMACEN', $sal->destino_final,
                        $sal->responsable_recepcion ?? '—', 'Causal: ' . $sal->tipo_baja,
                        'De baja', $docs
                    ], ';');
                }
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
        return view('livewire.trazabilidad-t-e-c');
    }
}