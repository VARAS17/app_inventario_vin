<div class="min-h-screen bg-zinc-100 text-zinc-800 text-xs font-sans p-4 space-y-4">

    <!-- ========================================================== -->
    <!-- 1. NOTIFICACIONES FLASH                                    -->
    <!-- ========================================================== -->
    @if (session()->has('message'))
        <div class="p-3 bg-zinc-900 text-zinc-100 border-l-4 border-emerald-500 rounded-none flex items-center justify-between text-xs font-mono shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>[ÉXITO] {{ session('message') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-zinc-400 hover:text-white font-bold ml-4 cursor-pointer">&times;</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 bg-zinc-900 text-zinc-100 border-l-4 border-red-500 rounded-none flex items-center justify-between text-xs font-mono shadow-xs">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-zinc-400 hover:text-white font-bold ml-4 cursor-pointer">&times;</button>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 2. BARRA SUPERIOR (TÍTULO, FILTRO DE ESTADO, BÚSQUEDA)     -->
    <!-- ========================================================== -->
    <div class="bg-white border border-zinc-300 p-3 flex flex-col md:flex-row items-start md:items-center justify-between gap-3 shadow-none rounded-none">
        <div class="flex items-center gap-2 border-l-4 border-blue-700 pl-3">
            <div>
                <h1 class="font-bold text-sm text-zinc-900 uppercase tracking-wider">Inventario Tecnológico General</h1>
                <span class="text-[11px] text-zinc-500 font-medium">Catálogo institucional de activos, expedientes digitales y alta inicial</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            
            <!-- FILTRO RÁPIDO DE ESTADO (PESTAÑAS PLANAS) -->
            <div class="inline-flex border border-zinc-300 p-0.5 bg-zinc-100 text-[10px] font-mono font-bold uppercase">
                <button type="button" 
                        wire:click="$set('filtroEstado', 'activos')" 
                        class="px-2.5 py-1 transition-colors cursor-pointer {{ $filtroEstado === 'activos' ? 'bg-blue-700 text-white' : 'text-zinc-600 hover:bg-zinc-200' }}">
                    En Circulación
                </button>
                <button type="button" 
                        wire:click="$set('filtroEstado', 'baja')" 
                        class="px-2.5 py-1 transition-colors cursor-pointer {{ $filtroEstado === 'baja' ? 'bg-rose-700 text-white' : 'text-zinc-600 hover:bg-zinc-200' }}">
                    Dados de Baja
                </button>
                <button type="button" 
                        wire:click="$set('filtroEstado', 'todos')" 
                        class="px-2.5 py-1 transition-colors cursor-pointer {{ $filtroEstado === 'todos' ? 'bg-zinc-800 text-white' : 'text-zinc-600 hover:bg-zinc-200' }}">
                    Todos
                </button>
            </div>

            <!-- Buscador reactivo -->
            <div class="relative w-full sm:w-64">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por VIN, nombre, serie..." 
                       class="w-full bg-zinc-50 border border-zinc-300 py-1.5 pl-8 pr-3 text-xs text-zinc-900 rounded-none focus:bg-white focus:border-blue-700 focus:ring-0">
                <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Botón Registrar -->
            <button type="button" 
                    wire:click="openCreateModal" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs uppercase tracking-wider rounded-none transition-colors shrink-0 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>+ Nuevo Equipo</span>
            </button>
            <button type="button" 
                    wire:click="exportar" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs uppercase tracking-wider rounded-none transition-colors shrink-0 cursor-pointer shadow-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar Excel</span>
            </button>
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- 3. TABLA PRINCIPAL DE EQUIPOS (PAGINADA A 8)               -->
    <!-- ========================================================== -->
    <div class="bg-white border border-zinc-300 rounded-none overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-zinc-800 text-zinc-200 uppercase font-mono text-[11px] tracking-wider border-b border-zinc-300">
                        <th class="p-2.5 font-semibold">Cód. VIN</th>
                        <th class="p-2.5 font-semibold">Equipo / Nombre</th>
                        <th class="p-2.5 font-semibold">Marca</th>
                        <th class="p-2.5 font-semibold">N° Serie</th>
                        <th class="p-2.5 font-semibold">Estado Actual</th>
                        <th class="p-2.5 font-semibold">Fecha Ingreso</th>
                        <th class="p-2.5 font-semibold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
                    @forelse ($tecnologias as $tec)
                        @php
                            $isSelected = ($selectedId === $tec->id);
                        @endphp
                        <tr wire:key="tec-{{ $tec->id }}" 
                            wire:click="selectItem({{ $tec->id }})" 
                            class="cursor-pointer transition-colors text-xs {{ $isSelected ? 'bg-blue-50/90 border-l-4 border-l-blue-700 font-medium' : 'hover:bg-zinc-50' }}">
                            
                            <td class="p-2.5 font-mono font-bold text-zinc-900 whitespace-nowrap">
                                <span class="px-1.5 py-0.5 bg-zinc-100 border border-zinc-300">
                                    {{ $tec->codigo_vin }}
                                </span>
                            </td>
                            <td class="p-2.5 font-medium text-zinc-900">
                                {{ $tec->nombre }}
                            </td>
                            <td class="p-2.5 uppercase font-medium text-zinc-700">
                                {{ $tec->marca }}
                            </td>
                            <td class="p-2.5 font-mono text-zinc-500 whitespace-nowrap">
                                {{ $tec->serie ?? 'S/N' }}
                            </td>
                            <td class="p-2.5 whitespace-nowrap">
                                <span class="px-2 py-0.5 font-mono font-bold text-[10px] uppercase border
                                    {{ $tec->estado === 'Disponible' ? 'bg-emerald-100 text-emerald-800 border-emerald-300' : '' }}
                                    {{ $tec->estado === 'Asignado' ? 'bg-blue-100 text-blue-800 border-blue-300' : '' }}
                                    {{ $tec->estado === 'Prestado' ? 'bg-amber-100 text-amber-800 border-amber-300' : '' }}
                                    {{ $tec->estado === 'En Mantenimiento' ? 'bg-orange-100 text-orange-800 border-orange-300' : '' }}
                                    {{ $tec->estado === 'De baja' ? 'bg-rose-100 text-rose-800 border-rose-300' : '' }}
                                ">
                                    {{ $tec->estado }}
                                </span>
                            </td>
                            <td class="p-2.5 font-mono text-zinc-600 whitespace-nowrap">
                                {{ $tec->fecha_ingreso ? \Carbon\Carbon::parse($tec->fecha_ingreso)->format('d-m-Y') : '—' }}
                            </td>
                            <td class="p-2.5 text-right whitespace-nowrap" onclick="event.stopPropagation()">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" 
                                            wire:click="openEditModal({{ $tec->id }})" 
                                            class="p-1 border border-zinc-300 hover:bg-zinc-100 text-zinc-700 rounded-none cursor-pointer"
                                            title="Editar datos básicos">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>

                                    <button type="button" 
                                            wire:click="confirmarEliminar({{ $tec->id }})" 
                                            class="p-1 border border-red-300 hover:bg-red-50 text-red-700 rounded-none cursor-pointer"
                                            title="Eliminar activo (solo si no tiene historial)">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-zinc-400 font-mono text-xs">
                                NO SE ENCONTRARON ACTIVOS TECNOLÓGICOS CON ESTOS CRITERIOS
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-2.5 border-t border-zinc-300 bg-zinc-50 font-mono text-[11px]">
            {{ $tecnologias->links() }}
        </div>
    </div>

    <!-- ========================================================== -->
    <!-- 4. SECCIÓN INFERIOR: FICHA TÉCNICA + MINI-TIMELINE         -->
    <!-- ========================================================== -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">

        <!-- COLUMNA IZQUIERDA: FICHA TÉCNICA + EXPEDIENTES PDF -->
        <div class="bg-white border border-zinc-300 rounded-none p-4 shadow-none">
            <div class="border-b border-zinc-200 pb-2 mb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-blue-700 inline-block"></span>
                    Ficha Técnica del Activo
                </h2>
                @if ($this->selectedTecnologia)
                    <span class="font-mono text-[11px] text-zinc-500 font-semibold">
                        {{ $this->selectedTecnologia->codigo_vin }}
                    </span>
                @endif
            </div>

            @if ($this->selectedTecnologia)
                @php $tecSel = $this->selectedTecnologia; @endphp
                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    
                    <!-- Foto en Base64 / Disco -->
                    <div class="w-28 h-28 bg-zinc-200 border border-zinc-300 shrink-0 flex items-center justify-center overflow-hidden">
                        @if ($tecSel->foto)
                            <img src="{{ route('tecnologia.foto', $tecSel->foto) }}" alt="Foto" class="w-full h-full object-cover">
                        @else
                            <span class="text-[10px] text-zinc-400 font-mono uppercase text-center p-1">Sin Foto</span>
                        @endif
                    </div>

                    <!-- Datos del Activo -->
                    <div class="flex-1 space-y-1.5 text-xs w-full">
                        <div class="flex justify-between border-b border-zinc-100 pb-1">
                            <span class="text-zinc-500 uppercase text-[10px]">Equipo:</span>
                            <span class="font-bold text-zinc-900">{{ $tecSel->nombre }}</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-1">
                            <span class="text-zinc-500 uppercase text-[10px]">Marca / Serie:</span>
                            <span class="font-medium text-zinc-800">{{ $tecSel->marca }} — {{ $tecSel->serie ?? 'S/N' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-1">
                            <span class="text-zinc-500 uppercase text-[10px]">Estado Operativo:</span>
                            <span class="font-bold uppercase text-[10px]">{{ $tecSel->estado }}</span>
                        </div>
                        <div class="flex justify-between border-b border-zinc-100 pb-1">
                            <span class="text-zinc-500 uppercase text-[10px]">Proveedor:</span>
                            <span class="font-medium text-zinc-800 truncate max-w-[200px]">{{ $tecSel->proveedor }}</span>
                        </div>
                        <div class="flex justify-between pb-1">
                            <span class="text-zinc-500 uppercase text-[10px]">Alta en Sistema:</span>
                            <span class="font-mono text-zinc-700">{{ $tecSel->fecha_ingreso ? \Carbon\Carbon::parse($tecSel->fecha_ingreso)->format('d-m-Y') : '—' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Lista de Documentos Adjuntos con Botón de Ojo -->
                <div class="mt-4 pt-3 border-t border-zinc-200 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-zinc-600">Documentos Adjuntos (Facturas / Pólizas)</span>
                        <span class="font-mono text-[10px] text-zinc-400">Total: {{ $tecSel->archivos->count() }}</span>
                    </div>

                    <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                        @forelse ($tecSel->archivos as $archivo)
                            <div class="p-1.5 bg-zinc-50 border border-zinc-300 flex items-center justify-between hover:bg-zinc-100 transition">
                                <div class="flex items-center gap-1.5 truncate pr-2">
                                    <span class="px-1 bg-zinc-200 text-zinc-800 font-mono font-bold text-[9px]">DOC</span>
                                    <span class="text-[11px] font-mono text-zinc-800 truncate" title="{{ $archivo->nombre_archivo }}">
                                        {{ $archivo->nombre_archivo }}
                                    </span>
                                </div>

                                <button type="button" 
                                        wire:click="abrirPrevisualizador({{ $archivo->id }})" 
                                        class="px-2 py-0.5 bg-white border border-zinc-300 hover:border-blue-700 text-zinc-700 hover:text-blue-700 text-[10px] font-semibold uppercase rounded-none cursor-pointer flex items-center gap-1"
                                        title="Ver documento">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <span>Ver</span>
                                </button>
                            </div>
                        @empty
                            <div class="p-2 bg-zinc-50 border border-dashed border-zinc-300 text-center text-zinc-400 text-[11px] font-mono">
                                SIN EXPEDIENTES ADJUNTOS
                            </div>
                        @endforelse
                    </div>
                </div>

            @else
                <div class="p-8 text-center text-zinc-400 font-mono space-y-1">
                    <p class="text-xs font-semibold uppercase">Ningún activo seleccionado</p>
                    <p class="text-[11px]">Haga clic en una fila de la tabla superior para inspeccionar sus datos y documentos.</p>
                </div>
            @endif
        </div>

        <!-- COLUMNA DERECHA: MINI-TIMELINE (3 ÚLTIMOS MOVIMIENTOS CON DIRECCIÓN) -->
        <div class="bg-white border border-zinc-300 rounded-none p-4 shadow-none">
            <div class="border-b border-zinc-200 pb-2 mb-3 flex items-center justify-between">
                <h2 class="text-xs font-bold uppercase tracking-wider text-zinc-800 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Últimos  Movimientos Registrados
                </h2>
                <span class="text-[10px] font-mono text-zinc-400 uppercase">Top 3 hitos</span>
            </div>

            @if ($this->selectedId && $this->ultimosMovimientos->count() > 0)
                <div class="relative pl-6 space-y-3 pt-3">
                    
                    <!-- LÍNEA VERTICAL CON PUNTA DE FLECHA APUNTANDO HACIA ARRIBA -->
                    <div class="absolute left-2.5 top-0 bottom-2 w-0.5 bg-zinc-300 flex flex-col items-center">
                        <!-- Punta de flecha superior en dirección a la tabla -->
                        <div class="w-0 h-0 border-l-[4px] border-l-transparent border-r-[4px] border-r-transparent border-b-[6px] border-b-blue-700 -translate-y-1"></div>
                    </div>

                    <!-- Indicador sutil de sentido cronológico -->
                    <div class="text-[9px] font-mono font-bold uppercase text-blue-700 flex items-center gap-1 -mt-2 mb-1">
                        <span>↑ Más actual</span>
                    </div>

                    @foreach ($this->ultimosMovimientos as $mov)
                        <div class="relative bg-zinc-50 border border-zinc-200 p-2.5 space-y-1">
                            <!-- Nodo indicador centrado en la línea -->
                            <div class="absolute -left-[19px] top-3.5 w-2.5 h-2.5 bg-zinc-800 border border-white"></div>

                            <div class="flex items-center justify-between">
                                <span class="px-1.5 py-0.2 border text-[9px] font-bold font-mono uppercase {{ $mov['badge'] }}">
                                    {{ $mov['tipo'] }}
                                </span>
                                <span class="text-[10px] font-mono text-zinc-500 font-semibold">
                                    {{ $mov['fecha']->format('d/m/Y') }}
                                </span>
                            </div>

                            <div class="font-bold text-zinc-900 text-xs leading-tight">{{ $mov['titulo'] }}</div>
                            <div class="text-[11px] text-zinc-600 leading-tight">{{ $mov['detalle'] }}</div>
                        </div>
                    @endforeach

                    <!-- Indicador inferior de origen -->
                    <div class="text-[9px] font-mono text-zinc-400 uppercase pl-1 pt-0.5">
                        <span>● Más antiguo</span>
                    </div>
                </div>
            @else
                <div class="p-8 text-center text-zinc-400 font-mono space-y-1">
                    <p class="text-xs font-semibold uppercase">Sin movimientos registrados</p>
                    <p class="text-[11px]">Seleccione un activo para revisar su historial reciente.</p>
                </div>
            @endif
        </div>

    </div>

    <!-- ========================================================== -->
    <!-- 5. MODAL: FORMULARIO CREAR / EDITAR                        -->
    <!-- ========================================================== -->
    @if ($isOpenModal)
        <div class="fixed inset-0 z-50 bg-zinc-900/60 backdrop-blur-none flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white border border-zinc-400 w-full max-w-2xl rounded-none shadow-none text-xs space-y-0">
                
                <div class="bg-zinc-900 text-zinc-100 p-3 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider">
                    <span class="font-bold">
                        {{ $isEditMode ? '[EDITAR] DATOS DEL ACTIVO' : '[NUEVO] REGISTRO DE ACTIVO TECNOLÓGICO' }}
                    </span>
                    <button type="button" wire:click="closeModal" class="text-zinc-400 hover:text-white font-bold cursor-pointer">&times;</button>
                </div>

                <form wire:submit.prevent="save" class="p-4 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        
                        <!-- Código VIN -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Código VIN <span class="text-zinc-400 font-normal lowercase">(automático)</span>
                            </label>
                            <input type="text" wire:model="codigo_vin" readonly class="w-full bg-zinc-200 border border-zinc-300 p-2 text-xs font-mono font-bold text-zinc-700 rounded-none cursor-not-allowed">
                            @error('codigo_vin') <span class="text-red-600 text-[11px] block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Nombre del Equipo -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Nombre del Equipo <span class="text-red-600">*</span>
                            </label>
                            <input type="text" wire:model="nombre" placeholder="Ej: Laptop Dell Latitude 5420" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('nombre') <span class="text-red-600 text-[11px] block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Marca -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Marca <span class="text-red-600">*</span>
                            </label>
                            <input type="text" wire:model="marca" placeholder="Ej: Dell, HP, Lenovo" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('marca') <span class="text-red-600 text-[11px] block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Número de Serie -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Número de Serie <span class="text-zinc-400 font-normal lowercase">(opcional)</span>
                            </label>
                            <input type="text" wire:model="serie" placeholder="Ej: 5CD1234XYZ" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs font-mono rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('serie') <span class="text-red-600 text-[11px] block">{{ $message }}</span> @enderror
                        </div>

                        <!-- ESTADO BLOQUEADO (AUDITORÍA) -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Estado Operativo <span class="text-zinc-400 font-normal lowercase">(protegido)</span>
                            </label>
                            <input type="text" value="{{ $estado }}" disabled class="w-full bg-zinc-200 border border-zinc-300 p-2 text-xs font-bold text-zinc-700 uppercase rounded-none cursor-not-allowed">
                            <span class="text-[9px] text-zinc-500 font-mono block mt-0.5">El estado solo cambia mediante los módulos de movimiento.</span>
                        </div>

                        <!-- Fecha de Ingreso -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Fecha de Ingreso <span class="text-red-600">*</span>
                            </label>
                            <input type="date" wire:model="fecha_ingreso" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs font-mono rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('fecha_ingreso') <span class="text-red-600 text-[11px] block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Proveedor -->
                        <div class="md:col-span-2">
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Proveedor / Comprobante de Compra <span class="text-red-600">*</span>
                            </label>
                            <input type="text" wire:model="proveedor" placeholder="Ej: Tech Solution SAC - O/C N° 458" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('proveedor') <span class="text-red-600 text-[11px] block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Fotografía del Equipo -->
                    <div class="pt-2 border-t border-zinc-200">
                        <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                            Fotografía del Activo {{ $isEditMode ? '(Opcional para actualizar)' : '*' }}
                        </label>
                        <div class="flex items-center gap-3">
                            <div class="w-14 h-14 bg-zinc-100 border border-zinc-300 shrink-0 flex items-center justify-center overflow-hidden">
                                @if ($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                                @elseif ($foto_existente)
                                    <img src="{{ route('tecnologia.foto', $foto_existente) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-[9px] font-mono text-zinc-400 uppercase">Sin Foto</span>
                                @endif
                            </div>
                            <input type="file" wire:model="foto" accept="image/*" class="text-xs font-mono">
                        </div>
                        @error('foto') <span class="text-red-600 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- GESTIÓN AUTOMÁTICA DE EXPEDIENTES / PDFs (+) -->
                    <div class="border-t border-zinc-300 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider">Documentos y Facturas</span>
                                <span class="text-[10px] text-zinc-500">Carga con asignación automática del nombre de archivo</span>
                            </div>

                            <div>
                                <label for="btnSubirArchivoTec" class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 hover:bg-zinc-900 text-zinc-100 font-mono text-[11px] uppercase cursor-pointer rounded-none border border-zinc-900">
                                    <span>+ AGREGAR DOCUMENTO</span>
                                </label>
                                <input type="file" id="btnSubirArchivoTec" wire:model="tempFile" class="hidden">
                            </div>
                        </div>

                        <div wire:loading wire:target="tempFile" class="p-1.5 bg-blue-50 text-blue-800 text-[11px] font-mono border border-blue-200">
                            [CARGANDO DOCUMENTO...] Por favor espere.
                        </div>

                        <!-- Archivos ya guardados en BD (al editar) -->
                        @if ($isEditMode && count($archivosExistentes) > 0)
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono text-zinc-500 uppercase">Documentos registrados:</span>
                                @foreach ($archivosExistentes as $ae)
                                    <div class="p-1.5 bg-zinc-100 border border-zinc-300 flex items-center justify-between font-mono text-[11px]">
                                        <span class="truncate max-w-sm">{{ $ae->nombre_archivo }}</span>
                                        <button type="button" wire:click="eliminarArchivoExistente({{ $ae->id }})" class="text-red-700 font-bold px-1.5 hover:bg-red-100 cursor-pointer">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Archivos nuevos acumulados con "+" -->
                        @if (count($archivosNuevos) > 0)
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono text-blue-700 uppercase font-bold">Nuevos documentos por guardar:</span>
                                @foreach ($archivosNuevos as $idx => $an)
                                    <div class="p-1.5 bg-blue-50/70 border border-blue-200 flex items-center justify-between font-mono text-[11px]">
                                        <span class="truncate max-w-sm">{{ $an->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="eliminarArchivoNuevo({{ $idx }})" class="text-red-700 font-bold px-1.5 hover:bg-red-100 cursor-pointer">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Botones de Acción -->
                    <div class="border-t border-zinc-300 pt-3 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-100 font-semibold uppercase text-xs tracking-wider rounded-none cursor-pointer">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold uppercase text-xs tracking-wider rounded-none disabled:opacity-50 cursor-pointer">
                            <span wire:loading.remove wire:target="save">{{ $isEditMode ? 'Actualizar Activo' : 'Guardar en Catálogo' }}</span>
                            <span wire:loading wire:target="save">Guardando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 6. MODAL: PREVISUALIZADOR INTEGRADO (BASE64)               -->
    <!-- ========================================================== -->
    @if ($modalPreview)
        <div class="fixed inset-0 z-50 bg-zinc-900/80 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-zinc-400 w-full max-w-4xl h-[85vh] rounded-none flex flex-col shadow-none">
                
                <div class="bg-zinc-900 text-zinc-100 p-2.5 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider shrink-0">
                    <span class="truncate pr-4 font-bold">[EXPEDIENTE] {{ $previewName }}</span>
                    <button type="button" wire:click="cerrarPrevisualizador" class="text-zinc-400 hover:text-white font-bold px-2 py-0.5 border border-zinc-700 cursor-pointer">
                        ✕ CERRAR
                    </button>
                </div>

                <div class="flex-1 bg-zinc-200 p-2 overflow-auto flex items-center justify-center">
                    @if ($previewType === 'image')
                        <img src="{{ $previewUrl }}" alt="Vista Previa" class="max-h-full max-w-full object-contain border border-zinc-300 bg-white">
                    @elseif ($previewType === 'pdf')
                        <iframe src="{{ $previewUrl }}" class="w-full h-full border border-zinc-300 bg-white"></iframe>
                    @else
                        <div class="p-6 bg-white border border-zinc-300 text-center font-mono space-y-2">
                            <p class="text-xs text-zinc-700">Este formato no cuenta con visor embebido directo.</p>
                            <p class="text-[11px] text-zinc-500">{{ $previewName }}</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 7. MODAL: CONFIRMACIÓN DE ELIMINACIÓN                      -->
    <!-- ========================================================== -->
    @if ($modalEliminar)
        <div class="fixed inset-0 z-50 bg-zinc-900/60 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-red-500 w-full max-w-md rounded-none shadow-none text-xs space-y-0">
                
                <div class="bg-zinc-900 text-red-400 p-3 font-mono font-bold uppercase text-[11px] tracking-wider border-b border-zinc-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Confirmación de Eliminación</span>
                </div>

                <div class="p-4 space-y-2 bg-zinc-50">
                    <p class="text-zinc-800 leading-relaxed">
                        ¿Está seguro de eliminar permanentemente este activo tecnológico?
                    </p>
                    <p class="text-zinc-500 text-[11px]">
                        Esta acción solo está permitida si el activo no cuenta con ningún movimiento en el historial.
                    </p>
                </div>

                <div class="p-3 bg-white border-t border-zinc-300 flex justify-end gap-2">
                    <button type="button" wire:click="$set('modalEliminar', false)" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-100 font-semibold uppercase text-xs tracking-wider rounded-none cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" wire:click="eliminar" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white font-semibold uppercase text-xs tracking-wider rounded-none cursor-pointer">
                        Eliminar Registro
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>