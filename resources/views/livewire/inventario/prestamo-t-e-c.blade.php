<div class="p-4 bg-zinc-100 min-h-screen text-zinc-800 font-sans">

    {{-- 1. NOTIFICACIONES DE ACCIÓN --}}
    @if (session()->has('mensaje') || session()->has('success'))
        <div class="mb-4 p-3 bg-emerald-50 border-l-4 border-emerald-600 text-emerald-900 flex justify-between items-center rounded-none shadow-xs">
            <div class="flex items-center space-x-2 text-xs font-medium">
                <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('mensaje') ?? session('success') }}</span>
            </div>
            <button type="button" class="text-emerald-700 hover:text-emerald-900 text-xs font-bold uppercase tracking-wider cursor-pointer" onclick="this.parentElement.remove()">✕</button>
        </div>
    @endif

    {{-- 2. BARRA SUPERIOR FIJA (ESTÁNDAR INSTITUCIONAL) --}}
    <div class="bg-white border border-zinc-300 p-4 mb-4 rounded-none shadow-xs flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="border-l-4 border-blue-700 pl-3">
            <h1 class="text-lg font-bold uppercase tracking-wider text-zinc-900 leading-tight">Control de Préstamos Tecnológicos</h1>
            <p class="text-[11px] text-zinc-500 font-mono">VIN - MÓDULO DE GESTIÓN Y TRAZABILIDAD TEMPORAL</p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-72">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="searchPrestamo" 
                    placeholder="Buscar por activo, VIN o área..."
                    class="w-full text-xs font-mono border-zinc-300 rounded-none focus:border-zinc-800 focus:ring-0 py-1.5 pl-8 pr-3 bg-zinc-50"
                >
                <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <button 
                wire:click="abrirModal"
                class="bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2 rounded-none transition-colors uppercase tracking-wider shrink-0 flex items-center space-x-1.5 shadow-xs cursor-pointer"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Nuevo Préstamo</span>
            </button>
        </div>
    </div>

    {{-- 3. CUERPO DIVIDIDO (MASTER-DETAIL COLAPSABLE) --}}
    <div class="flex flex-col lg:flex-row gap-4 items-start">
        
        {{-- COLUMNA IZQUIERDA: TABLA PRINCIPAL (PAGINACIÓN 8) --}}
        <div class="transition-all duration-200 {{ $detallePrestamo ? 'w-full lg:w-7/12 xl:w-3/5' : 'w-full' }}">
            <div class="bg-white border border-zinc-300 rounded-none shadow-xs overflow-hidden">
                <div class="bg-zinc-800 px-4 py-2.5 flex justify-between items-center text-white">
                    <span class="text-xs font-bold uppercase tracking-wider">Historial de Salidas / Préstamos</span>
                    <span class="text-[11px] font-mono text-zinc-300">Pág. 8 registros</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-zinc-600">
                        <thead class="bg-zinc-100 text-[11px] uppercase font-bold text-zinc-700 border-b border-zinc-300 tracking-wider">
                            <tr>
                                <th class="py-2.5 px-3">Activo / VIN</th>
                                <th class="py-2.5 px-3">Itinerario</th>
                                <th class="py-2.5 px-3">Fechas (Límite)</th>
                                <th class="py-2.5 px-3">Estado</th>
                                <th class="py-2.5 px-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($prestamos as $item)
                                <tr 
                                    wire:click="seleccionarParaDetalle({{ $item->id }})"
                                    class="cursor-pointer transition-colors {{ $prestamoSeleccionadoId === $item->id ? 'bg-blue-50/80 border-l-4 border-blue-700' : 'hover:bg-zinc-50' }}"
                                >
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-zinc-900 leading-tight">{{ $item->tecnologia->nombre }}</div>
                                        <div class="font-mono text-[11px] text-zinc-500">{{ $item->tecnologia->codigo_vin }}</div>
                                    </td>
                                    <td class="py-2.5 px-3 text-[11px]">
                                        <div class="truncate max-w-[140px]"><span class="text-zinc-400">A:</span> <strong class="text-zinc-800">{{ $item->area_destino }}</strong></div>
                                        @if($item->responsable)
                                            <div class="text-zinc-500 truncate max-w-[140px]">{{ $item->responsable }}</div>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 font-mono text-[11px]">
                                        <div>P: {{ \Carbon\Carbon::parse($item->fecha_prestamo)->format('d-m-Y') }}</div>
                                        <div class="font-semibold text-zinc-800">L: {{ \Carbon\Carbon::parse($item->fecha_devolucion_pactada)->format('d-m-Y') }}</div>
                                    </td>
                                    <td class="py-2.5 px-3">
                                        @if(is_null($item->fecha_devolucion_real))
                                            @if($item->esta_vencido)
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 uppercase">
                                                    +{{ $item->dias_retraso }}d mora
                                                </span>
                                            @else
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-300 uppercase">
                                                    En Curso
                                                </span>
                                            @endif
                                        @else
                                            @if($item->entregado_con_retraso)
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold bg-zinc-200 text-rose-700 border border-zinc-300 uppercase">
                                                    Devuelto (+{{ $item->dias_retraso }}d)
                                                </span>
                                            @else
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">
                                                    Concluido
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right space-x-1" onclick="event.stopPropagation()">
                                        @if(is_null($item->fecha_devolucion_real))
                                            <button 
                                                wire:click="devolverActivo({{ $item->id }})"
                                                wire:confirm="¿Confirmar retorno del activo? Volverá a estado: {{ $item->estado_previo }}."
                                                class="px-2 py-1 bg-emerald-700 hover:bg-emerald-800 text-white text-[10px] font-bold uppercase rounded-none transition-colors cursor-pointer"
                                                title="Registrar Devolución"
                                            >
                                                Devolver
                                            </button>
                                        @endif

                                        <button 
                                            wire:click="editar({{ $item->id }})"
                                            class="px-2 py-1 bg-zinc-700 hover:bg-zinc-800 text-white text-[10px] font-bold uppercase rounded-none transition-colors cursor-pointer"
                                            title="Editar Préstamo"
                                        >
                                            Editar
                                        </button>

                                        <button 
                                            wire:click="confirmarEliminar({{ $item->id }})"
                                            class="px-2 py-1 bg-rose-700 hover:bg-rose-800 text-white text-[10px] font-bold uppercase rounded-none transition-colors cursor-pointer"
                                            title="Eliminar Registro"
                                        >
                                            Borrar
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-zinc-400 text-xs font-mono">
                                        NO SE ENCONTRARON REGISTROS DE PRÉSTAMO.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-zinc-50 border-t border-zinc-300">
                    {{ $prestamos->links() }}
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: PANEL DE INSPECCIÓN DETALLADO (COLAPSABLE) --}}
        @if ($detallePrestamo)
            <div class="w-full lg:w-5/12 xl:w-2/5 space-y-4">
                <div class="bg-white border border-zinc-300 rounded-none shadow-xs overflow-hidden">
                    {{-- Encabezado del Panel --}}
                    <div class="bg-zinc-900 text-white px-4 py-2 flex justify-between items-center">
                        <span class="text-xs font-bold uppercase tracking-wider text-zinc-200">Panel de Inspección</span>
                        <button 
                            wire:click="cerrarDetalle" 
                            class="text-zinc-400 hover:text-white font-bold text-xs uppercase tracking-wider cursor-pointer transition-colors"
                        >
                            ✕ Cerrar Detalle
                        </button>
                    </div>

                    {{-- FICHA SUPERIOR: DETALLES E IMAGEN PRINCIPAL DEL BIEN --}}
                    <div class="p-4 border-b border-zinc-200 bg-zinc-50/50">
                        <div class="flex items-start space-x-3">
                            <img 
                                src="{{ $this->obtenerFotoBase64($detallePrestamo->tecnologia->foto) ?? 'https://placehold.co/120x120?text=Sin+Foto' }}" 
                                alt="Foto" 
                                class="w-20 h-20 object-cover border border-zinc-300 rounded-none shrink-0 bg-white"
                            >
                            <div class="w-full text-xs space-y-1">
                                <div class="text-[10px] uppercase font-bold text-blue-700 tracking-wider">Activo Prestado</div>
                                <div class="font-bold text-sm text-zinc-900 leading-tight">{{ $detallePrestamo->tecnologia->nombre }}</div>
                                <div class="font-mono text-zinc-600 text-[11px]"><span class="text-zinc-400">VIN:</span> {{ $detallePrestamo->tecnologia->codigo_vin }}</div>
                                <div class="text-[11px] text-zinc-600"><span class="text-zinc-400">Marca / Serie:</span> {{ $detallePrestamo->tecnologia->marca }} / {{ $detallePrestamo->tecnologia->serie ?? 'S/N' }}</div>
                                <div class="text-[11px] text-zinc-600"><span class="text-zinc-400">Estado Previo:</span> <strong class="text-zinc-800">{{ $detallePrestamo->estado_previo }}</strong></div>
                            </div>
                        </div>
                    </div>

                    {{-- FICHA INFERIOR: RESPONSABLE, ITINERARIO, MORAS Y ARCHIVOS --}}
                    <div class="p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs border-b border-zinc-200 pb-3">
                            <div>
                                <span class="text-[10px] uppercase text-zinc-400 font-bold block">Área de Origen</span>
                                <span class="font-semibold text-zinc-800 font-mono">{{ $detallePrestamo->area_origen }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase text-zinc-400 font-bold block">Área Destino</span>
                                <span class="font-semibold text-zinc-800 font-mono">{{ $detallePrestamo->area_destino }}</span>
                            </div>
                            <div class="col-span-2">
                                <span class="text-[10px] uppercase text-zinc-400 font-bold block">Responsable Asignado</span>
                                <span class="font-semibold text-zinc-800">{{ $detallePrestamo->responsable ?? 'No especificado' }}</span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-xs border-b border-zinc-200 pb-3 font-mono">
                            <div>
                                <span class="text-[10px] uppercase text-zinc-400 font-bold block">Fecha Préstamo</span>
                                <span class="text-zinc-800">{{ \Carbon\Carbon::parse($detallePrestamo->fecha_prestamo)->format('d-m-Y') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] uppercase text-zinc-400 font-bold block">Fecha Límite Pactada</span>
                                <span class="text-zinc-900 font-bold">{{ \Carbon\Carbon::parse($detallePrestamo->fecha_devolucion_pactada)->format('d-m-Y') }}</span>
                            </div>
                            @if($detallePrestamo->fecha_devolucion_real)
                                <div class="col-span-2">
                                    <span class="text-[10px] uppercase text-zinc-400 font-bold block">Fecha Real Retorno</span>
                                    <span class="text-emerald-700 font-bold">{{ \Carbon\Carbon::parse($detallePrestamo->fecha_devolucion_real)->format('d-m-Y') }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- ESTADO DE MORA --}}
                        <div class="text-xs border-b border-zinc-200 pb-3">
                            <span class="text-[10px] uppercase text-zinc-400 font-bold block mb-1">Diagnóstico de Retraso</span>
                            @if(is_null($detallePrestamo->fecha_devolucion_real))
                                @if($detallePrestamo->esta_vencido)
                                    <div class="p-2 bg-rose-50 border border-rose-300 text-rose-800 font-semibold text-[11px]">
                                        EQUIPO EN MORA: +{{ $detallePrestamo->dias_retraso }} día(s) de retraso respecto a la fecha pactada.
                                    </div>
                                @else
                                    <div class="p-2 bg-amber-50 border border-amber-300 text-amber-800 font-semibold text-[11px]">
                                        PRÉSTAMO VIGENTE: Dentro del plazo normal establecido.
                                    </div>
                                @endif
                            @else
                                @if($detallePrestamo->entregado_con_retraso)
                                    <div class="p-2 bg-zinc-100 border border-zinc-300 text-rose-700 font-semibold text-[11px]">
                                        DEVUELTO CON MORA: Se entregó con {{ $detallePrestamo->dias_retraso }} día(s) de atraso.
                                    </div>
                                @else
                                    <div class="p-2 bg-emerald-50 border border-emerald-300 text-emerald-800 font-semibold text-[11px]">
                                        DEVUELTO A TIEMPO: Cumplió cabalmente con la fecha límite.
                                    </div>
                                @endif
                            @endif
                        </div>

                        {{-- LISTA DE ARCHIVOS CON OJO PREVISUALIZADOR --}}
                        <div>
                            <span class="text-[10px] uppercase text-zinc-400 font-bold block mb-1.5">Expedientes Adjuntos</span>
                            <div class="space-y-1.5">
                                @forelse ($detallePrestamo->archivos as $doc)
                                    <div class="flex items-center justify-between p-2 bg-zinc-50 border border-zinc-200 rounded-none text-xs">
                                        <span class="truncate max-w-[200px] font-mono text-[11px] text-zinc-700" title="{{ $doc->nombre_archivo }}">
                                            {{ $doc->nombre_archivo }}
                                        </span>
                                        <button 
                                            type="button" 
                                            wire:click="previsualizarArchivoExistente({{ $doc->id }})"
                                            class="inline-flex items-center space-x-1 px-2 py-0.5 bg-zinc-800 hover:bg-zinc-900 text-white text-[10px] font-semibold uppercase transition-colors cursor-pointer rounded-none"
                                            title="Ver archivo"
                                        >
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>Ver</span>
                                        </button>
                                    </div>
                                @empty
                                    <div class="text-[11px] font-mono text-zinc-400 italic">No hay documentos de sustento adjuntos.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- 4. MODAL CRUD (CREAR / EDITAR) CON BUSCADOR ASISTIDO Y BOTÓN (+) --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/75 backdrop-blur-xs p-4 overflow-y-auto">
            <div class="bg-white border border-zinc-400 rounded-none w-full max-w-2xl shadow-2xl overflow-hidden my-6">
                
                {{-- Cabecera del Modal --}}
                <div class="bg-zinc-900 text-white px-5 py-3 flex justify-between items-center">
                    <div class="flex items-center space-x-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-none"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-white">
                            {{ $isEditing ? 'Modificar Registro de Préstamo' : 'Nueva Salida / Préstamo de Activo' }}
                        </h3>
                    </div>
                    <button wire:click="cerrarModal" class="text-zinc-400 hover:text-white font-bold text-base cursor-pointer">&times;</button>
                </div>

                <form wire:submit.prevent="guardar" class="p-5 space-y-4">
                    
                    {{-- SELECCIÓN VISUAL ASISTIDA DEL ACTIVO (BUSCADOR -> FICHA FIJA CON CAMBIAR) --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-zinc-600 mb-1">Activo Tecnológico *</label>
                        
                        @if ($tecnologiaSeleccionada)
                            {{-- Ficha fija bloqueada con botón cambiar --}}
                            <div class="flex items-center justify-between p-3 bg-zinc-50 border border-zinc-300 rounded-none">
                                <div class="flex items-center space-x-3">
                                    <img 
                                        src="{{ $this->obtenerFotoBase64($tecnologiaSeleccionada->foto) ?? 'https://placehold.co/80x80?text=Sin+Foto' }}" 
                                        alt="Foto" 
                                        class="w-12 h-12 object-cover border border-zinc-300 rounded-none shrink-0"
                                    >
                                    <div class="text-xs">
                                        <div class="font-bold text-zinc-900 leading-tight">{{ $tecnologiaSeleccionada->nombre }}</div>
                                        <div class="font-mono text-[11px] text-zinc-500">VIN: {{ $tecnologiaSeleccionada->codigo_vin }} | Marca: {{ $tecnologiaSeleccionada->marca }}</div>
                                        <div class="text-[10px] text-zinc-400 font-semibold uppercase">Estado: {{ $tecnologiaSeleccionada->estado }}</div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="deseleccionarTecnologia" 
                                    class="px-2 py-1 bg-zinc-200 hover:bg-zinc-300 text-zinc-700 text-[10px] font-bold uppercase tracking-wider rounded-none cursor-pointer"
                                >
                                    Cambiar
                                </button>
                            </div>
                        @else
                            {{-- Input de búsqueda reactiva con sugerencias fotográficas --}}
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.300ms="searchTecnologia" 
                                    placeholder="Escriba VIN, nombre o serie para buscar equipo..."
                                    class="w-full text-xs font-mono border-zinc-300 rounded-none focus:border-zinc-900 focus:ring-0 py-2 px-3 bg-zinc-50"
                                >
                                @if(count($tecnologiasSugeridas) > 0)
                                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-zinc-300 rounded-none shadow-lg z-20 max-h-56 overflow-y-auto divide-y divide-zinc-200">
                                        @foreach($tecnologiasSugeridas as $sug)
                                            <div 
                                                wire:click="seleccionarTecnologia({{ $sug->id }})"
                                                class="p-2 flex items-center space-x-3 hover:bg-blue-50 cursor-pointer transition-colors"
                                            >
                                                <img 
                                                    src="{{ $this->obtenerFotoBase64($sug->foto) ?? 'https://placehold.co/60x60?text=Sin+Foto' }}" 
                                                    class="w-9 h-9 object-cover border border-zinc-200 rounded-none shrink-0"
                                                >
                                                <div class="text-xs">
                                                    <div class="font-bold text-zinc-900">{{ $sug->nombre }}</div>
                                                    <div class="font-mono text-[11px] text-zinc-500">VIN: {{ $sug->codigo_vin }} | {{ $sug->marca }}</div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('tecnologia_id') <span class="text-rose-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- ÁREAS DE ORIGEN Y DESTINO --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-600 mb-1">Área Origen *</label>
                            <select 
                                wire:model="area_origen" 
                                class="w-full text-xs font-mono border-zinc-300 rounded-none focus:border-zinc-900 focus:ring-0 py-2 px-2.5 bg-white"
                            >
                                @foreach($areasOrigen as $area)
                                    <option value="{{ $area }}">{{ $area }}</option>
                                @endforeach
                            </select>
                            @error('area_origen') <span class="text-rose-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-600 mb-1">Área Destino *</label>
                            <input 
                                type="text" 
                                wire:model="area_destino" 
                                placeholder="Ej: Dirección Académica"
                                class="w-full text-xs border-zinc-300 rounded-none focus:border-zinc-900 focus:ring-0 py-2 px-3"
                            >
                            @error('area_destino') <span class="text-rose-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- RESPONSABLE / SOLICITANTE --}}
                    <div>
                        <label class="block text-[11px] font-bold uppercase text-zinc-600 mb-1">Persona Responsable / Solicitante (Opcional)</label>
                        <input 
                            type="text" 
                            wire:model="responsable" 
                            placeholder="Nombre de quien recibe el bien físicamente"
                            class="w-full text-xs border-zinc-300 rounded-none focus:border-zinc-900 focus:ring-0 py-2 px-3"
                        >
                        @error('responsable') <span class="text-rose-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                    </div>

                    {{-- FECHAS PACTADAS --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-600 mb-1">Fecha de Salida / Préstamo *</label>
                            <input 
                                type="date" 
                                wire:model="fecha_prestamo" 
                                class="w-full text-xs font-mono border-zinc-300 rounded-none focus:border-zinc-900 focus:ring-0 py-2 px-3"
                            >
                            @error('fecha_prestamo') <span class="text-rose-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-zinc-600 mb-1">Fecha Límite Pactada *</label>
                            <input 
                                type="date" 
                                wire:model="fecha_devolucion_pactada" 
                                class="w-full text-xs font-mono border-zinc-300 rounded-none focus:border-zinc-900 focus:ring-0 py-2 px-3"
                            >
                            @error('fecha_devolucion_pactada') <span class="text-rose-600 text-[11px] block mt-1 font-semibold">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- CARGA INCREMENTAL DE ARCHIVOS CON BOTÓN (+) --}}
                    <div class="border-t border-zinc-200 pt-3">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-[11px] font-bold uppercase text-zinc-700">Archivos Adjuntos</label>
                            <label class="inline-flex items-center space-x-1 px-2.5 py-1 bg-zinc-800 hover:bg-zinc-900 text-white text-[10px] font-bold uppercase rounded-none cursor-pointer transition-colors shadow-2xs">
                                <span>+ Agregar Archivos</span>
                                <input type="file" wire:model="nuevosArchivos" multiple class="hidden">
                            </label>
                        </div>

                        <div wire:loading wire:target="nuevosArchivos" class="text-xs text-blue-700 py-1 font-mono">
                            Cargando archivos temporales...
                        </div>

                        {{-- Archivos Existentes (Si está en modo edición) --}}
                        @if(!empty($archivosExistentes) && count($archivosExistentes) > 0)
                            <div class="mb-2 space-y-1">
                                <span class="text-[10px] uppercase font-bold text-zinc-500 block">Guardados en BD:</span>
                                @foreach($archivosExistentes as $doc)
                                    <div class="flex items-center justify-between p-1.5 bg-zinc-100 border border-zinc-300 rounded-none text-xs">
                                        <span class="truncate max-w-xs font-mono text-[11px] text-zinc-700">{{ $doc->nombre_archivo }}</span>
                                        <button 
                                            type="button" 
                                            wire:click="eliminarArchivoExistente({{ $doc->id }})" 
                                            class="text-rose-700 hover:text-rose-900 text-[10px] font-bold uppercase cursor-pointer"
                                        >
                                            Eliminar
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Archivos Nuevos Acumulados --}}
                        <div class="space-y-1">
                            @forelse($archivos as $index => $file)
                                <div class="flex items-center justify-between p-1.5 bg-blue-50 border border-blue-200 rounded-none text-xs">
                                    <span class="truncate max-w-xs font-mono text-[11px] text-blue-900">{{ $file->getClientOriginalName() }}</span>
                                    <div class="space-x-2">
                                        <button 
                                            type="button" 
                                            wire:click="previsualizarArchivoNuevo({{ $index }})" 
                                            class="text-blue-700 hover:text-blue-900 text-[10px] font-bold uppercase cursor-pointer"
                                        >
                                            Ver
                                        </button>
                                        <button 
                                            type="button" 
                                            wire:click="eliminarArchivoTemporal({{ $index }})" 
                                            class="text-rose-700 hover:text-rose-900 text-[10px] font-bold uppercase cursor-pointer"
                                        >
                                            Quitar
                                        </button>
                                    </div>
                                </div>
                            @empty
                                @if(empty($archivosExistentes) || count($archivosExistentes) === 0)
                                    <div class="text-[11px] font-mono text-zinc-400 italic p-2 border border-dashed border-zinc-300 text-center">
                                        No se han adjuntado documentos probatorios.
                                    </div>
                                @endif
                            @endforelse
                        </div>
                    </div>

                    {{-- Botones de Acción del Modal --}}
                    <div class="border-t border-zinc-200 pt-3 flex justify-end space-x-2">
                        <button 
                            type="button" 
                            wire:click="cerrarModal" 
                            class="px-4 py-2 border border-zinc-300 text-zinc-700 hover:bg-zinc-100 text-xs font-bold uppercase rounded-none cursor-pointer tracking-wider"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-5 py-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold uppercase rounded-none cursor-pointer tracking-wider disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="guardar">{{ $isEditing ? 'Actualizar Préstamo' : 'Guardar Préstamo' }}</span>
                            <span wire:loading wire:target="guardar">Procesando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- 5. MODAL PREVISUALIZADOR LOCAL INTEGRADO (BASE64) --}}
    @if ($mostrarModalPreview)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/80 backdrop-blur-xs p-4">
            <div class="bg-white border border-zinc-400 rounded-none w-full max-w-4xl h-[85vh] shadow-2xl flex flex-col overflow-hidden">
                <div class="bg-zinc-900 text-white px-4 py-2.5 flex justify-between items-center shrink-0">
                    <span class="text-xs font-bold uppercase tracking-wider font-mono truncate max-w-md">{{ $previewNombre }}</span>
                    <button wire:click="cerrarPreview" class="text-zinc-400 hover:text-white font-bold text-lg cursor-pointer">&times;</button>
                </div>
                
                <div class="flex-1 p-3 bg-zinc-100 flex items-center justify-center overflow-auto">
                    @if ($previewTipo === 'imagen')
                        <img src="{{ $previewSrc }}" class="max-h-full max-w-full object-contain border border-zinc-300 shadow-xs">
                    @elseif ($previewTipo === 'pdf')
                        <iframe src="{{ $previewSrc }}" class="w-full h-full border border-zinc-300"></iframe>
                    @else
                        <div class="text-center font-mono text-xs text-zinc-500">
                            Vista previa no disponible para este formato.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 6. MODAL DE CONFIRMACIÓN DE ELIMINACIÓN --}}
    @if ($mostrarModalEliminar)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-950/70 backdrop-blur-xs p-4">
            <div class="bg-white border border-zinc-300 rounded-none w-full max-w-md p-5 shadow-2xl space-y-4">
                <div class="flex items-center space-x-2 text-rose-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h4 class="text-xs font-bold uppercase tracking-wider">Confirmación de Borrado</h4>
                </div>
                
                <p class="text-xs text-zinc-600">
                    ¿Está seguro de eliminar permanentemente este registro de préstamo? Se purgarán los expedientes del disco local y, si el activo aún estaba en préstamo, se restaurará su estado original.
                </p>

                <div class="flex justify-end space-x-2 pt-2 border-t border-zinc-200">
                    <button 
                        wire:click="$set('mostrarModalEliminar', false)" 
                        class="px-3 py-1.5 border border-zinc-300 text-zinc-700 text-xs font-bold uppercase rounded-none cursor-pointer"
                    >
                        Cancelar
                    </button>
                    <button 
                        wire:click="eliminar" 
                        class="px-3 py-1.5 bg-rose-700 hover:bg-rose-800 text-white text-xs font-bold uppercase rounded-none cursor-pointer tracking-wider"
                    >
                        Eliminar Registro
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>