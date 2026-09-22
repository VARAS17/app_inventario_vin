<div class="min-h-screen bg-zinc-100 text-zinc-800 text-xs font-sans p-4 space-y-4">

    <!-- ========================================================== -->
    <!-- 1. BARRA SUPERIOR Y BUSCADOR INTELIGENTE DE ACTIVOS        -->
    <!-- ========================================================== -->
    <div class="bg-white border border-zinc-300 p-3 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-none rounded-none">
        <div class="flex items-center gap-2 border-l-4 border-blue-700 pl-3">
            <div>
                <h1 class="font-bold text-sm text-zinc-900 uppercase tracking-wider">Historial y Trazabilidad del Activo</h1>
                <span class="text-[11px] text-zinc-500 font-medium">Línea de tiempo cronológica, auditoría de custodios y archivo de actas</span>
            </div>
        </div>

        <!-- Buscador de Activo con Sugerencias en Tiempo Real -->
        <div class="relative w-full md:w-96">
            <div class="relative">
                <input type="text" 
                       wire:model.live.debounce.250ms="searchTecnologia" 
                       placeholder="Escriba Cód. VIN, serie o nombre para auditar..." 
                       class="w-full bg-zinc-50 border border-zinc-300 py-2 pl-8 pr-3 text-xs text-zinc-900 rounded-none focus:bg-white focus:border-blue-700 focus:ring-0">
                <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <button type="button" 
                    wire:click="exportar" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs uppercase tracking-wider rounded-none transition-colors shrink-0 cursor-pointer shadow-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>
                    {{ $this->activo ? 'Exportar Hoja de Vida (' . $this->activo->codigo_vin . ')' : 'Exportar Trazabilidad Global' }}
                </span>
            </button>

            <!-- Desplegable flotante de resultados de búsqueda -->
            @if ($this->tecnologiasEncontradas->count() > 0)
                <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-zinc-300 z-30 max-h-56 overflow-y-auto divide-y divide-zinc-200 shadow-lg">
                    @foreach ($this->tecnologiasEncontradas as $item)
                        @php $miniFoto = $this->getFotoBase64($item->foto); @endphp
                        <div wire:click="seleccionarTecnologia({{ $item->id }})" 
                             class="p-2 flex items-center gap-3 hover:bg-blue-50 cursor-pointer transition">
                            <div class="w-8 h-8 bg-zinc-200 border border-zinc-300 shrink-0 overflow-hidden flex items-center justify-center">
                                @if ($miniFoto)
                                    <img src="{{ $miniFoto }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[8px] font-mono text-zinc-400">N/A</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0 text-[11px]">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono font-bold text-zinc-900">{{ $item->codigo_vin }}</span>
                                    <span class="font-semibold text-zinc-700 uppercase">[{{ $item->marca }}]</span>
                                </div>
                                <div class="text-zinc-600 truncate">{{ $item->nombre }}</div>
                            </div>
                            <span class="text-[10px] font-mono font-semibold px-1.5 py-0.5 border {{ $item->estado === 'De baja' ? 'bg-rose-100 text-rose-800 border-rose-300' : 'bg-zinc-100 text-zinc-700 border-zinc-300' }}">
                                {{ $item->estado }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Mensaje Flash -->
    @if (session()->has('error'))
        <div class="p-2.5 bg-zinc-900 text-red-400 border-l-4 border-red-500 rounded-none flex items-center justify-between text-xs font-mono">
            <span>[ERROR] {{ session('error') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-zinc-400 hover:text-white font-bold">&times;</button>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 2. CUERPO PRINCIPAL (FICHA HERO + TIMELINE)                -->
    <!-- ========================================================== -->
    @if ($this->activo)
        @php
            $tec = $this->activo;
            $fotoPrincipal = $this->getFotoBase64($tec->foto);
            $ubicacion = $this->ubicacionActual;
        @endphp

        <!-- FICHA TÉCNICA Y SITUACIÓN ACTUAL (CABECERA INFORMATIVA) -->
        <div class="bg-white border border-zinc-300 p-4 rounded-none space-y-4 shadow-none">
            <div class="flex flex-col lg:flex-row gap-4 items-start justify-between border-b border-zinc-200 pb-4">
                
                <!-- Foto y Datos Principales -->
                <div class="flex gap-3.5 items-start">
                    <div class="w-24 h-24 bg-zinc-200 border border-zinc-300 shrink-0 flex items-center justify-center overflow-hidden">
                        @if ($fotoPrincipal)
                            <img src="{{ $fotoPrincipal }}" alt="Activo" class="w-full h-full object-cover">
                        @else
                            <span class="text-[10px] text-zinc-500 font-mono uppercase">Sin Foto</span>
                        @endif
                    </div>

                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm font-bold text-zinc-900 bg-zinc-100 px-2 py-0.5 border border-zinc-300">
                                {{ $tec->codigo_vin }}
                            </span>
                            <span class="text-[11px] font-bold uppercase tracking-wider px-2 py-0.5 border 
                                {{ $tec->estado === 'Disponible' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : '' }}
                                {{ $tec->estado === 'Asignado' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}
                                {{ $tec->estado === 'Prestado' ? 'bg-amber-100 text-amber-800 border-amber-300' : '' }}
                                {{ $tec->estado === 'En Mantenimiento' ? 'bg-orange-100 text-orange-800 border-orange-300' : '' }}
                                {{ $tec->estado === 'De baja' ? 'bg-rose-100 text-rose-800 border-rose-300' : '' }}
                            ">
                                {{ $tec->estado }}
                            </span>
                        </div>
                        <h2 class="text-base font-bold text-zinc-900 leading-tight">{{ $tec->nombre }}</h2>
                        <div class="text-[11px] text-zinc-600 font-mono space-x-2">
                            <span>Marca: <strong class="text-zinc-800">{{ $tec->marca }}</strong></span>
                            <span>|</span>
                            <span>Serie: <strong class="text-zinc-800">{{ $tec->serie ?? 'S/N' }}</strong></span>
                            <span>|</span>
                            <span>Ingreso: <strong class="text-zinc-800">{{ $tec->fecha_ingreso ? \Carbon\Carbon::parse($tec->fecha_ingreso)->format('d-m-Y') : '—' }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta Destacada: Ubicación y Custodio Actual -->
                <div class="w-full lg:w-96 bg-zinc-50 border-l-4 border-blue-700 border-y border-r border-zinc-300 p-3 space-y-1.5">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-500 block">Situación Física en Tiempo Real</span>
                    <div>
                        <span class="text-[10px] text-zinc-400 uppercase font-mono">Ubicación Actual:</span>
                        <div class="font-bold text-xs text-zinc-900">{{ $ubicacion['area'] }}</div>
                    </div>
                    <div>
                        <span class="text-[10px] text-zinc-400 uppercase font-mono">Custodio / Responsable:</span>
                        <div class="font-semibold text-[11px] text-blue-900">{{ $ubicacion['custodio'] }}</div>
                    </div>
                </div>
            </div>

            <!-- Controles del Timeline -->
            <div class="flex items-center justify-between pt-1">
                <span class="text-xs font-bold uppercase tracking-wider text-zinc-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-zinc-800 inline-block"></span>
                    Línea Histórica ({{ $this->timeline->count() }} eventos registrados)
                </span>

                <div class="flex items-center gap-2">
                    <button type="button" 
                            wire:click="cambiarOrden" 
                            class="px-2.5 py-1 bg-white border border-zinc-300 hover:bg-zinc-100 text-zinc-700 text-[11px] font-semibold uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                        </svg>
                        <span>Orden: {{ $ordenCronologico === 'desc' ? 'Más Reciente Primero' : 'Más Antiguo Primero' }}</span>
                    </button>

                    <button type="button" 
                            wire:click="limpiarSeleccion" 
                            class="px-2 py-1 bg-white border border-zinc-300 hover:border-red-500 text-zinc-500 hover:text-red-700 text-[11px] font-bold uppercase">
                        ✕ Cerrar
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- 3. TIMELINE VERTICAL INTERACTIVO                           -->
        <!-- ========================================================== -->
        <div class="relative pl-6 sm:pl-8 space-y-6 before:content-[''] before:absolute before:left-3 sm:before:left-4 before:top-3 before:bottom-3 before:w-0.5 before:bg-zinc-300">
            
            @forelse ($this->timeline as $index => $evento)
                @php
                    // Mapeo de colores por tipo de evento
                    $colorMap = [
                        'ingreso'       => ['dot' => 'bg-emerald-600', 'border' => 'border-l-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-800 border-emerald-300'],
                        'asignacion'    => ['dot' => 'bg-blue-600',    'border' => 'border-l-blue-600',    'badge' => 'bg-blue-50 text-blue-800 border-blue-300'],
                        'prestamo'      => ['dot' => 'bg-amber-600',   'border' => 'border-l-amber-600',   'badge' => 'bg-amber-50 text-amber-800 border-amber-300'],
                        'mantenimiento' => ['dot' => 'bg-orange-600',  'border' => 'border-l-orange-600',  'badge' => 'bg-orange-50 text-orange-800 border-orange-300'],
                        'salida'        => ['dot' => 'bg-rose-600',    'border' => 'border-l-rose-600',    'badge' => 'bg-rose-50 text-rose-800 border-rose-300'],
                    ];
                    $estilo = $colorMap[$evento['tipo']] ?? ['dot' => 'bg-zinc-600', 'border' => 'border-l-zinc-600', 'badge' => 'bg-zinc-100 text-zinc-800 border-zinc-300'];
                @endphp

                <div class="relative group">
                    <!-- Nodo indicador en la línea vertical -->
                    <div class="absolute -left-6 sm:-left-8 top-3.5 w-3.5 h-3.5 {{ $estilo['dot'] }} ring-4 ring-zinc-100 border border-white"></div>

                    <!-- Tarjeta del Evento -->
                    <div class="bg-white border border-zinc-300 {{ $estilo['border'] }} border-l-4 p-4 rounded-none shadow-none space-y-3 transition hover:border-zinc-400">
                        
                        <!-- Encabezado del Nodo -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-zinc-100 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 border text-[10px] font-bold uppercase tracking-wider {{ $estilo['badge'] }}">
                                    {{ $evento['badge'] }}
                                </span>
                                <h3 class="font-bold text-xs text-zinc-900">{{ $evento['titulo'] }}</h3>
                            </div>
                            <span class="font-mono text-[11px] text-zinc-500 font-semibold">
                                {{ $evento['fecha']->format('d/m/Y') }} 
                                <span class="text-zinc-400 font-normal">({{ $evento['fecha']->diffForHumans() }})</span>
                            </span>
                        </div>

                        <!-- Subtítulo y Narrativa -->
                        <div>
                            <div class="text-[11px] font-semibold text-zinc-700">{{ $evento['subtitulo'] }}</div>
                            <p class="text-zinc-600 text-[11px] mt-0.5 leading-relaxed">{{ $evento['descripcion'] }}</p>
                        </div>

                        <!-- Grid de Detalles Estructurados -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 bg-zinc-50 border border-zinc-200 p-2.5 text-[11px]">
                            @foreach ($evento['detalles'] as $etiqueta => $valor)
                                <div>
                                    <span class="text-[10px] uppercase text-zinc-400 font-mono block">{{ $etiqueta }}:</span>
                                    <span class="font-medium text-zinc-800 truncate block" title="{{ $valor }}">{{ $valor }}</span>
                                </div>
                            @endforeach
                        </div>

                        <!-- Evidencias Adjuntas del Evento (Actas, Informes, etc.) -->
                        @if ($evento['archivos']->count() > 0)
                            <div class="pt-2 border-t border-zinc-100 flex flex-wrap items-center gap-2">
                                <span class="text-[10px] font-bold uppercase text-zinc-500 tracking-wider">Actas Adjuntas:</span>
                                @foreach ($evento['archivos'] as $doc)
                                    <button type="button" 
                                            wire:click="previsualizarArchivo('{{ $doc->ruta_archivo }}', '{{ $doc->nombre_archivo }}')"
                                            class="inline-flex items-center gap-1.5 px-2 py-1 bg-white border border-zinc-300 hover:border-blue-700 text-zinc-700 hover:text-blue-700 text-[10px] font-mono font-semibold transition">
                                        <svg class="w-3 h-3 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span class="truncate max-w-[180px]">{{ $doc->nombre_archivo }}</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif

                    </div>
                </div>
            @empty
                <div class="bg-white border border-zinc-300 p-8 text-center text-zinc-400 font-mono text-xs">
                    EL ACTIVO NO TIENE MOVIMIENTOS HISTÓRICOS REGISTRADOS
                </div>
            @endforelse

        </div>

    @else
        <!-- ESTADO VACÍO (CUANDO AÚN NO SE BUSCA NI SELECCIONA UN ACTIVO) -->
        <div class="bg-white border border-zinc-300 p-12 text-center rounded-none shadow-none space-y-3">
            <div class="w-12 h-12 bg-zinc-100 border border-zinc-300 mx-auto flex items-center justify-center text-zinc-400">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h2 class="text-sm font-bold uppercase tracking-wider text-zinc-800">Ningún activo tecnológico seleccionado</h2>
            <p class="text-xs text-zinc-500 max-w-md mx-auto leading-relaxed">
                Utilice la barra superior para buscar un equipo por su <strong>Código VIN</strong>, número de serie o nombre. El sistema reconstruirá su trazabilidad completa en tiempo real.
            </p>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 4. MODAL PREVISUALIZADOR INTEGRADO (BASE64)                -->
    <!-- ========================================================== -->
    @if ($modalPreview)
        <div class="fixed inset-0 z-50 bg-zinc-900/80 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-zinc-400 w-full max-w-4xl h-[85vh] rounded-none flex flex-col shadow-none">
                
                <div class="bg-zinc-900 text-zinc-100 p-2.5 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider shrink-0">
                    <span class="truncate pr-4 font-bold">[EXPEDIENTE] {{ $previewName }}</span>
                    <button type="button" wire:click="cerrarPreview" class="text-zinc-400 hover:text-white font-bold px-2 py-0.5 border border-zinc-700">
                        ✕ CERRAR
                    </button>
                </div>

                <div class="flex-1 bg-zinc-200 p-2 overflow-auto flex items-center justify-center">
                    @if ($previewType === 'image')
                        <img src="{{ $previewUrl }}" alt="Evidencia" class="max-h-full max-w-full object-contain border border-zinc-300 bg-white">
                    @elseif ($previewType === 'pdf')
                        <iframe src="{{ $previewUrl }}" class="w-full h-full border border-zinc-300 bg-white"></iframe>
                    @else
                        <div class="p-6 bg-white border border-zinc-300 text-center font-mono space-y-2">
                            <p class="text-xs text-zinc-700">Este formato no admite previsualización embebida directa.</p>
                            <p class="text-[11px] text-zinc-500">{{ $previewName }}</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif

</div>