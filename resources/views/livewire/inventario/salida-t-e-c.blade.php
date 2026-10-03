<div class="min-h-screen bg-zinc-100 text-zinc-800 text-xs font-sans p-4 space-y-4">

    <!-- ========================================================== -->
    <!-- 1. BARRA SUPERIOR FIJA (MÓDULO, BÚSQUEDA Y ACCIÓN)         -->
    <!-- ========================================================== -->
    <div class="bg-white border border-zinc-300 p-3 flex flex-col md:flex-row items-center justify-between gap-3 shadow-none rounded-none">
        <div class="flex items-center gap-2 border-l-4 border-blue-700 pl-3">
            <div>
                <h1 class="font-bold text-sm text-zinc-900 uppercase tracking-wider">Bajas Definitivas y Salidas</h1>
                <span class="text-[11px] text-zinc-500 font-medium">Control de disposición final, chatarreo, donaciones y obsolescencia</span>
            </div>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <div class="relative w-full md:w-72">
                <input type="text" 
                       wire:model.live.debounce.300ms="search" 
                       placeholder="Buscar por VIN, activo, destino o tipo..." 
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
                <span>+ Registrar Salida</span>
            </button>
            <button type="button" 
                    wire:click="exportar" 
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs uppercase tracking-wider rounded-none transition-colors shrink-0 cursor-pointer shadow-none">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span>Exportar Bajas</span>
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

        <!-- TABLA PRINCIPAL -->
        <div class="{{ $selectedSalidaId ? 'col-span-12 lg:col-span-7 xl:col-span-8' : 'col-span-12' }} transition-all duration-150">
            <div class="bg-white border border-zinc-300 rounded-none">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-zinc-800 text-zinc-200 uppercase font-mono text-[11px] tracking-wider border-b border-zinc-300">
                                <th class="p-2.5 font-semibold">Cód. VIN</th>
                                <th class="p-2.5 font-semibold">Activo / Marca</th>
                                <th class="p-2.5 font-semibold">Origen</th>
                                <th class="p-2.5 font-semibold">Tipo de Baja</th>
                                <th class="p-2.5 font-semibold">Destino Final</th>
                                <th class="p-2.5 font-semibold">F. Salida</th>
                                <th class="p-2.5 font-semibold text-center">Docs</th>
                                <th class="p-2.5 font-semibold text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($salidas as $item)
                                @php
                                    $isSelected = ($selectedSalidaId === $item->id);
                                @endphp
                                <tr wire:key="salida-{{ $item->id }}" 
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
                                        {{ $item->areaOrigen->nombre ?? 'VIN' }}
                                    </td>
                                    <td class="p-2.5 font-medium whitespace-nowrap">
                                        <span class="inline-block px-1.5 py-0.5 bg-rose-50 text-rose-800 border border-rose-200 font-semibold text-[10px] uppercase">
                                            {{ $item->tipo_baja }}
                                        </span>
                                    </td>
                                    <td class="p-2.5 text-zinc-800 truncate max-w-[150px]" title="{{ $item->destino_final }}">
                                        {{ $item->destino_final }}
                                    </td>
                                    <td class="p-2.5 font-mono text-zinc-600 whitespace-nowrap">
                                        {{ $item->fecha_salida ? \Carbon\Carbon::parse($item->fecha_salida)->format('d-m-Y') : '—' }}
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
                                                    title="Editar salida"
                                                    class="p-1 border border-zinc-300 hover:bg-zinc-100 text-zinc-700 rounded-none">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                </svg>
                                            </button>
                                            <button type="button" 
                                                    wire:click.stop="confirmarEliminar({{ $item->id }})" 
                                                    title="Eliminar salida"
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
                                    <td colspan="8" class="p-8 text-center text-zinc-500 font-mono text-xs">
                                        NO SE ENCONTRARON REGISTROS DE SALIDAS O BAJAS
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="p-2.5 border-t border-zinc-300 bg-zinc-50 font-mono text-[11px]">
                    {{ $salidas->links() }}
                </div>
            </div>
        </div>

        <!-- PANEL DERECHO DE INSPECCIÓN DETALLADO -->
        @if ($this->salidaSeleccionada)
            @php
                $sal = $this->salidaSeleccionada;
                $tec = $sal->tecnologia;
                $fotoTec = $this->getFotoBase64($tec->foto ?? null);
            @endphp
            <div class="col-span-12 lg:col-span-5 xl:col-span-4 bg-white border border-zinc-300 rounded-none space-y-0 shadow-none">
                
                <!-- Encabezado del Panel -->
                <div class="bg-zinc-900 text-zinc-100 p-2.5 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider border-b border-zinc-300">
                    <span class="font-bold flex items-center gap-1.5">
                        <span class="w-2 h-2 bg-rose-600 inline-block"></span>
                        Baja #{{ str_pad($sal->id, 5, '0', STR_PAD_LEFT) }}
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
                        <span>Activo en Disposición Final</span>
                        <span class="font-mono text-[10px] px-1.5 py-0.5 border bg-rose-100 text-rose-800 border-rose-300 uppercase">
                            {{ $tec->estado }}
                        </span>
                    </div>

                    <div class="flex gap-3 items-start">
                        <div class="w-28 h-28 bg-zinc-200 border border-zinc-300 shrink-0 flex items-center justify-center overflow-hidden">
                            @if ($fotoTec)
                                <img src="{{ $fotoTec }}" alt="Foto" class="w-full h-full object-cover">
                            @else
                                <span class="text-[10px] text-zinc-500 font-mono text-center p-1 uppercase">Sin Imagen</span>
                            @endif
                        </div>

                        <div class="space-y-1 text-[11px] w-full">
                            <div>
                                <span class="text-zinc-500 uppercase block text-[10px]">Equipo:</span>
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
                                    <span class="text-zinc-500 uppercase block text-[10px]">Ingreso Original:</span>
                                    <span class="font-mono text-zinc-800">{{ $tec->fecha_ingreso ? \Carbon\Carbon::parse($tec->fecha_ingreso)->format('d-m-Y') : '—' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FICHA INFERIOR: Detalles de Salida y Actas -->
                <div class="p-3.5 space-y-3">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-zinc-600 border-b border-zinc-200 pb-1">
                        Sustento de la Baja Física
                    </div>

                    <div class="grid grid-cols-2 gap-2 text-[11px]">
                        <div>
                            <span class="text-zinc-500 uppercase block text-[10px]">Área de Origen:</span>
                            <span class="font-medium text-zinc-900 uppercase">{{ $sal->areaOrigen->nombre ?? 'VIN' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 uppercase block text-[10px]">Tipo de Baja:</span>
                            <span class="font-bold text-rose-800 uppercase">{{ $sal->tipo_baja }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 uppercase block text-[10px]">Fecha de Salida:</span>
                            <span class="font-mono text-zinc-900">{{ $sal->fecha_salida ? \Carbon\Carbon::parse($sal->fecha_salida)->format('d-m-Y') : '—' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500 uppercase block text-[10px]">Receptor / Firmante:</span>
                            <span class="font-medium text-zinc-900">{{ $sal->responsable_recepcion ?? 'No especificado' }}</span>
                        </div>
                        <div class="col-span-2">
                            <span class="text-zinc-500 uppercase block text-[10px]">Destino Físico Final:</span>
                            <p class="p-2 bg-zinc-100 border border-zinc-200 text-zinc-800 text-[11px] font-semibold leading-relaxed">
                                {{ $sal->destino_final }}
                            </p>
                        </div>
                    </div>

                    <!-- Lista de Actas / Resoluciones Adjuntas -->
                    <div class="pt-2 border-t border-zinc-200 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-zinc-700">Actas y Resoluciones</span>
                            <span class="text-[10px] font-mono text-zinc-500">Total: {{ $sal->archivos->count() }}</span>
                        </div>

                        <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                            @forelse ($sal->archivos as $arch)
                                <div class="p-2 bg-zinc-50 border border-zinc-300 flex items-center justify-between hover:bg-zinc-100 transition">
                                    <div class="flex items-center gap-2 truncate pr-2">
                                        <svg class="w-3.5 h-3.5 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <span class="text-[11px] font-mono text-zinc-800 truncate" title="{{ $arch->nombre_archivo }}">
                                            {{ $arch->nombre_archivo }}
                                        </span>
                                    </div>

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
                                    SIN DOCUMENTOS DE BAJA ADJUNTOS
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
                
                <div class="bg-zinc-900 text-zinc-100 p-3 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider">
                    <span class="font-bold">
                        {{ $salida_id ? '[EDITAR] REGISTRO DE BAJA DEFINITIVA' : '[NUEVO] REGISTRO DE BAJA DEFINITIVA' }}
                    </span>
                    <button type="button" wire:click="$set('modalFormulario', false)" class="text-zinc-400 hover:text-white font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="guardar" class="p-4 space-y-4">

                    <!-- BÚSQUEDA VISUAL DE ACTIVOS (DISPONIBLES O DE BAJA EN MANTENIMIENTO) -->
                    <div class="space-y-1.5">
                        <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider">
                            Activo Tecnológico a Dar de Baja <span class="text-red-600">*</span>
                        </label>

                        @if ($tecnologiaSeleccionada)
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
                                        <div class="text-[10px] text-rose-700 font-semibold uppercase">
                                            Estado Actual: {{ $tecnologiaSeleccionada->estado }}
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
                            <div class="relative">
                                <input type="text" 
                                       wire:model.live.debounce.250ms="searchTecnologia" 
                                       placeholder="Buscar activo por VIN, nombre o serie..." 
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
                                                    <span class="text-[10px] font-mono text-zinc-500 uppercase">[{{ $itemTec->estado }}]</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('tecnologia_id') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Campos del Movimiento de Salida -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        
                        <!-- CAMPO ÁREA ORIGEN BLOQUEADO (AUTOCOMPLETADO) -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Área de Origen <span class="text-zinc-400 font-normal lowercase">(autocompletado)</span>
                            </label>
                            <select wire:model="area_origen_id" disabled class="w-full bg-zinc-200 border border-zinc-300 p-2 text-xs rounded-none text-zinc-600 font-medium cursor-not-allowed">
                                <option value="">-- AUTOCOMPLETADO SEGÚN ACTIVO --</option>
                                @foreach ($areas as $a)
                                    <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                                @endforeach
                            </select>
                            @error('area_origen_id') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- TIPO DE BAJA -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Tipo / Motivo de la Baja <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   wire:model="tipo_baja" 
                                   placeholder="Ej: Obsolescencia, Chatarreo RAEE, Donación, Daño irreparable..." 
                                   class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('tipo_baja') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- DESTINO FINAL -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Destino Físico Final <span class="text-red-600">*</span>
                            </label>
                            <input type="text" 
                                   wire:model="destino_final" 
                                   placeholder="Ej: Empresa de Reciclaje RAEE, Almacén Central de Chatarra..." 
                                   class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('destino_final') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- FECHA DE SALIDA -->
                        <div>
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Fecha de Salida Definitiva <span class="text-red-600">*</span>
                            </label>
                            <input type="date" 
                                   wire:model="fecha_salida" 
                                   class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs font-mono rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('fecha_salida') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- RESPONSABLE / RECEPTOR -->
                        <div class="md:col-span-2">
                            <label class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider mb-1">
                                Persona o Entidad Receptora <span class="text-zinc-400 font-normal lowercase">(opcional)</span>
                            </label>
                            <input type="text" 
                                   wire:model="responsable_recepcion" 
                                   placeholder="Nombre de quien recibe la chatarra o donación, DNI o razón social..." 
                                   class="w-full bg-zinc-50 border border-zinc-300 p-2 text-xs rounded-none focus:border-blue-700 focus:bg-white focus:ring-0">
                            @error('responsable_recepcion') <span class="text-[11px] text-red-600 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- CARGA INCREMENTAL DE ACTAS Y RESOLUCIONES (+) -->
                    <div class="border-t border-zinc-300 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="block uppercase font-bold text-[10px] text-zinc-700 tracking-wider">Actas, Resoluciones y Documentos</span>
                                <span class="text-[10px] text-zinc-500">Carga múltiple incremental (PDF, Imágenes)</span>
                            </div>

                            <div>
                                <label for="btnSubirArchivoSalida" class="inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 hover:bg-zinc-900 text-zinc-100 font-mono text-[11px] uppercase cursor-pointer rounded-none border border-zinc-900">
                                    <span>+ AGREGAR DOCUMENTO</span>
                                </label>
                                <input type="file" id="btnSubirArchivoSalida" wire:model="tempFile" class="hidden">
                            </div>
                        </div>

                        <div wire:loading wire:target="tempFile" class="p-1.5 bg-blue-50 text-blue-800 text-[11px] font-mono border border-blue-200">
                            [CARGANDO DOCUMENTO...] Por favor espere.
                        </div>

                        <!-- Archivos ya guardados en BD (al editar) -->
                        @if (count($archivosExistentes) > 0)
                            <div class="space-y-1">
                                <span class="text-[10px] font-mono text-zinc-500 uppercase">Documentos registrados:</span>
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
                                <span class="text-[10px] font-mono text-blue-700 uppercase font-bold">Nuevos documentos para anexar:</span>
                                @foreach ($archivosNuevos as $idx => $an)
                                    <div class="p-1.5 bg-blue-50/70 border border-blue-200 flex items-center justify-between font-mono text-[11px]">
                                        <span class="truncate max-w-sm">{{ $an->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="eliminarArchivoNuevo({{ $idx }})" class="text-red-700 font-bold px-1.5 hover:bg-red-100">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Botones de Acción -->
                    <div class="border-t border-zinc-300 pt-3 flex justify-end gap-2">
                        <button type="button" wire:click="$set('modalFormulario', false)" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-100 font-semibold uppercase text-xs tracking-wider rounded-none">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-4 py-1.5 bg-blue-700 hover:bg-blue-800 text-white font-semibold uppercase text-xs tracking-wider rounded-none disabled:opacity-50">
                            <span wire:loading.remove wire:target="guardar">Confirmar y Dar de Baja</span>
                            <span wire:loading wire:target="guardar">Procesando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    <!-- ========================================================== -->
    <!-- 4. MODAL: PREVISUALIZADOR INTEGRADO (PDFS E IMÁGENES)      -->
    <!-- ========================================================== -->
    @if ($modalPreview)
        <div class="fixed inset-0 z-50 bg-zinc-900/80 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-zinc-400 w-full max-w-4xl h-[85vh] rounded-none flex flex-col shadow-none">
                
                <div class="bg-zinc-900 text-zinc-100 p-2.5 flex items-center justify-between uppercase font-mono text-[11px] tracking-wider shrink-0">
                    <span class="truncate pr-4 font-bold">[VISOR LOCAL] {{ $previewName }}</span>
                    <button type="button" wire:click="cerrarPrevisualizador" class="text-zinc-400 hover:text-white font-bold px-2 py-0.5 border border-zinc-700">
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
    <!-- 5. MODAL: CONFIRMACIÓN DE ELIMINACIÓN                     -->
    <!-- ========================================================== -->
    @if ($modalEliminar)
        <div class="fixed inset-0 z-50 bg-zinc-900/60 backdrop-blur-none flex items-center justify-center p-4">
            <div class="bg-white border border-red-500 w-full max-w-md rounded-none shadow-none text-xs space-y-0">
                
                <div class="bg-zinc-900 text-red-400 p-3 font-mono font-bold uppercase text-[11px] tracking-wider border-b border-zinc-300 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Confirmación de Eliminación</span>
                </div>

                <div class="p-4 space-y-3 bg-zinc-50">
                    <p class="text-zinc-800 leading-relaxed">
                        ¿Está seguro de anular este registro de salida y baja definitiva?
                    </p>
                    <div class="p-2.5 bg-red-50 border border-red-200 text-red-800 text-[11px] font-mono space-y-1">
                        <span class="font-bold block">[ADVERTENCIA]</span>
                        <span>Se eliminarán las actas del disco local y el activo tecnológico volverá a figurar como <strong>"Disponible"</strong> en el almacén.</span>
                    </div>
                </div>

                <div class="p-3 bg-white border-t border-zinc-300 flex justify-end gap-2">
                    <button type="button" wire:click="$set('modalEliminar', false)" class="px-3 py-1.5 bg-white border border-zinc-300 hover:bg-zinc-100 font-semibold uppercase text-xs tracking-wider rounded-none">
                        Cancelar
                    </button>
                    <button type="button" wire:click="eliminarSalida" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white font-semibold uppercase text-xs tracking-wider rounded-none">
                        Eliminar Definitivamente
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>