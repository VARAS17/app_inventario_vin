<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Mobiliario;

class TrazabilidadINMBO extends Component
{
    #[Layout('layouts.app')]

    public string $searchMobiliario = '';
    public ?int $mobiliario_id = null;

    // Orden del Timeline: 'desc' (recientes primero) o 'asc' (antiguos primero)
    public string $ordenCronologico = 'desc';

    // Visor Integrado de Documentos (Base64)
    public bool $modalPreview = false;
    public ?string $previewUrl = null;
    public ?string $previewType = null;
    public ?string $previewName = null;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->seleccionarMobiliario($id);
        }
    }

    // ==========================================
    // 1. BÚSQUEDA CON TUS COLUMNAS REALES
    // ==========================================
    #[Computed]
    public function mobiliariosEncontrados()
    {
        $busqueda = trim($this->searchMobiliario);

        if (strlen($busqueda) < 1) {
            return collect();
        }

        return Mobiliario::where(function ($query) use ($busqueda) {
            $query->where('codigo_vin', 'like', "%{$busqueda}%")
                  ->orWhere('codigo_inventario_unt', 'like', "%{$busqueda}%")
                  ->orWhere('nombre', 'like', "%{$busqueda}%")
                  ->orWhere('descripcion', 'like', "%{$busqueda}%");
        })
        ->limit(8)
        ->get();
    }

    public function seleccionarMobiliario(int $id): void
    {
        $this->mobiliario_id = $id;
        $this->searchMobiliario = '';
    }

    public function limpiarSeleccion(): void
    {
        $this->mobiliario_id = null;
        $this->searchMobiliario = '';
    }

    public function cambiarOrden(): void
    {
        $this->ordenCronologico = ($this->ordenCronologico === 'desc') ? 'asc' : 'desc';
    }

    // ==========================================
    // 2. CONSOLIDACIÓN DE LA HOJA DE VIDA
    // ==========================================
    #[Computed]
    public function activo()
    {
        if (!$this->mobiliario_id) return null;

        return Mobiliario::with([
            'asignaciones.areaOrigen',
            'asignaciones.areaDestino',
            'asignaciones.personal',
            'asignaciones.archivos',
            'salida.areaOrigen',
            'salida.archivos',
            'archivos',
        ])->find($this->mobiliario_id);
    }

    #[Computed]
    public function timeline()
    {
        $mobi = $this->activo;
        if (!$mobi) return collect();

        $eventos = collect();

        // 1. HITO: ALTA ORIGINAL
        if ($mobi->fecha_ingreso || $mobi->created_at) {
            $fechaAlta = $mobi->fecha_ingreso ? Carbon::parse($mobi->fecha_ingreso) : $mobi->created_at;

            $eventos->push([
                'tipo'        => 'ingreso',
                'color'       => 'emerald',
                'badge'       => 'Alta de Mobiliario',
                'fecha'       => $fechaAlta,
                'titulo'      => 'Incorporación a Inventario Institucional',
                'subtitulo'   => 'Alta patrimonial y recepción física',
                'descripcion' => 'Mobiliario registrado bajo proveedor: ' . ($mobi->proveedor ?? 'No especificado') . '.',
                'detalles'    => [
                    'Estado Inicial' => 'Disponible',
                    'Proveedor'      => $mobi->proveedor ?? '—',
                    'Placa UNT'      => $mobi->codigo_inventario_unt ?? 'Sin placa asignada',
                ],
                'archivos'    => $mobi->archivos ?? collect(),
            ]);
        }

        // 2. HITOS: TRANSFERENCIAS / ASIGNACIONES
        if ($mobi->asignaciones) {
            foreach ($mobi->asignaciones as $asig) {
                $origen = $asig->areaOrigen?->nombre ?? 'VIN (Almacén)';
                $destino = $asig->areaDestino?->nombre ?? 'Sin definir';
                $persona = $asig->personal 
                    ? "{$asig->personal->grado_academico} {$asig->personal->nombre} {$asig->personal->apellido}"
                    : 'Área general';

                $fechaEvento = $asig->fecha_traspaso ?? $asig->fecha_transferencia ?? $asig->created_at;

                $eventos->push([
                    'tipo'        => 'transferencia',
                    'color'       => 'amber',
                    'badge'       => 'Transferencia Mobiliario',
                    'fecha'       => Carbon::parse($fechaEvento),
                    'titulo'      => "Traspaso de {$origen} a {$destino}",
                    'subtitulo'   => "Custodio: {$persona}",
                    'descripcion' => "Mobiliario reasignado por redistribución de ambientes o nuevo uso operativo.",
                    'detalles'    => [
                        'Origen'   => $origen,
                        'Destino'  => $destino,
                        'Custodio' => $persona,
                        'Motivo'   => $asig->motivo ?? 'Reubicación interna',
                    ],
                    'archivos'    => $asig->archivos ?? collect(),
                ]);
            }
        }

        // 3. HITO: BAJA DEFINITIVA / SALIDA
        if ($mobi->salida) {
            $sal = $mobi->salida;
            $origen = $sal->areaOrigen?->nombre ?? 'VIN';
            $fechaSalida = $sal->fecha_salida ?? $sal->created_at;

            $eventos->push([
                'tipo'        => 'salida',
                'color'       => 'rose',
                'badge'       => 'Baja Definitiva',
                'fecha'       => Carbon::parse($fechaSalida),
                'titulo'      => "Baja Final: " . ($sal->tipo_baja ?? 'Retiro de inventario'),
                'subtitulo'   => "Destino: " . ($sal->destino_final ?? 'No especificado'),
                'descripcion' => "Retiro final del inventario físico institucional.",
                'detalles'    => [
                    'Área Origen' => $origen,
                    'Tipo Baja'   => $sal->tipo_baja ?? 'Deterioro / Excedencia',
                    'Destino'     => $sal->destino_final ?? 'Almacén Central / Chatarra',
                    'Receptor'    => $sal->responsable_recepcion ?? '—',
                ],
                'archivos'    => $sal->archivos ?? collect(),
            ]);
        }

        return $this->ordenCronologico === 'desc'
            ? $eventos->sortByDesc('fecha')->values()
            : $eventos->sortBy('fecha')->values();
    }

    // ==========================================
    // 3. UBICACIÓN Y CUSTODIO ACTUAL
    // ==========================================
    #[Computed]
    public function ubicacionActual(): array
    {
        $mobi = $this->activo;
        if (!$mobi) return ['area' => '—', 'custodio' => '—'];

        switch ($mobi->estado) {
            case 'Disponible':
                return [
                    'area'     => 'Almacén Central (VIN)',
                    'custodio' => 'Custodia de Almacén',
                ];

            case 'Asignado':
                $ultAsig = $mobi->asignaciones?->sortByDesc('id')->first();
                return [
                    'area'     => $ultAsig?->areaDestino?->nombre ?? 'Oficina / Ambiente Asignado',
                    'custodio' => $ultAsig?->personal 
                        ? "{$ultAsig->personal->grado_academico} {$ultAsig->personal->nombre} {$ultAsig->personal->apellido}" 
                        : 'Responsable de oficina',
                ];

            case 'De baja':
                $sal = $mobi->salida;
                return [
                    'area'     => $sal?->destino_final ?? 'Fuera de Servicio / Baja',
                    'custodio' => 'Dado de baja definitivamente',
                ];

            default:
                return ['area' => 'No determinada', 'custodio' => '—'];
        }
    }

    // ==========================================
    // 4. VISOR DE ARCHIVOS EN BASE64
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

    // ==========================================
    // 5. EXPORTACIONES A CSV
    // ==========================================
    public function exportar()
    {
        return $this->activo ? $this->exportarIndividual() : $this->exportarGlobal();
    }

    private function exportarIndividual()
    {
        $mobi = $this->activo;
        $timeline = $this->timeline;
        $ubicacion = $this->ubicacionActual;

        $filename = $mobi->codigo_vin . '_hoja_de_vida_' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($mobi, $timeline, $ubicacion) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['HOJA DE VIDA Y EXPEDIENTE DE TRAZABILIDAD DE MOBILIARIO'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['[SITUACION TECNICA Y CUSTODIA ACTUAL]'], ';');
            fputcsv($file, ['Código VIN', $mobi->codigo_vin, 'Placa UNT', $mobi->codigo_inventario_unt ?? 'S/N'], ';');
            fputcsv($file, ['Nombre / Mueble', $mobi->nombre, 'Proveedor', $mobi->proveedor ?? '—'], ';');
            fputcsv($file, ['Estado Actual', $mobi->estado, 'Ubicación Actual', $ubicacion['area']], ';');
            fputcsv($file, ['Custodio Actual', $ubicacion['custodio'], '', ''], ';');
            fputcsv($file, [], ';');

            fputcsv($file, ['[CRONOLOGIA COMPLETA DE EVENTOS]'], ';');
            fputcsv($file, ['N° Evento', 'Fecha', 'Hito', 'Operación', 'Descripción', 'Área Involucrada', 'Custodio', 'Sustento'], ';');

            foreach ($timeline as $idx => $ev) {
                $areaTexto = $ev['detalles']['Destino'] ?? $ev['detalles']['Área Origen'] ?? '—';
                $responsableTexto = $ev['detalles']['Custodio'] ?? $ev['detalles']['Receptor'] ?? '—';
                $docsCount = $ev['archivos']->count();
                $sustento = $docsCount > 0 ? "Con Acta ({$docsCount} doc)" : 'Sin acta adjunta';

                fputcsv($file, [
                    $idx + 1,
                    $ev['fecha']->format('d/m/Y'),
                    $ev['badge'],
                    $ev['titulo'],
                    $ev['descripcion'],
                    $areaTexto,
                    $responsableTexto,
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

    private function exportarGlobal()
    {
        $mobiliarios = Mobiliario::with([
            'asignaciones.areaOrigen',
            'asignaciones.areaDestino',
            'asignaciones.personal',
            'salida.areaOrigen',
        ])->orderBy('id', 'asc')->get();

        $filename = 'trazabilidad_global_mobiliario_' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($mobiliarios) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['LIBRO MAESTRO DE TRAZABILIDAD INSTITUCIONAL DE MOBILIARIO (INMBO)'], ';');
            fputcsv($file, ['Universidad Nacional de Trujillo - Vicerrectorado de Investigación (VIN)'], ';');
            fputcsv($file, ['Generado el: ' . now()->format('d/m/Y H:i:s')], ';');
            fputcsv($file, [], ';');

            fputcsv($file, [
                'Código VIN',
                'Placa UNT',
                'Nombre / Denominación',
                'Fecha Evento',
                'Tipo de Evento',
                'Área Origen',
                'Área Destino',
                'Custodio / Responsable',
                'Estado Resultante'
            ], ';');

            foreach ($mobiliarios as $mobi) {
                // 1. Alta
                $fechaAlta = $mobi->fecha_ingreso ? Carbon::parse($mobi->fecha_ingreso)->format('d/m/Y') : $mobi->created_at->format('d/m/Y');
                fputcsv($file, [
                    $mobi->codigo_vin, $mobi->codigo_inventario_unt ?? 'S/N', $mobi->nombre,
                    $fechaAlta, 'ALTA INICIAL', '—', 'ALMACEN', 'Almacén Central', 'Disponible'
                ], ';');

                // 2. Transferencias / Asignaciones
                if ($mobi->asignaciones) {
                    foreach ($mobi->asignaciones as $asig) {
                        $persona = $asig->personal ? "{$asig->personal->nombre} {$asig->personal->apellido}" : 'Área general';
                        $fecha = $asig->fecha_traspaso ?? $asig->created_at;

                        fputcsv($file, [
                            $mobi->codigo_vin, $mobi->codigo_inventario_unt ?? 'S/N', $mobi->nombre,
                            Carbon::parse($fecha)->format('d/m/Y'), 'TRANSFERENCIA',
                            $asig->areaOrigen?->nombre ?? 'ALMACEN', $asig->areaDestino?->nombre ?? '—',
                            $persona, 'Asignado'
                        ], ';');
                    }
                }

                // 3. Salida / Baja
                if ($mobi->salida) {
                    $sal = $mobi->salida;
                    $fecha = $sal->fecha_salida ?? $sal->created_at;

                    fputcsv($file, [
                        $mobi->codigo_vin, $mobi->codigo_inventario_unt ?? 'S/N', $mobi->nombre,
                        Carbon::parse($fecha)->format('d/m/Y'), 'BAJA DEFINITIVA',
                        $sal->areaOrigen?->nombre ?? 'ALMACEN', $sal->destino_final ?? 'Fuera de Servicio',
                        $sal->responsable_recepcion ?? '—', 'De baja'
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
        return view('livewire.trazabilidad-i-n-m-b-o');
    }
}