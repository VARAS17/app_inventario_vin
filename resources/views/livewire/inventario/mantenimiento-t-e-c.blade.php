<div class="min-h-screen bg-zinc-100 text-zinc-800 text-xs font-sans p-4 space-y-4">

    <!-- ========================================================== -->
    <!-- 1. BARRA SUPERIOR FIJA (MÓDULO, BÚSQUEDA Y ACCIÓN)         -->
    <!-- ========================================================== -->
    <div class="bg-white border border-zinc-300 p-3 flex flex-col md:flex-row items-center justify-between gap-3 shadow-none rounded-none">
        <!-- Título con Indicador Azul Institucional -->
        <div class="flex items-center gap-2 border-l-4 border-blue-700 pl-3">
            <div>
                <h1 class="font-bold text-sm text-zinc-900 uppercase tracking-wider">Módulo de Mantenimiento</h1>
                <span class="text-[11px] text-zinc-500 font-medium">Control de activos en taller y gestión de evidencias técnicas</span>
            </div>
        </div>

        <!-- Buscador Reactivo y Botón Nuevo Registro -->
        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-72">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por VIN, activo, área o motivo..." 
                       class="w-full bg-zinc-50 border border-zinc-300 py-1.5 pl-8 pr-3 text-xs text-zinc-900 rounded-none focus:bg-white focus:border-blue-700 focus:ring-0">
                <svg class="w-4 h-4 text-zinc-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <button type="button" 
                    wire:click="abrirModalCrear" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs uppercase tracking-wider rounded-none transition-colors shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                </svg>
                <span>+ Nuevo Registro</span>
            </button>
        </div>
    </div>

    <!-- Alertas del Sistema -->
    @if (session()->has('success'))
        <div class="p-2.5 bg-zinc-900 text-zinc-100 border-l-4 border-emerald-500 rounded-none flex items-center justify-between text-xs font-mono">
            <span>[ÉXITO] {{ session('success') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-zinc-400 hover:text-white font-bold ml-4">&times;</button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="p-2.5 bg-zinc-900 text-zinc-100 border-l-4 border-red-500 rounded-none flex items-center justify-between text-xs font-mono">
            <span>[ERROR] {{ session('error') }}</span>
            <button type="button" @click="$el.parentElement.remove()" class="text-zinc-400 hover:text-white font-bold ml-4">&times;</button>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 2. CUERPO MASTER-DETAIL (TABLA + PANEL COLAPSABLE)         -->
    <!-- ========================================================== -->
    <div class="grid grid-cols-12 gap-4 items-start">

        <!-- TABLA PRINCIPAL (Se expande a 12 si no hay inspección) -->
        <div class="{{ $selectedMantenimientoId ? 'col-span-12 lg:col-span-7 xl:col-span-8' : 'col-span-12' }} transition-all duration-150">
            <div class="bg-white border border-zinc-300 rounded-none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-800 text-zinc-200 uppercase font-mono text-[11px] tracking-wider border-b border-zinc-300">
                                <th class="p-2.5 font-semibold">Cód. VIN</th>
                                <th class="p-2.5 font-semibold">Activo / Marca</th>
                                <th class="p-2.5 font-semibold">Área Origen</th>
                                <th class="p-2.5 font-semibold">F. Envío</th>
                                <th class="p-2.5 font-semibold">F. Retorno</th>
                                <th class="p-2.5 font-semibold text-center">Adjuntos</th>
                                <th class="p-2.5 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($mantenimientos as $item)
                                @php
                                    $isSelected = ($selectedMantenimientoId === $item->id);
                                @endphp
                                <tr wire:key="mant-{{ $item->id }}" 
                                    wire:click="toggleSelect({{ $item->id }})" 
                                    class="cursor-pointer transition-colors text-xs {{ $isSelected ? 'bg-blue-50/90 border-l-4 border-l-blue-700 font-medium' : 'hover:bg-zinc-50' }}">
                                    
                                    <td class="p-2.5 font-mono font-bold text-zinc-900 whitespace-nowrap">
                                        {{ $item->tecnologia->codigo_vin ?? 'N/A' }}
                                    </td>
                                    <td class="p-2.5">
                                        <div class="font-medium text-zinc-900 leading-tight">{{ $item->tecnologia->nombre ?? 'N/A' }}</div>
                                        <div class="text-[11px] text-zinc-500">{{ $item->tecnologia->marca ?? '' }}</div>
                                    </td>
                                    <td class="p-2.5 uppercase font-medium text-zinc-700 whitespace-nowrap">
                                        {{ $item->area_origen }}
                                    </td>
                                    <td class="p-2.5 font-mono text-zinc-600 whitespace-nowrap">
                                        {{ $item->fecha_envio }}
                                    </td>
                                    <td class="p-2.5 font-mono whitespace-nowrap">
                                        @if ($item->fecha_ingreso)
                                            <span class="text-emerald-700 font-semibold">{{ $item->fecha_ingreso }}</span>
                                        @else
                                            <span class="text-amber-700 font-semibold bg-amber-50 px-1 border border-amber-300 uppercase text-[10px]">En Taller</span>
                                        @endif
                                    </td>
                                    <td class="p-2.5 text-center font-mono font-bold text-zinc-600 whitespace-nowrap">
                                        <span class="inline-block px-1.5 py-0.5 bg-zinc-100 border border-zinc-300 text-[10px]">
                                            {{ $item->archivos->count() }}
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-right whitespace-nowrap" wire:click.stop>
                                        <div class="inline-flex items-center gap-1">
                                            <button type="button" 
                                                    wire:click.stop="abrirModalEditar({{ $item->id }})" 
                                                    title="Editar registro"
                                                    class="p-1 border border-zinc-300 hover:bg-zinc-100 text-zinc-700 rounded-none">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <button type="button" 
                                                    wire:click.stop="confirmarEliminar({{ $item->id }})" 
                                                    title="Eliminar registro"
                                                    class="p-1 border border-red-300 hover:bg-red-50 text-red-700 rounded-none">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-8 text-center text-zinc-500 font-mono text-xs">
                                        NO SE ENCONTRARON REGISTROS DE MANTENIMIENTO
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación Estricta de 8 -->
                <div class="p-2.5 border-t border-zinc-300 bg-zinc-50 font-mono text-[11px]">
                    {{ $mantenimientos->links() }}
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO DE INSPECCIÓN COLAPSABLE -->
        @if ($this->mantenimientoSeleccionado)
            @php
                $mant = $this->mantenimientoSeleccionado;
                $tec = $mant->tecnologia;
                $fotoTec = $this->getFotoBase64($tec->foto ?? null);
            @endphp
            <div class="col-span-12 lg:col-span-5 xl:col-span-4 bg-white border border-zinc-300 rounded-none space-y-0 shadow-none">
                
                <!-- Encabezado del Panel -->
                <div class="bg-zinc-900 text-zinc-100 p-2.5 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider border-b border-zinc-300">
                    <span class="font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-blue-500 inline-block"></span>
                        Expediente #{{ str_pad($mant->id, 5, '0', STR_PAD_LEFT) }}
                    </span>
                    <button type="button" 
                            wire:click="cerrarDetalle" 
                            class="text-zinc-400 hover:text-white font-bold px-1.5 py-0.5 border border-zinc-700 hover:border-zinc-500">
                        ✕ CERRAR DETALLE
                    </button>
                </div>

                <!-- FICHA SUPERIOR: Datos e Imagen Principal del Bien -->
                <div class="p-3.5 border-b border-zinc-300 space-y-3 bg-zinc-50/50">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-600 border-b border-zinc-200 pb-1 flex justify-between items-center">
                        <span>Ficha Técnica del Activo</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.5 border {{ $tec->estado === 'En Mantenimiento' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300' }}">
                            {{ $tec->estado }}
                        </span>
                    </div>

                    <div class="flex gap-3 items-start">
                        <!-- Foto del Activo en Base64 -->
                        <div class="w-28 h-28 bg-zinc-200 border border-zinc-300 shrink-0 flex items-center justify-center overflow-hidden">
                            @if ($fotoTec)
                                <img src="{{ $fotoTec }}" alt="Foto" class="w-full h-full object-cover">
                            @else
                                <span class="text-[10px] text-zinc-500 font-mono text-center p-1 uppercase">Sin Imagen</span>
                            @endif
                        </div>

                        <!-- Metadatos del Activo -->
                        <div class="space-y-1 text-[11px] w-full">
                            <div>
                                <span class="text-zinc-500 uppercase block text-[10px]">Nombre / Equipo:</span>
                                <span class="font-bold text-zinc-900 leading-tight">{{ $tec->nombre }}</span>
                            </div>
                            <div class="grid grid-cols-2 gap-1 pt-1">
                                <div>
                                    <span class="text-zinc-500 uppercase block text-[10px]">Cód. VIN:</span>
                                    <span class="font-mono font-bold text-zinc-800">{{ $tec->codigo_vin }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 uppercase block text-[10px]">Marca:</span>
                                    <span class="font-medium text-zinc-800">{{ $tec->marca }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 uppercase block text-[10px]">N° Serie:</span>
                                    <span class="font-mono text-zinc-800">{{ $tec->serie ?? 'S/N' }}</span>
                                </div>
                                <div>
                                    <span class="text-zinc-500 uppercase block text-[10px]">Proveedor:</span>
                                    <span class="text-zinc-800 truncate block">{{ $tec->proveedor }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FICHA INFERIOR: Detalles de Movimiento y Archivos Adjuntos -->
                <div class="p-3.5 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-600 border-b border-zinc-200 pb-1">
                        Detalles del Mantenimiento
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div>
                            <span class="text-zinc-500 uppercase block text-[10px]">Área de Origen:</span>
                            <span class="font-medium text-zinc-900 uppercase">{{ $mant->area_origen }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 uppercase block text-[10px]">Fecha de Envío:</span>
                            <span class="font-mono text-zinc-900">{{ $mant->fecha_envio }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-zinc-500 uppercase block text-[10px]">Fecha de Retorno:</span>
                            <span class="font-mono text-zinc-900">
                                {{ $mant->fecha_ingreso ?? 'PENDIENTE DE RETORNO' }}
                            </span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-zinc-500 uppercase block text-[10px]">Motivo / Diagnóstico:</span>
                            <p class="p-2 bg-zinc-100 border border-zinc-200 text-zinc-800 text-[11px] leading-relaxed">
                                {{ $mant->motivo }}
                            </p>
                        </div>
                    </div>

                    <!-- Lista de Archivos Adjuntos con Botón de Ojo -->
                    <div class="pt-2 border-t border-zinc-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-700">Evidencias Adjuntas</span>
                            <span class="text-[10px] font-mono text-zinc-500">Total: {{ $mant->archivos->count() }}</span>
                        </div>

                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                            @forelse ($mant->archivos as $arch)
                                <div class="p-2 bg-zinc-50 border border-zinc-300 flex items-center justify-between hover:bg-zinc-100 transition">
                                    <div class="flex items-center gap-2 truncate pr-2">
                                        <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                        </svg>
                                        <span class="text-[11px] font-mono text-zinc-800 truncate" title="{{ $arch->nombre_archivo }}">
                                            {{ $arch->nombre_archivo }}
                                        </span>
                                    </div>

                                    <!-- Botón de Ojo para Previsualizar -->
                                    <button type="button" 
                                            wire:click="abrirPrevisualizador({{ $arch->id }})" 
                                            title="Previsualizar archivo"
                                            class="px-2 py-1 bg-white border border-zinc-300 hover:border-blue-700 text-zinc-700 hover:text-blue-700 shrink-0 font-semibold text-[10px] flex items-center gap-1 rounded-none">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <span>Ver</span>
                                    </button>
                                </div>
                            @empty
                                <div class="p-3 bg-zinc-50 border border-dashed border-zinc-300 text-center text-zinc-400 text-[11px] font-mono">
                                    SIN ARCHIVOS ADJUNTOS
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>

    <!-- ========================================================== -->
    <!-- 3. MODAL: FORMULARIO (CREAR / EDITAR)                      -->
    <!-- ========================================================== -->
    @if ($modalFormulario)
        <div class="fixed inset-0 z-50 bg-zinc-900/60 backdrop-blur-none flex items-center justify-center p-4 overflow-y-auto">
            <div class="bg-white border border-zinc-400 w-full max-w-2xl rounded-none shadow-none text-xs space-y-0">
                
                <!-- Encabezado Modal -->
                <div class="bg-zinc-900 text-zinc-100 p-3 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider">
                    <span class="font-bold">
                        {{ $mantenimiento_id ? '[EDITAR] REGISTRO DE MANTENIMIENTO' : '[NUEVO] REGISTRO DE MANTENIMIENTO' }}
                    </span>
                    <button type="button" wire:click="$set('modalFormulario', false)" class="text-zinc-400 hover:text-white font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="guardar" class="p-4 space-y-4">

                    <!-- BÚSQUEDA VISUAL ASISTIDA DE ACTIVOS -->
                    <div class="space-y-1.5">
                        <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider">
                            Activo Tecnológico Vinculado <span class="text-red-600">*</span>
                        </label>

                        @if ($tecnologiaSeleccionada)
                            <!-- Ficha fija del bien seleccionado -->
                            @php
                                $fotoModal = $this->getFotoBase64($tecnologiaSeleccionada->foto);
                            @endphp
                            <div class="p-2.5 bg-zinc-50 border border-zinc-300 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-zinc-200 border border-zinc-300 shrink-0 overflow-hidden flex items-center justify-center">
                                        @if ($fotoModal)
                                            <img src="{{ $fotoModal }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-[9px] font-mono text-zinc-400">N/A</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-zinc-900">{{ $tecnologiaSeleccionada->nombre }}</div>
                                        <div class="font-mono text-[11px] text-zinc-600">
                                            VIN: {{ $tecnologiaSeleccionada->codigo_vin }} | Marca: {{ $tecnologiaSeleccionada->marca }}
                                        </div>
                                    </div>
                                </div>
                                <button type="button" 
                                        wire:click="cambiarTecnologia" 
                                        class="px-2 py-1 bg-white border border-zinc-300 hover:border-zinc-500 font-semibold uppercase text-[10px] tracking-wider rounded-none">
                                    Cambiar
                                </button>
                            </div>
                        @else
                            <!-- Buscador en vivo de activos -->
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.250ms="searchTecnologia" 
                                       placeholder="Escriba VIN, nombre o serie para buscar..." 
                                       class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">

                                @if ($this->tecnologiasEncontradas->count() > 0)
                                    <div class="absolute left-0 right-0 top-full bg-white border border-zinc-300 z-10 max-h-48 overflow-y-auto divide-y divide-zinc-200 shadow-sm">
                                        @foreach ($this->tecnologiasEncontradas as $itemTec)
                                            @php
                                                $miniFoto = $this->getFotoBase64($itemTec->foto);
                                            @endphp
                                            <div wire:click="seleccionarTecnologia({{ $itemTec->id }})" 
                                                 class="p-2 flex items-center gap-3 hover:bg-blue-50 cursor-pointer transition">
                                                <div class="w-8 h-8 bg-zinc-200 border border-zinc-300 shrink-0 overflow-hidden flex items-center justify-center">
                                                    @if ($miniFoto)
                                                        <img src="{{ $miniFoto }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span class="text-[8px] font-mono text-zinc-400">N/A</span>
                                                    @endif
                                                </div>
                                                <div class="text-[11px]">
                                                    <span class="font-mono font-bold text-zinc-900">{{ $itemTec->codigo_vin }}</span>
                                                    - {{ $itemTec->nombre }} ({{ $itemTec->marca }})
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('tecnologia_id') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Campos del Movimiento -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Área de Origen <span class="text-red-600">*</span>
                            </label>
                            <select wire:model="area_origen" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                                <option value="">-- SELECCIONAR --</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a }}">{{ $a }}</option>
                                @endforeach
                            </select>
                            @error('area_origen') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Fecha de Envío <span class="text-red-600">*</span>
                            </label>
                            <input type="date" wire:model="fecha_envio" class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs font-mono rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('fecha_envio') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Fecha de Retorno <span class="text-zinc-400 font-normal lowercase">(opcional)</span>
                            </label>
                            <input type="date" wire:model="fecha_ingreso" class="w-full md:w-1/2 bg-zinc-50 border border-zinc-300 p-2 text-xs font-mono rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('fecha_ingreso') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Motivo del Mantenimiento <span class="text-red-600">*</span>
                            </label>
                            <textarea wire:model="motivo" rows="3" placeholder="Describa los motivos o síntomas de falla..." class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0"></textarea>
                            @error('motivo') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- CARGA INCREMENTAL DE ARCHIVOS (+) -->
                    <div class="border-t border-zinc-300 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider">Archivos y Evidencias</span>
                                <span class="text-[10px] text-zinc-500">Carga múltiple incremental (PDF, Imágenes)</span>
                            </div>

                            <div>
                                <label for="btnSubirArchivo" class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 hover:bg-zinc-900 text-zinc-100 font-mono text-[11px] uppercase cursor-pointer rounded-none border border-zinc-900">
                                    <span>+ AGREGAR ARCHIVO</span>
                                </label>
                                <input type="file" id="btnSubirArchivo" wire:model="tempFile" class="hidden">
                            </div>
                        </div>

                        <!-- Indicador de Carga -->
                        <div wire:loading wire:target="tempFile" class="p-1.5 bg-blue-50 text-blue-800 text-[11px] font-mono border border-blue-200">
                            [CARGANDO ARCHIVO...] Por favor espere.
                        </div>

                        <!-- Archivos ya existentes (al editar) -->
                        @if (count($archivosExistentes) > 0)
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono text-zinc-500 uppercase">Archivos almacenados:</span>
                                @foreach ($archivosExistentes as $ae)
                                    <div class="p-1.5 bg-zinc-100 border border-zinc-300 flex items-center justify-between font-mono text-[11px]">
                                        <span class="truncate max-w-sm">{{ $ae->nombre_archivo }}</span>
                                        <button type="button" wire:click="eliminarArchivoExistente({{ $ae->id }})" class="text-red-700 font-bold px-1.5 hover:bg-red-100">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Archivos nuevos acumulados con "+" -->
                        @if (count($archivosNuevos) > 0)
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono text-blue-700 uppercase font-bold">Nuevos archivos para adjuntar:</span>
                                @foreach ($archivosNuevos as $idx => $an)
                                    <div class="p-1.5 bg-blue-50/70 border border-blue-200 flex items-center justify-between font-mono text-[11px]">
                                        <span class="truncate max-w-sm">{{ $an->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="eliminarArchivoNuevo({{ $idx }})" class="text-red-700 font-bold px-1.5 hover:bg-red-100">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Botones de Acción del Modal -->
                    <div class="border-t border-zinc-300 pt-3 flex justify-end gap-2">
                        <button type="button" wire:click="$set('modalFormulario', false)" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-100 font-semibold uppercase text-xs tracking-wider rounded-none">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold uppercase text-xs tracking-wider rounded-none disabled:opacity-50">
                            <span wire:loading.remove wire:target="guardar">Guardar Registro</span>
                            <span wire:loading wire:target="guardar">Procesando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 4. MODAL: PREVISUALIZADOR INTEGRADO (IMÁGENES Y PDFS)       -->
    <!-- ========================================================== -->
    @if ($modalPreview)
        <div class="fixed inset-0 z-50 bg-zinc-900/80 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-zinc-400 w-full max-w-4xl h-[85vh] rounded-none flex flex-col shadow-none">
                
                <!-- Encabezado Previsualizador -->
                <div class="bg-zinc-900 text-zinc-100 p-2.5 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider shrink-0">
                    <span class="truncate pr-4 font-bold">[VISOR LOCAL] {{ $previewName }}</span>
                    <button type="button" wire:click="cerrarPrevisualizador" class="text-zinc-400 hover:text-white font-bold px-2 py-0.5 border border-zinc-700">
                        ✕ CERRAR
                    </button>
                </div>

                <!-- Visor Base64 -->
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
    <!-- 5. MODAL: CONFIRMACIÓN DE ELIMINACIÓN (LIMPIEZA DE DISCO)  -->
    <!-- ========================================================== -->
    @if ($modalEliminar)
        <div class="fixed inset-0 z-50 bg-zinc-900/60 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-red-500 w-full max-w-md rounded-none shadow-none text-xs space-y-0">
                
                <div class="bg-zinc-900 text-red-400 p-3 font-mono font-bold uppercase text-[11px] tracking-wider border-b border-zinc-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Confirmación de Eliminación Física</span>
                </div>

                <div class="p-4 space-y-3 bg-zinc-50">
                    <p class="text-zinc-800 leading-relaxed">
                        ¿Está seguro de eliminar permanentemente este registro de mantenimiento?
                    </p>
                    <div class="p-2.5 bg-red-50 border border-red-200 text-red-800 text-[11px] font-mono space-y-1">
                        <span class="font-bold block">[ADVERTENCIA DE DISCO]</span>
                        <span>Se destruirán de forma irrevocable todos los archivos locales vinculados a este registro en el almacenamiento físico de la aplicación.</span>
                    </div>
                </div>

                <div class="p-3 bg-white border-t border-zinc-300 flex justify-end gap-2">
                    <button type="button" wire:click="$set('modalEliminar', false)" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-100 font-semibold uppercase text-xs tracking-wider rounded-none">
                        Cancelar
                    </button>
                    <button type="button" wire:click="eliminarMantenimiento" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white font-semibold uppercase text-xs tracking-wider rounded-none">
                        Eliminar Definitivamente
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>