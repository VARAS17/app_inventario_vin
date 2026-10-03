<div class="p-4 space-y-4 max-w-7xl mx-auto flex flex-col min-h-screen">

    {{-- ALERTAS --}}
    @if (session()->has('mensaje'))
        <div class="p-3 bg-blue-50 border-l-4 border-blue-500 text-blue-800 rounded-r-lg shadow-sm flex items-center justify-between text-xs font-medium">
            <span>{{ session('mensaje') }}</span>
            <button type="button" class="text-blue-500 hover:text-blue-700 font-bold" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- SECCIÓN SUPERIOR: TABLA DE HISTORIAL DE TRANSFERENCIAS                   --}}
    {{-- ========================================================================= --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-3">
        
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <h1 class="text-lg font-bold text-slate-800 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    Cadena de Custodia y Transferencias
                </h1>

                <select wire:model.live="filtroEstado" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 outline-none">
                    <option value="activas">Vigentes (En uso)</option>
                    <option value="baja">De Muebles en Baja</option>
                    <option value="todas">Todo el Historial</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           wire:model.live.debounce.300ms="searchHistorial" 
                           placeholder="Buscar por código, mueble, custodio..." 
                           class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <button wire:click="exportar" title="Exportar a CSV" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>CSV</span>
                </button>

                <button wire:click="abrirModal" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nueva Transferencia</span>
                </button>
            </div>
        </div>

        {{-- Tabla de Historial --}}
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Fecha</th>
                        <th class="py-2.5 px-3">Mueble / Activo</th>
                        <th class="py-2.5 px-3">Área Origen</th>
                        <th class="py-2.5 px-3">Área Destino</th>
                        <th class="py-2.5 px-3">Custodio Asignado</th>
                        <th class="py-2.5 px-3">Condición</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($historial as $item)
                        @php
                            $esVigente = in_array($item->id, $ultimasAsignacionesIds);
                        @endphp
                        <tr wire:click="seleccionarParaDetalle({{ $item->id }})" 
                            class="cursor-pointer transition-colors hover:bg-blue-50/50 {{ $asignacionSeleccionadaId === $item->id ? 'bg-blue-50 border-l-4 border-blue-600 font-medium text-slate-900' : '' }}">
                            
                            <td class="py-2 px-3 text-slate-500">{{ $item->fecha_traspaso ? $item->fecha_traspaso->format('d/m/Y') : '—' }}</td>
                            <td class="py-2 px-3 font-semibold text-slate-800">
                                <div>{{ $item->mobiliario?->nombre }}</div>
                                <div class="text-[10px] text-blue-600 font-mono">{{ $item->mobiliario?->codigo_vin }}</div>
                            </td>
                            <td class="py-2 px-3 text-slate-500">{{ $item->areaOrigen?->nombre ?? 'ALMACEN' }}</td>
                            <td class="py-2 px-3 text-slate-700 font-medium">{{ $item->areaDestino?->nombre }}</td>
                            <td class="py-2 px-3">
                                @if ($item->personal)
                                    <span class="text-slate-800">{{ $item->personal->nombre }} {{ $item->personal->apellido }}</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Uso Común (Área)</span>
                                @endif
                            </td>
                            <td class="py-2 px-3">
                                @if ($esVigente)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">Vigente</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">Reasignado</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-right space-x-1" wire:click.stop>
                                @if ($esVigente)
                                    <button wire:click="editar({{ $item->id }})" title="Editar" class="p-1 hover:bg-slate-200 text-amber-600 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                    <button wire:click="confirmarEliminar({{ $item->id }})" title="Anular Transferencia" class="p-1 hover:bg-slate-200 text-rose-600 rounded">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                @else
                                    <span class="text-[10px] text-slate-400 italic">Histórico</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                No se encontraron transferencias registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $historial->links() }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN INFERIOR: DETALLES DE LA TRANSFERENCIA + HISTORIAL DEL MUEBLE    --}}
    {{-- ========================================================================= --}}
    @if ($detalleAsignacion)
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            {{-- Panel Izquierdo: Ficha del Traspaso y Evidencias --}}
            <div class="border-b lg:border-b-0 lg:border-r border-slate-200 lg:pr-6 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Detalles de Transferencia</span>
                    <span class="text-xs font-mono font-bold text-blue-700 bg-blue-50 border border-blue-200 px-2 py-0.5 rounded">
                        {{ $detalleAsignacion->mobiliario?->codigo_vin }}
                    </span>
                </div>

                <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <p><strong class="text-slate-800">Mobiliario:</strong> {{ $detalleAsignacion->mobiliario?->nombre }}</p>
                    <p><strong class="text-slate-800">Área de Salida:</strong> {{ $detalleAsignacion->areaOrigen?->nombre ?? 'ALMACEN' }}</p>
                    <p><strong class="text-slate-800">Área Receptora:</strong> {{ $detalleAsignacion->areaDestino?->nombre }}</p>
                    <p><strong class="text-slate-800">Custodio Responsable:</strong> 
                        {{ $detalleAsignacion->personal ? $detalleAsignacion->personal->nombre . ' ' . $detalleAsignacion->personal->apellido : 'Uso Común (Sin custodio individual)' }}
                    </p>
                    <p><strong class="text-slate-800">Fecha Efectiva:</strong> {{ $detalleAsignacion->fecha_traspaso ? $detalleAsignacion->fecha_traspaso->format('d/m/Y') : '—' }}</p>
                </div>

                {{-- Evidencias / Archivos de esta transferencia --}}
                <div class="pt-2">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wide block mb-1.5">Actas y Fotos de Entrega:</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($detalleAsignacion->archivos as $doc)
                            <button type="button" 
                                    wire:click="previsualizarArchivoExistente({{ $doc->id }})" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-blue-50 hover:text-blue-700 border border-slate-200 rounded text-xs text-slate-700 transition">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="truncate max-w-[140px]">{{ $doc->nombre_archivo }}</span>
                            </button>
                        @empty
                            <span class="text-xs text-slate-400 italic">No se adjuntaron actas o fotos a esta transferencia.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Cadena de Custodia completa del Mueble --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Cadena de Custodia del Mueble</span>
                    <span class="text-[11px] text-slate-400">De más reciente a más antiguo</span>
                </div>

                <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                    @forelse ($cadenaCustodios as $hist)
                        <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg flex items-center justify-between text-xs">
                            <div>
                                <span class="font-semibold text-slate-800">{{ $hist->areaDestino?->nombre }}</span>
                                <div class="text-[11px] text-slate-500">
                                    Custodio: {{ $hist->personal ? $hist->personal->nombre . ' ' . $hist->personal->apellido : 'Uso Común' }}
                                </div>
                            </div>
                            <span class="text-[11px] font-mono text-slate-400">
                                {{ $hist->fecha_traspaso ? $hist->fecha_traspaso->format('d/m/Y') : '—' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-400 text-xs">Sin registros de custodia previa.</div>
                    @endforelse
                </div>
            </div>

        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: FORMULARIO NUEVA / EDITAR TRANSFERENCIA                          --}}
    {{-- ========================================================================= --}}
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $isEditing ? 'Editar Transferencia' : 'Registrar Traslado de Mobiliario' }}
                    </h3>
                    <button wire:click="cerrarModal" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="guardar" class="p-5 space-y-4">
                    
                    {{-- 1. Buscador asistido del Mueble --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobiliario a Transferir *</label>
                        @if ($mobiliarioSeleccionado)
                            <div class="flex items-center justify-between bg-blue-50 border border-blue-200 p-2.5 rounded-lg text-xs">
                                <div>
                                    <div class="font-bold text-blue-900">{{ $mobiliarioSeleccionado->nombre }}</div>
                                    <div class="text-[11px] text-blue-700 font-mono">{{ $mobiliarioSeleccionado->codigo_vin }} — Estado: {{ $mobiliarioSeleccionado->estado }}</div>
                                </div>
                                @if (!$isEditing)
                                    <button type="button" wire:click="deseleccionarMobiliario" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Cambiar</button>
                                @endif
                            </div>
                        @else
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.250ms="searchMobiliario" placeholder="Buscar por código VIN o nombre del mueble..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-blue-500">
                                @if ($mobiliariosSugeridos->isNotEmpty())
                                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-20 max-h-40 overflow-y-auto divide-y divide-slate-100">
                                        @foreach ($mobiliariosSugeridos as $mob)
                                            <div wire:click="seleccionarMobiliario({{ $mob->id }})" class="p-2 text-xs hover:bg-blue-50 cursor-pointer flex justify-between items-center">
                                                <span class="font-medium text-slate-800">{{ $mob->nombre }}</span>
                                                <span class="text-slate-500 font-mono text-[10px]">{{ $mob->codigo_vin }} ({{ $mob->estado }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif
                        @error('mobiliario_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- 2. Área de Origen (Autocompletada y protegida) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Área de Procedencia (Origen Automático)</label>
                        <select wire:model="area_origen_id" disabled class="w-full text-xs px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed font-medium">
                            @foreach ($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- 3. Área de Destino --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Área de Destino *</label>
                        <select wire:model.live="area_destino_id" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="">-- Seleccionar Oficina de Destino --</option>
                            @foreach ($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                        @error('area_destino_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- 4. Custodio (OPCIONAL para lugares comunes) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Custodio Responsable <span class="text-slate-400 font-normal">(Opcional: Dejar vacío si es para espacio común)</span>
                        </label>
                        
                        @if ($personalSeleccionado)
                            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 p-2 rounded-lg text-xs">
                                <span class="font-medium text-slate-800">{{ $personalSeleccionado->nombre }} {{ $personalSeleccionado->apellido }}</span>
                                <button type="button" wire:click="deseleccionarPersonal" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Quitar</button>
                            </div>
                        @else
                            @if ($area_destino_id)
                                <select wire:model.live="personal_id" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                                    <option value="">[ Ninguno — Mobiliario de Uso Común / Área ]</option>
                                    @foreach ($personalSugerido as $p)
                                        <option value="{{ $p->id }}">{{ $p->nombre }} {{ $p->apellido }} ({{ $p->cargo ?? 'Personal' }})</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="text-[11px] text-slate-400 italic">Seleccione primero el área de destino para filtrar el personal.</div>
                            @endif
                        @endif
                        @error('personal_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- 5. Fecha de Traspaso --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Traspaso *</label>
                        <input type="date" wire:model="fecha_traspaso" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">
                        @error('fecha_traspaso') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    {{-- 6. Evidencias de Transferencia con Botón (+) --}}
                    <div class="border-t border-slate-100 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">Evidencias / Actas de Entrega</label>
                            <label class="cursor-pointer px-2.5 py-1 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md border border-blue-200 text-xs font-semibold flex items-center gap-1 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Adjuntar Archivo (+)</span>
                                <input type="file" wire:model="nuevosArchivos" multiple class="hidden">
                            </label>
                        </div>
                        <div wire:loading wire:target="nuevosArchivos" class="text-[11px] text-blue-600 font-semibold">Cargando archivos adjuntos...</div>

                        {{-- Archivos Nuevos en cola --}}
                        @if (!empty($archivos))
                            <div class="space-y-1">
                                @foreach ($archivos as $idx => $f)
                                    <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-200 px-2 py-1 rounded">
                                        <span class="truncate max-w-xs text-slate-700">{{ $f->getClientOriginalName() }}</span>
                                        <div class="space-x-2">
                                            <button type="button" wire:click="previsualizarArchivoNuevo({{ $idx }})" class="text-blue-600 font-semibold text-[11px]">Ver</button>
                                            <button type="button" wire:click="eliminarArchivoTemporal({{ $idx }})" class="text-rose-600 font-bold">&times;</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Archivos Existentes (en edición) --}}
                        @if (!empty($archivosExistentes))
                            <div class="space-y-1 pt-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Archivos ya registrados:</span>
                                @foreach ($archivosExistentes as $doc)
                                    <div class="flex items-center justify-between text-xs bg-slate-100 border border-slate-200 px-2 py-1 rounded">
                                        <span class="truncate max-w-xs text-slate-600">{{ $doc->nombre_archivo }}</span>
                                        <button type="button" wire:click="eliminarArchivoExistente({{ $doc->id }})" class="text-rose-600 font-semibold text-[11px]">Eliminar</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Botones --}}
                    <div class="border-t border-slate-200 pt-3 flex justify-end gap-2">
                        <button type="button" wire:click="cerrarModal" class="px-4 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            {{ $isEditing ? 'Actualizar Transferencia' : 'Confirmar Transferencia' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: CONFIRMAR ANULACIÓN --}}
    @if ($mostrarModalEliminar)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-5 space-y-4 border border-slate-200">
                <div class="flex items-center gap-3 text-rose-600">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h4 class="text-sm font-bold text-slate-800">¿Anular esta transferencia?</h4>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Esta acción eliminará el registro de este traspaso y restaurará el custodio y área anterior del mueble (o volverá a quedar Disponible en Almacén si era su única transferencia).
                </p>
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('mostrarModalEliminar', false)" class="px-3 py-1.5 border border-slate-300 text-xs font-semibold text-slate-600 rounded-lg hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="eliminar" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Confirmar Anulación
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: PREVISUALIZADOR BASE64 --}}
    @if ($mostrarModalPreview)
        <div class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 truncate max-w-md">{{ $previewNombre }}</span>
                    <button wire:click="cerrarPreview" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>
                <div class="p-4 flex-1 overflow-auto flex items-center justify-center bg-slate-100 min-h-[400px]">
                    @if ($previewTipo === 'imagen')
                        <img src="{{ $previewSrc }}" class="max-h-[75vh] max-w-full object-contain rounded shadow">
                    @elseif ($previewTipo === 'pdf')
                        <iframe src="{{ $previewSrc }}" class="w-full h-[75vh] rounded border border-slate-200"></iframe>
                    @else
                        <div class="text-center p-6 text-slate-500 text-xs">Previsualización no disponible para este formato.</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>