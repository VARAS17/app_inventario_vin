<div class="p-4 space-y-4 max-w-full mx-auto bg-zinc-100 min-h-screen font-sans text-zinc-800">

    <!-- ========================================================================= -->
    <!-- CABECERA PRINCIPAL: TÍTULO, BUSCADOR Y ACCIÓN                             -->
    <!-- ========================================================================= -->
    <div class="bg-white border border-zinc-300 p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3 shadow-sm rounded-none">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-6 bg-blue-700 inline-block"></span>
                <h1 class="text-xl font-bold uppercase tracking-tight text-zinc-900">Transferencias y Asignaciones</h1>
            </div>
            <p class="text-xs text-zinc-500 mt-0.5 ml-4.5">Control de traspasos de bienes tecnológicos desde VIN hacia dependencias institucionales.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Buscador en historial -->
            <div class="relative w-64">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-zinc-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="searchHistorial" 
                    placeholder="Buscar VIN, equipo o custodio..." 
                    class="w-full pl-8 pr-3 py-1.5 text-xs bg-zinc-50 border border-zinc-300 rounded-none focus:bg-white focus:border-blue-600 focus:outline-none transition-colors"
                />
            </div>

            <button 
                wire:click="abrirModal" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-700 hover:bg-blue-800 text-white font-semibold text-xs uppercase tracking-wider rounded-none shadow-sm transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva Asignación
            </button>
        </div>
    </div>

    <!-- NOTIFICACIÓN FLASH -->
    @if (session()->has('mensaje'))
        <div class="p-3 bg-emerald-50 border-l-4 border-emerald-600 border-y border-r border-emerald-200 text-xs text-emerald-800 font-medium flex items-center justify-between shadow-sm rounded-none">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('mensaje') }}</span>
            </div>
        </div>
    @endif

    <!-- ========================================================================= -->
    <!-- CUERPO PRINCIPAL: DIVISIÓN VERTICAL (IZQUIERDA: TABLA | DERECHA: DETALLE) -->
    <!-- ========================================================================= -->
    <div class="flex flex-col lg:flex-row gap-4 items-start">
        
        <!-- SECCIÓN IZQUIERDA: TABLA DE DATOS (SE EXPANDE AL 100% SI NO HAY DETALLE) -->
        <div class="{{ $detalleAsignacion ? 'w-full lg:w-7/12' : 'w-full' }} transition-all duration-200">
            <div class="bg-white border border-zinc-300 shadow-sm overflow-hidden rounded-none">
                
                <div class="bg-zinc-800 text-white px-4 py-2 text-xs font-bold uppercase tracking-wider flex items-center justify-between border-b border-zinc-700">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                        </svg>
                        Listado de Asignaciones
                    </span>
                    @if($detalleAsignacion)
                        <span class="text-[11px] text-blue-300 font-normal">Fila seleccionada activa</span>
                    @else
                        <span class="text-[11px] text-zinc-400 font-normal">Clic en una fila para ver detalles</span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-zinc-100 text-zinc-700 uppercase tracking-wider font-semibold text-[11px] border-b border-zinc-300">
                            <tr>
                                <th class="px-3 py-2.5 border-r border-zinc-300 w-10 text-center">#</th>
                                <th class="px-3 py-2.5 border-r border-zinc-300">Fecha</th>
                                <th class="px-3 py-2.5 border-r border-zinc-300">Activo (VIN / Nombre)</th>
                                <th class="px-3 py-2.5 border-r border-zinc-300">Destino</th>
                                <th class="px-3 py-2.5 border-r border-zinc-300">Custodio</th>
                                <th class="px-3 py-2.5 border-r border-zinc-300 text-center w-16">Docs</th>
                                <th class="px-3 py-2.5 text-center w-24">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @forelse ($historial as $index => $item)
                                @php 
                                    $isSelected = $asignacionSeleccionadaId === $item->id;
                                @endphp
                                <tr 
                                    wire:click="{{ $isSelected ? "\$set('asignacionSeleccionadaId', null)" : "seleccionarParaDetalle($item->id)" }}" 
                                    class="cursor-pointer transition-colors {{ $isSelected ? 'bg-blue-100/90 border-l-4 border-l-blue-700 font-medium' : 'hover:bg-zinc-50' }}"
                                    title="{{ $isSelected ? 'Clic para deseleccionar y cerrar detalle' : 'Clic para inspeccionar ficha técnica' }}"
                                >
                                    <td class="px-3 py-2 text-center font-mono text-zinc-500 border-r border-zinc-200">
                                        {{ ($historial->currentPage() - 1) * $historial->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-3 py-2 whitespace-nowrap font-mono text-zinc-700 border-r border-zinc-200">
                                        {{ \Carbon\Carbon::parse($item->fecha_traspaso)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-3 py-2 border-r border-zinc-200">
                                        <div class="flex items-center gap-1.5">
                                            <span class="px-1.5 py-0.2 bg-zinc-200 text-zinc-800 font-mono text-[10px] font-bold border border-zinc-300">
                                                {{ $item->tecnologia?->codigo_vin }}
                                            </span>
                                            <span class="text-zinc-900 truncate max-w-[180px]">{{ $item->tecnologia?->nombre }}</span>
                                        </div>
                                    </td>
                                    <td class="px-3 py-2 border-r border-zinc-200 whitespace-nowrap">
                                        <span class="px-1.5 py-0.5 bg-zinc-100 text-zinc-800 border border-zinc-300 font-semibold text-[11px]">{{ $item->area_destino }}</span>
                                    </td>
                                    <td class="px-3 py-2 border-r border-zinc-200">
                                        @if($item->personal)
                                            <div class="text-zinc-900 font-semibold truncate max-w-[160px]">{{ $item->personal->nombre }} {{ $item->personal->apellido }}</div>
                                            <div class="text-[10px] text-zinc-500 truncate max-w-[160px]">{{ $item->personal->cargo }}</div>
                                        @else
                                            <span class="text-zinc-400 italic">Área general</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-center border-r border-zinc-200">
                                        @if($item->archivos->count() > 0)
                                            <span class="px-1.5 py-0.2 bg-zinc-200 text-zinc-800 font-mono font-bold text-[10px] border border-zinc-300">
                                                {{ $item->archivos->count() }}
                                            </span>
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-1.5 text-center" onclick="event.stopPropagation();">
                                        <div class="inline-flex items-center gap-1">
                                            <button 
                                                wire:click="editar({{ $item->id }})" 
                                                class="p-1 bg-zinc-100 hover:bg-amber-100 text-zinc-700 hover:text-amber-800 border border-zinc-300 transition-colors"
                                                title="Editar asignación"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <button 
                                                wire:click="confirmarEliminar({{ $item->id }})" 
                                                class="p-1 bg-zinc-100 hover:bg-rose-100 text-zinc-700 hover:text-rose-800 border border-zinc-300 transition-colors"
                                                title="Eliminar registro"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-zinc-500 font-medium">
                                        No se encontraron transferencias registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($historial->hasPages())
                    <div class="px-4 py-2 border-t border-zinc-300 bg-zinc-50 flex items-center justify-between">
                        <span class="text-xs text-zinc-500">8 registros por página</span>
                        <div>{{ $historial->links() }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- ===================================================================== -->
        <!-- SECCIÓN DERECHA: DETALLE (COLAPSABLE / DESPLEGABLE)                    -->
        <!-- ===================================================================== -->
        @if($detalleAsignacion)
            <div class="w-full lg:w-5/12 space-y-4 animate-fade-in">

                <!-- 1. PARTE SUPERIOR DERECHA: FICHA DEL ACTIVO TECNOLÓGICO -->
                <div class="bg-white border border-zinc-300 shadow-sm rounded-none">
                    <div class="bg-zinc-800 text-white px-4 py-2.5 border-b border-zinc-700 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            Ficha Técnica del Activo
                        </h3>
                        
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-[11px] px-2 py-0.5 bg-blue-600 text-white font-bold">
                                {{ $detalleAsignacion->tecnologia?->codigo_vin }}
                            </span>
                            <!-- BOTÓN PARA CERRAR / LIMPIAR LA VISTA -->
                            <button 
                                wire:click="$set('asignacionSeleccionadaId', null)"
                                class="px-2 py-0.5 bg-zinc-700 hover:bg-rose-700 text-white font-bold text-[10px] uppercase tracking-wider transition-colors"
                                title="Ocultar panel de detalles y expandir tabla"
                            >
                                ✕ Cerrar Detalle
                            </button>
                        </div>
                    </div>

                    <div class="p-4">
                        @php $tec = $detalleAsignacion->tecnologia; @endphp
                        <div class="flex flex-col sm:flex-row gap-4">
                            <!-- Foto Grande del Activo -->
                            <div class="w-full sm:w-36 h-36 bg-zinc-100 border border-zinc-300 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                @if($tec?->foto)
                                    <img src="{{ route('tecnologia.foto', ['path' => $tec->foto]) }}" alt="Foto del Activo" class="w-full h-full object-cover">
                                @else
                                    <div class="flex flex-col items-center justify-center text-zinc-400">
                                        <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-[9px] uppercase font-bold">Sin foto</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Especificaciones Técnicas -->
                            <div class="flex-1 min-w-0">
                                <table class="w-full text-xs border border-zinc-200">
                                    <tbody class="divide-y divide-zinc-200">
                                        <tr class="bg-zinc-50">
                                            <td class="px-2.5 py-1.5 font-bold text-zinc-600 w-24 border-r border-zinc-200">Nombre:</td>
                                            <td class="px-2.5 py-1.5 text-zinc-900 font-semibold">{{ $tec?->nombre }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-2.5 py-1.5 font-bold text-zinc-600 border-r border-zinc-200">Marca:</td>
                                            <td class="px-2.5 py-1.5 text-zinc-800">{{ $tec?->marca }}</td>
                                        </tr>
                                        <tr class="bg-zinc-50">
                                            <td class="px-2.5 py-1.5 font-bold text-zinc-600 border-r border-zinc-200">Serie:</td>
                                            <td class="px-2.5 py-1.5 font-mono text-zinc-800">{{ $tec?->serie ?? 'S/N' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="px-2.5 py-1.5 font-bold text-zinc-600 border-r border-zinc-200">Estado:</td>
                                            <td class="px-2.5 py-1.5">
                                                <span class="px-1.5 py-0.2 font-bold text-[10px] bg-blue-100 text-blue-900 border border-blue-300 uppercase">
                                                    {{ $tec?->estado }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr class="bg-zinc-50">
                                            <td class="px-2.5 py-1.5 font-bold text-zinc-600 border-r border-zinc-200">Ingreso:</td>
                                            <td class="px-2.5 py-1.5 font-mono text-zinc-700">{{ $tec?->fecha_ingreso ? \Carbon\Carbon::parse($tec->fecha_ingreso)->format('d/m/Y') : '—' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. PARTE INFERIOR DERECHA: FICHA DEL CUSTODIO Y ARCHIVOS -->
                <div class="bg-white border border-zinc-300 shadow-sm rounded-none">
                    <div class="bg-zinc-800 text-white px-4 py-2.5 border-b border-zinc-700 flex items-center justify-between">
                        <h3 class="text-xs font-bold uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Custodio y Documentación
                        </h3>
                        <span class="text-xs text-zinc-300 font-mono">Traspaso: {{ \Carbon\Carbon::parse($detalleAsignacion->fecha_traspaso)->format('d/m/Y') }}</span>
                    </div>

                    <div class="p-4 space-y-3">
                        <div class="flex flex-col sm:flex-row gap-3 pb-3 border-b border-zinc-200">
                            <!-- Foto del Personal -->
                            <div class="w-20 h-20 bg-zinc-100 border border-zinc-300 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                @if($detalleAsignacion->personal?->foto_perfil)
                                    <img src="{{ route('personal.foto', ['path' => $detalleAsignacion->personal->foto_perfil]) }}" alt="Foto Personal" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-zinc-200 flex items-center justify-center font-bold text-zinc-600 text-base">
                                        {{ $detalleAsignacion->personal?->initials() ?? 'VIN' }}
                                    </div>
                                @endif
                            </div>

                            <!-- Información Custodio -->
                            <div class="flex-1 min-w-0">
                                @if($detalleAsignacion->personal)
                                    @php $per = $detalleAsignacion->personal; @endphp
                                    <h4 class="text-xs font-bold text-zinc-900 uppercase truncate">{{ $per->grado_academico }} {{ $per->nombre }} {{ $per->apellido }}</h4>
                                    <p class="text-[11px] font-semibold text-blue-700 truncate">{{ $per->cargo }}</p>
                                    <p class="text-[11px] text-zinc-500 font-mono truncate">{{ $per->correo ?? 'Sin correo registrado' }}</p>
                                @else
                                    <h4 class="text-xs font-bold text-zinc-700 uppercase">Sin Custodio Individual</h4>
                                    <p class="text-[11px] text-zinc-500">Bajo responsabilidad del área receptora.</p>
                                @endif

                                <div class="mt-2 pt-1.5 border-t border-zinc-200 flex items-center gap-2 text-xs">
                                    <span class="font-bold text-zinc-600">Área Destino:</span>
                                    <span class="px-2 py-0.5 bg-blue-50 text-blue-900 border border-blue-200 font-bold uppercase text-[10px]">
                                        {{ $detalleAsignacion->area_destino }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Lista de Archivos Adjuntos con Ojo de Previsualización -->
                        <div>
                            <span class="text-[11px] font-bold text-zinc-700 uppercase tracking-wider block mb-1.5">
                                Documentos / Actas de Entrega ({{ $detalleAsignacion->archivos->count() }}):
                            </span>
                            @if($detalleAsignacion->archivos->count() > 0)
                                <div class="grid grid-cols-1 gap-1.5 max-h-36 overflow-y-auto">
                                    @foreach($detalleAsignacion->archivos as $doc)
                                        <div class="p-1.5 bg-zinc-50 border border-zinc-200 flex items-center justify-between text-xs">
                                            <div class="flex items-center gap-1.5 truncate pr-2">
                                                <svg class="w-3.5 h-3.5 text-zinc-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="truncate font-medium text-zinc-800 text-[11px]" title="{{ $doc->nombre_archivo }}">{{ $doc->nombre_archivo }}</span>
                                            </div>
                                            <button 
                                                wire:click="previsualizarArchivoExistente({{ $doc->id }})" 
                                                class="p-1 bg-white hover:bg-zinc-200 border border-zinc-300 text-zinc-700" 
                                                title="Previsualizar archivo"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-[11px] text-zinc-400 italic">No se adjuntaron actas a este traspaso.</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </div>


    <!-- ========================================================================= -->
    <!-- MODAL: CREACIÓN Y EDICIÓN (CRUD COMPLETO)                                 -->
    <!-- ========================================================================= -->
    @if($mostrarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-3 bg-zinc-900/70 backdrop-blur-[1px]">
            <div class="bg-white border-2 border-zinc-800 w-full max-w-4xl shadow-2xl flex flex-col max-h-[95vh] rounded-none">
                
                <div class="bg-zinc-900 text-white px-4 py-3 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-4 bg-blue-500"></span>
                        <h3 class="text-sm font-bold uppercase tracking-wider">
                            {{ $isEditing ? 'Modificar Registro de Asignación' : 'Registrar Nueva Transferencia / Asignación' }}
                        </h3>
                    </div>
                    <button wire:click="cerrarModal" class="text-zinc-400 hover:text-white p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-5 overflow-y-auto space-y-4 text-xs">

                    <!-- PASO 1: SELECCIÓN DEL ACTIVO TECNOLÓGICO -->
                    <div>
                        <label class="block font-bold text-zinc-700 uppercase tracking-wider mb-1.5">
                            1. Activo Tecnológico Disponible <span class="text-rose-600">*</span>
                        </label>

                        @if(!$tecnologiaSeleccionada)
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.250ms="searchTecnologia"
                                    placeholder="Escriba código VIN, serie, marca o nombre del activo disponible..." 
                                    class="w-full px-3 py-2 bg-zinc-50 border border-zinc-300 rounded-none focus:bg-white focus:border-blue-600 focus:outline-none"
                                />

                                @if($tecnologiasSugeridas->isNotEmpty())
                                    <div class="absolute z-20 mt-1 w-full bg-white border border-zinc-400 shadow-xl max-h-56 overflow-y-auto divide-y divide-zinc-200">
                                        @foreach($tecnologiasSugeridas as $item)
                                            <button 
                                                type="button" 
                                                wire:click="seleccionarTecnologia({{ $item->id }})"
                                                class="w-full text-left p-2 flex items-center gap-3 hover:bg-blue-50 transition-colors"
                                            >
                                                <div class="w-12 h-12 bg-zinc-100 border border-zinc-300 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                                    @if($item->foto)
                                                        <img src="{{ route('tecnologia.foto', ['path' => $item->foto]) }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span class="text-[9px] text-zinc-400 uppercase font-bold">Sin foto</span>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-1.5 py-0.2 bg-zinc-800 text-white font-mono text-[10px] font-bold">
                                                            {{ $item->codigo_vin }}
                                                        </span>
                                                        <span class="font-bold text-zinc-600 uppercase">{{ $item->marca }}</span>
                                                    </div>
                                                    <div class="font-bold text-zinc-900 truncate mt-0.5">{{ $item->nombre }}</div>
                                                    <div class="text-zinc-500 font-mono text-[10px]">Serie: {{ $item->serie ?? 'S/N' }}</div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-3 bg-zinc-50 border border-blue-400 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-14 h-14 bg-white border border-zinc-300 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                        @if($tecnologiaSeleccionada->foto)
                                            <img src="{{ route('tecnologia.foto', ['path' => $tecnologiaSeleccionada->foto]) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-[9px] text-zinc-400 uppercase font-bold">Sin foto</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-1.5 py-0.5 bg-blue-700 text-white font-mono font-bold text-[10px]">
                                                {{ $tecnologiaSeleccionada->codigo_vin }}
                                            </span>
                                            <span class="font-bold text-zinc-700 uppercase">{{ $tecnologiaSeleccionada->marca }}</span>
                                        </div>
                                        <div class="font-bold text-zinc-900 text-sm mt-0.5">{{ $tecnologiaSeleccionada->nombre }}</div>
                                        <div class="text-zinc-500 font-mono text-[10px]">Serie: {{ $tecnologiaSeleccionada->serie ?? 'No registrada' }}</div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="deseleccionarTecnologia"
                                    class="px-2.5 py-1.5 text-xs font-semibold text-rose-700 bg-white border border-rose-300 hover:bg-rose-50"
                                >
                                    Cambiar Activo
                                </button>
                            </div>
                        @endif
                        @error('tecnologia_id') <span class="text-rose-600 font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- PASO 2: TRAYECTORIA Y RESPONSABLE -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 pt-3 border-t border-zinc-200">
                        <div>
                            <label class="block font-bold text-zinc-700 uppercase tracking-wider mb-1">Área Origen</label>
                            <input type="text" value="VIN" disabled class="w-full px-3 py-2 bg-zinc-200 border border-zinc-300 text-zinc-700 font-bold rounded-none" />
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 uppercase tracking-wider mb-1">
                                Área Destino <span class="text-rose-600">*</span>
                            </label>
                            <select wire:model="area_destino" class="w-full px-3 py-2 bg-zinc-50 border border-zinc-300 rounded-none focus:bg-white focus:border-blue-600 focus:outline-none">
                                <option value="">Seleccione el destino...</option>
                                @foreach($areasDestino as $area)
                                    <option value="{{ $area }}">{{ $area }}</option>
                                @endforeach
                            </select>
                            @error('area_destino') <span class="text-rose-600 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block font-bold text-zinc-700 uppercase tracking-wider mb-1">
                                Fecha de Traspaso <span class="text-rose-600">*</span>
                            </label>
                            <input type="date" wire:model="fecha_traspaso" class="w-full px-3 py-2 bg-zinc-50 border border-zinc-300 rounded-none focus:bg-white focus:border-blue-600 focus:outline-none font-mono" />
                            @error('fecha_traspaso') <span class="text-rose-600 font-semibold block mt-0.5">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- SELECCIÓN DEL PERSONAL CON FOTO -->
                    <div class="pt-3 border-t border-zinc-200">
                        <label class="block font-bold text-zinc-700 uppercase tracking-wider mb-1">
                            Custodio Receptor (Personal) <span class="text-zinc-400 font-normal">(Opcional)</span>
                        </label>

                        @if(!$personalSeleccionado)
                            <div class="relative">
                                <input 
                                    type="text" 
                                    wire:model.live.debounce.250ms="searchPersonal"
                                    placeholder="Buscar personal por nombre, apellido o cargo..." 
                                    class="w-full px-3 py-2 bg-zinc-50 border border-zinc-300 rounded-none focus:bg-white focus:border-blue-600 focus:outline-none"
                                />

                                @if($personalSugerido->isNotEmpty())
                                    <div class="absolute z-20 mt-1 w-full bg-white border border-zinc-400 shadow-xl max-h-56 overflow-y-auto divide-y divide-zinc-200">
                                        @foreach($personalSugerido as $p)
                                            <button 
                                                type="button" 
                                                wire:click="seleccionarPersonal({{ $p->id }})"
                                                class="w-full text-left p-2 flex items-center gap-3 hover:bg-blue-50 transition-colors"
                                            >
                                                <div class="w-10 h-10 bg-zinc-200 border border-zinc-300 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                                    @if($p->foto_perfil)
                                                        <img src="{{ route('personal.foto', ['path' => $p->foto_perfil]) }}" class="w-full h-full object-cover">
                                                    @else
                                                        <span class="font-bold text-zinc-600 text-xs">{{ $p->initials() }}</span>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-bold text-zinc-900 truncate">{{ $p->grado_academico }} {{ $p->nombre }} {{ $p->apellido }}</div>
                                                    <div class="text-zinc-500 text-[11px]">{{ $p->cargo }}</div>
                                                </div>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="p-2.5 bg-zinc-50 border border-zinc-300 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-zinc-200 border border-zinc-300 flex-shrink-0 flex items-center justify-center overflow-hidden">
                                        @if($personalSeleccionado->foto_perfil)
                                            <img src="{{ route('personal.foto', ['path' => $personalSeleccionado->foto_perfil]) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="font-bold text-zinc-600 text-sm">{{ $personalSeleccionado->initials() }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-zinc-900">{{ $personalSeleccionado->grado_academico }} {{ $personalSeleccionado->nombre }} {{ $personalSeleccionado->apellido }}</div>
                                        <div class="text-zinc-500 text-[11px]">{{ $personalSeleccionado->cargo }} — <span class="font-mono">{{ $personalSeleccionado->correo }}</span></div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    wire:click="deseleccionarPersonal"
                                    class="px-2 py-1 text-xs font-semibold text-rose-700 bg-white border border-rose-300 hover:bg-rose-50"
                                >
                                    Quitar Custodio
                                </button>
                            </div>
                        @endif
                    </div>

                    <!-- PASO 3: DOCUMENTACIÓN CON BOTÓN "+" -->
                    <div class="pt-3 border-t border-zinc-200">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <span class="font-bold text-zinc-700 uppercase tracking-wider block">Actas y Documentos de Respaldo</span>
                                <span class="text-[11px] text-zinc-400">Puedes anexar múltiples archivos (PDF o Imágenes).</span>
                            </div>

                            <div>
                                <input 
                                    type="file" 
                                    wire:model="nuevosArchivos" 
                                    multiple 
                                    id="btn-anexar-archivos" 
                                    class="hidden"
                                />
                                <label 
                                    for="btn-anexar-archivos" 
                                    class="cursor-pointer inline-flex items-center gap-1 px-3 py-1.5 bg-zinc-800 hover:bg-zinc-900 text-white font-bold text-xs uppercase tracking-wider"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Agregar Archivos (+)
                                </label>
                            </div>
                        </div>

                        <div wire:loading wire:target="nuevosArchivos" class="p-2 bg-blue-50 border border-blue-200 text-blue-800 text-xs font-semibold flex items-center gap-2 mb-2">
                            <svg class="animate-spin w-4 h-4 text-blue-700" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                            </svg>
                            Cargando archivos al almacenamiento local...
                        </div>

                        @if(!empty($archivosExistentes) && count($archivosExistentes) > 0)
                            <div class="space-y-1 mb-3">
                                <span class="text-[11px] font-bold text-zinc-500 uppercase">Archivos ya registrados:</span>
                                @foreach($archivosExistentes as $guardado)
                                    <div class="flex items-center justify-between p-1.5 bg-zinc-100 border border-zinc-300">
                                        <div class="flex items-center gap-2 truncate pr-2">
                                            <span class="px-1 bg-zinc-300 text-[10px] font-mono">BD</span>
                                            <span class="truncate font-medium text-zinc-800">{{ $guardado->nombre_archivo }}</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button 
                                                type="button" 
                                                wire:click="previsualizarArchivoExistente({{ $guardado->id }})" 
                                                class="p-1 bg-white hover:bg-zinc-200 border border-zinc-300 text-zinc-700" 
                                                title="Previsualizar"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="eliminarArchivoExistente({{ $guardado->id }})" 
                                                class="p-1 bg-white hover:bg-rose-100 text-rose-700 border border-zinc-300" 
                                                title="Eliminar"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($archivos))
                            <div class="space-y-1">
                                <span class="text-[11px] font-bold text-zinc-500 uppercase">Archivos por guardar:</span>
                                @foreach($archivos as $index => $arch)
                                    <div class="flex items-center justify-between p-1.5 bg-blue-50/60 border border-blue-200">
                                        <div class="flex items-center gap-2 truncate pr-2">
                                            <span class="px-1 bg-blue-200 text-blue-900 text-[10px] font-bold font-mono">NUEVO</span>
                                            <span class="truncate font-medium text-zinc-800">{{ $arch->getClientOriginalName() }}</span>
                                            <span class="text-[10px] text-zinc-400 font-mono">({{ round($arch->getSize() / 1024, 1) }} KB)</span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <button 
                                                type="button" 
                                                wire:click="previsualizarArchivoNuevo({{ $index }})" 
                                                class="p-1 bg-white hover:bg-zinc-200 border border-zinc-300 text-zinc-700" 
                                                title="Previsualizar"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                            </button>
                                            <button 
                                                type="button" 
                                                wire:click="eliminarArchivoTemporal({{ $index }})" 
                                                class="p-1 bg-white hover:bg-rose-100 text-rose-700 border border-zinc-300" 
                                                title="Quitar"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(empty($archivos) && empty($archivosExistentes))
                            <div class="p-3 bg-zinc-50 border border-dashed border-zinc-300 text-center text-zinc-400">
                                Ningún documento adjuntado aún. Haz clic en <strong>Agregar Archivos (+)</strong> para anexar.
                            </div>
                        @endif
                    </div>

                </div>

                <div class="px-5 py-3 bg-zinc-100 border-t border-zinc-300 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        wire:click="cerrarModal" 
                        class="px-4 py-2 text-xs font-bold uppercase tracking-wider text-zinc-700 bg-white border border-zinc-300 hover:bg-zinc-200"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        wire:click="guardar" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 text-xs font-bold uppercase tracking-wider text-white bg-blue-700 hover:bg-blue-800 disabled:opacity-50"
                    >
                        {{ $isEditing ? 'Guardar Cambios' : 'Confirmar y Guardar' }}
                    </button>
                </div>

            </div>
        </div>
    @endif


    <!-- ========================================================================= -->
    <!-- MODAL: PREVISUALIZADOR DE ARCHIVOS                                        -->
    <!-- ========================================================================= -->
    @if($mostrarModalPreview)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-950/80">
            <div class="bg-white border-2 border-zinc-800 w-full max-w-3xl h-[85vh] flex flex-col rounded-none shadow-2xl">
                <div class="bg-zinc-900 text-white px-4 py-2.5 flex items-center justify-between text-xs">
                    <span class="font-bold font-mono truncate max-w-lg">{{ $previewNombre }}</span>
                    <button wire:click="cerrarPreview" class="text-zinc-400 hover:text-white font-bold text-sm">✕</button>
                </div>
                <div class="p-2 flex-1 bg-zinc-200 overflow-hidden flex items-center justify-center">
                    @if($previewTipo === 'imagen')
                        <img src="{{ $previewSrc }}" alt="Preview" class="max-w-full max-h-full object-contain border border-zinc-400 bg-white">
                    @elseif($previewTipo === 'pdf')
                        <iframe src="{{ $previewSrc }}" class="w-full h-full border-none"></iframe>
                    @else
                        <div class="text-center text-zinc-500 font-medium">
                            Este tipo de archivo no admite previsualización directa en el navegador.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif


    <!-- ========================================================================= -->
    <!-- MODAL: CONFIRMACIÓN DE ELIMINACIÓN                                         -->
    <!-- ========================================================================= -->
    @if($mostrarModalEliminar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-zinc-900/70">
            <div class="bg-white border-2 border-zinc-900 w-full max-w-md p-5 shadow-2xl rounded-none">
                <div class="flex items-center gap-3 text-rose-700 mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-900">Confirmar Eliminación</h3>
                </div>
                <p class="text-xs text-zinc-600 mb-4">
                    ¿Está seguro de que desea eliminar este registro de asignación? El activo volverá a estar <strong>Disponible</strong> en almacén y se borrarán los archivos físicos del equipo.
                </p>
                <div class="flex items-center justify-end gap-2 text-xs">
                    <button 
                        wire:click="$set('mostrarModalEliminar', false)" 
                        class="px-3 py-1.5 bg-zinc-200 hover:bg-zinc-300 font-bold uppercase tracking-wider text-zinc-700"
                    >
                        Cancelar
                    </button>
                    <button 
                        wire:click="eliminar" 
                        class="px-4 py-1.5 bg-rose-700 hover:bg-rose-800 font-bold uppercase tracking-wider text-white"
                    >
                        Eliminar Registro
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>