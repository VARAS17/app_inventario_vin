<div class="space-y-6">

    {{-- Encabezado y Barra de Herramientas --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-xl border border-gray-200 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <span class="p-2 bg-amber-50 text-amber-800 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </span>
                Trazabilidad y Hoja de Vida de Mobiliario (INMBO)
            </h1>
            <p class="text-xs text-gray-500 mt-1">Expediente histórico, asignaciones, traslados y custodia de bienes muebles.</p>
        </div>

        {{-- Acciones y Buscador --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
            {{-- Botón Exportar CSV --}}
            <button wire:click="exportar"
                    class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-amber-800 bg-amber-50 hover:bg-amber-100/80 border border-amber-200 rounded-lg transition-colors shadow-sm">
                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>{{ $this->activo ? 'Exportar Hoja de Vida' : 'Exportar Libro Maestro' }}</span>
            </button>

            {{-- Buscador Reactivo --}}
            <div class="relative w-full sm:w-80">
                <input type="text"
                       wire:model.live.debounce.300ms="searchMobiliario"
                       placeholder="Buscar por código VIN, placa, nombre..."
                       class="w-full pl-9 pr-8 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-600 focus:border-amber-600 bg-gray-50/50">
                <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>

                @if($searchMobiliario)
                    <button wire:click="limpiarSeleccion" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif

                {{-- Dropdown de Resultados --}}
                @if(count($this->mobiliariosEncontrados) > 0)
                    <div class="absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl z-30 max-h-64 overflow-y-auto">
                        @foreach($this->mobiliariosEncontrados as $item)
                            <button type="button" 
                                    wire:click="seleccionarMobiliario({{ $item->id }})"
                                    class="w-full text-left px-3 py-2.5 text-sm hover:bg-amber-50 border-b border-gray-100 last:border-b-0 flex items-center justify-between transition-colors">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-gray-800 font-mono text-xs">{{ $item->codigo_vin }}</span>
                                        @if($item->codigo_inventario_unt)
                                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1 rounded font-mono">{{ $item->codigo_inventario_unt }}</span>
                                        @endif
                                    </div>
                                    <span class="block text-xs text-gray-600 truncate max-w-[200px]">{{ $item->nombre }}</span>
                                </div>
                                <span class="text-[11px] text-amber-800 bg-amber-100/70 px-2 py-0.5 rounded font-medium">Ver Historial</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if($this->activo)
        @php
            $mobi = $this->activo;
            $ubicacion = $this->ubicacionActual;
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- 1. Tarjeta de Ficha Técnica / Situación Actual --}}
            <div class="lg:col-span-1 space-y-4">
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-800">Situación Actual</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $mobi->estado === 'Asignado' ? 'bg-amber-100 text-amber-800' : ($mobi->estado === 'De baja' ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700') }}">
                            {{ $mobi->estado }}
                        </span>
                    </div>

                    {{-- Foto del activo si existe --}}
                    @if($mobi->foto && $this->getFotoBase64($mobi->foto))
                        <div class="w-full h-44 rounded-lg overflow-hidden bg-gray-50 border border-gray-100">
                            <img src="{{ $this->getFotoBase64($mobi->foto) }}" class="w-full h-full object-cover">
                        </div>
                    @endif

                    <div class="space-y-3 text-sm">
                        <div>
                            <span class="text-xs text-gray-400 block uppercase font-medium">Código VIN</span>
                            <p class="font-bold text-gray-900 font-mono text-base">{{ $mobi->codigo_vin }}</p>
                        </div>

                        @if($mobi->codigo_inventario_unt)
                            <div>
                                <span class="text-xs text-gray-400 block uppercase font-medium">Placa Patrimonial UNT</span>
                                <p class="font-semibold text-gray-800 font-mono text-sm">{{ $mobi->codigo_inventario_unt }}</p>
                            </div>
                        @endif

                        <div>
                            <span class="text-xs text-gray-400 block uppercase font-medium">Nombre / Mueble</span>
                            <p class="font-medium text-gray-800">{{ $mobi->nombre }}</p>
                        </div>

                        @if($mobi->descripcion)
                            <div>
                                <span class="text-xs text-gray-400 block uppercase font-medium">Detalles / Características</span>
                                <p class="text-xs text-gray-600 leading-relaxed">{{ $mobi->descripcion }}</p>
                            </div>
                        @endif

                        <div class="border-t border-gray-100 pt-3">
                            <span class="text-xs text-gray-400 block uppercase font-medium">Ubicación Actual</span>
                            <p class="text-sm font-semibold text-gray-900 flex items-center gap-1.5 mt-0.5">
                                <svg class="w-4 h-4 text-amber-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/>
                                </svg>
                                <span>{{ $ubicacion['area'] }}</span>
                            </p>
                        </div>

                        <div>
                            <span class="text-xs text-gray-400 block uppercase font-medium">Custodio Responsable</span>
                            <p class="text-sm font-medium text-gray-800 mt-0.5">
                                {{ $ubicacion['custodio'] }}
                            </p>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button wire:click="limpiarSeleccion" 
                                class="w-full text-xs text-center py-2 text-gray-600 hover:text-gray-900 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors font-medium">
                            Consultar otro mueble
                        </button>
                    </div>
                </div>
            </div>

            {{-- 2. Línea Cronológica (Timeline) --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-6">
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-gray-900 flex items-center gap-2">
                        <span>Línea Cronológica de Trazabilidad</span>
                        <span class="text-xs font-normal text-gray-400 font-mono">({{ count($this->timeline) }} hitos)</span>
                    </h2>

                    <button wire:click="cambiarOrden" 
                            class="inline-flex items-center gap-1.5 text-xs text-amber-800 hover:text-amber-900 font-medium px-2 py-1 rounded-md bg-amber-50 hover:bg-amber-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"/>
                        </svg>
                        <span>{{ $ordenCronologico === 'desc' ? 'Más recientes primero' : 'Historia desde origen' }}</span>
                    </button>
                </div>

                @if(count($this->timeline) > 0)
                    <div class="relative pl-6 space-y-6 before:absolute before:left-2.5 before:top-2 before:bottom-2 before:w-0.5 before:bg-amber-200">
                        @foreach($this->timeline as $evento)
                            <div class="relative flex items-start group">
                                <div class="absolute -left-6 mt-1 w-5 h-5 rounded-full border-2 border-white bg-amber-700 ring-4 ring-amber-50 flex items-center justify-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                                </div>

                                <div class="w-full bg-gray-50/70 border border-gray-100 rounded-lg p-4 group-hover:border-amber-300 transition-colors space-y-2">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded
                                                {{ $evento['color'] === 'emerald' ? 'bg-emerald-100 text-emerald-800' : ($evento['color'] === 'rose' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">
                                                {{ $evento['badge'] }}
                                            </span>
                                            <h3 class="text-sm font-bold text-gray-900">{{ $evento['titulo'] }}</h3>
                                        </div>
                                        <time class="text-xs text-gray-400 font-mono">
                                            {{ $evento['fecha']->format('d/m/Y - h:i A') }}
                                        </time>
                                    </div>

                                    <p class="text-xs text-gray-500 font-medium">{{ $evento['subtitulo'] }}</p>
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ $evento['descripcion'] }}</p>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-2 border-t border-gray-100 text-xs">
                                        @foreach($evento['detalles'] as $campo => $valor)
                                            <div>
                                                <span class="text-gray-400 font-medium">{{ $campo }}:</span>
                                                <span class="text-gray-800 font-semibold ml-1">{{ $valor }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(isset($evento['archivos']) && count($evento['archivos']) > 0)
                                        <div class="pt-2 border-t border-gray-100 flex flex-wrap items-center gap-2">
                                            <span class="text-[11px] text-gray-400 uppercase font-semibold">Actas adjuntas:</span>
                                            @foreach($evento['archivos'] as $doc)
                                                <button type="button"
                                                        wire:click="previsualizarArchivo('{{ $doc->ruta ?? $doc->archivo_ruta }}', '{{ $doc->nombre_original ?? 'Acta' }}')"
                                                        class="inline-flex items-center gap-1 px-2 py-1 text-[11px] bg-white border border-gray-200 rounded hover:bg-amber-50 hover:text-amber-800 transition-colors">
                                                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                                    </svg>
                                                    <span class="truncate max-w-[130px]">{{ $doc->nombre_original ?? 'Ver documento' }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <p class="text-xs text-gray-400">Sin historial registrado para este mobiliario.</p>
                    </div>
                @endif
            </div>

        </div>
    @else
        <div class="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center">
            <div class="w-12 h-12 bg-amber-50 text-amber-800 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <h3 class="text-sm font-bold text-gray-800">Ningún mobiliario seleccionado</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto mt-1">
                Escribe en el buscador el código VIN (ej. <i>UNT-VINMOB-0001</i>), placa patrimonial o nombre del mueble para cargar su trazabilidad.
            </p>
        </div>
    @endif

    {{-- Visor Modal Base64 --}}
    @if($modalPreview)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h4 class="text-xs font-bold text-gray-800 truncate">{{ $previewName ?? 'Documento Sustento' }}</h4>
                    <button wire:click="cerrarPreview" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 p-4 overflow-auto flex items-center justify-center bg-gray-100 min-h-[400px]">
                    @if($previewType === 'image')
                        <img src="{{ $previewUrl }}" class="max-w-full max-h-[70vh] object-contain rounded border">
                    @elseif($previewType === 'pdf')
                        <iframe src="{{ $previewUrl }}" class="w-full h-[70vh] rounded border"></iframe>
                    @else
                        <div class="text-center py-8">
                            <p class="text-xs text-gray-500">Formato no soportado para previsualización directa.</p>
                            <a href="{{ $previewUrl }}" download="{{ $previewName }}" class="mt-2 inline-block px-3 py-1.5 text-xs bg-amber-800 text-white rounded">Descargar</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>