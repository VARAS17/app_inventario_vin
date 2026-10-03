<div class="p-4 space-y-4 max-w-7xl mx-auto flex flex-col min-h-screen">

    {{-- ALERTAS --}}
    @if (session()->has('success'))
        <div class="p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm flex items-center justify-between text-xs font-medium">
            <span>{{ session('success') }}</span>
            <button type="button" class="text-rose-500 hover:text-rose-700 font-bold" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- SECCIÓN SUPERIOR: TABLA DE BAJAS Y DESINCORPORACIONES                     --}}
    {{-- ========================================================================= --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 space-y-3">
        
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <h1 class="text-lg font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Bajas y Disposición Final de Mobiliario
            </h1>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Buscar por mueble, código, motivo..." 
                           class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 outline-none">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <button wire:click="exportar" title="Exportar a CSV" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>CSV</span>
                </button>

                <button wire:click="abrirModalCrear" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Registrar Baja</span>
                </button>
            </div>
        </div>

        {{-- Tabla de Salidas --}}
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Fecha Baja</th>
                        <th class="py-2.5 px-3">Mobiliario</th>
                        <th class="py-2.5 px-3">Área Salida</th>
                        <th class="py-2.5 px-3">Motivo / Causal</th>
                        <th class="py-2.5 px-3">Destino Final</th>
                        <th class="py-2.5 px-3">Receptor</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($salidas as $item)
                        <tr wire:click="toggleSelect({{ $item->id }})" 
                            class="cursor-pointer transition-colors hover:bg-rose-50/50 {{ $selectedSalidaId === $item->id ? 'bg-rose-50 border-l-4 border-rose-600 font-medium text-slate-900' : '' }}">
                            
                            <td class="py-2 px-3 text-slate-500">{{ $item->fecha_salida ? $item->fecha_salida->format('d/m/Y') : '—' }}</td>
                            <td class="py-2 px-3 font-semibold text-slate-800">
                                <div>{{ $item->mobiliario?->nombre }}</div>
                                <div class="text-[10px] text-rose-600 font-mono">{{ $item->mobiliario?->codigo_vin }}</div>
                            </td>
                            <td class="py-2 px-3 text-slate-600">{{ $item->areaOrigen?->nombre ?? 'ALMACEN' }}</td>
                            <td class="py-2 px-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                    {{ $item->tipo_baja }}
                                </span>
                            </td>
                            <td class="py-2 px-3 text-slate-600">{{ $item->destino_final }}</td>
                            <td class="py-2 px-3 text-slate-600">{{ $item->responsable_recepcion ?? 'No especificado' }}</td>
                            
                            <td class="py-2 px-3 text-right space-x-1" wire:click.stop>
                                <button wire:click="abrirModalEditar({{ $item->id }})" title="Editar" class="p-1 hover:bg-slate-200 text-amber-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="confirmarEliminar({{ $item->id }})" title="Eliminar Baja (Revertir)" class="p-1 hover:bg-slate-200 text-rose-600 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                No hay registros de bajas de mobiliario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $salidas->links() }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN INFERIOR: DETALLES DE LA DISPOSICIÓN FINAL (MASTER-DETAIL)        --}}
    {{-- ========================================================================= --}}
    @if ($this->salidaSeleccionada)
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            {{-- Panel Izquierdo: Expediente de Salida --}}
            <div class="border-b lg:border-b-0 lg:border-r border-slate-200 lg:pr-6 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Expediente de Desincorporación</span>
                    <span class="text-xs font-mono font-bold text-rose-700 bg-rose-50 border border-rose-200 px-2 py-0.5 rounded">
                        {{ $this->salidaSeleccionada->mobiliario?->codigo_vin }}
                    </span>
                </div>

                <div class="space-y-1.5 text-xs text-slate-600 bg-slate-50 p-3 rounded-lg border border-slate-200">
                    <p><strong class="text-slate-800">Causal de Baja:</strong> {{ $this->salidaSeleccionada->tipo_baja }}</p>
                    <p><strong class="text-slate-800">Área de Salida:</strong> {{ $this->salidaSeleccionada->areaOrigen?->nombre ?? 'ALMACEN' }}</p>
                    <p><strong class="text-slate-800">Destino Físico Final:</strong> {{ $this->salidaSeleccionada->destino_final }}</p>
                    <p><strong class="text-slate-800">Receptor / Entidad:</strong> {{ $this->salidaSeleccionada->responsable_recepcion ?? 'No especificado' }}</p>
                    <p><strong class="text-slate-800">Fecha de Salida:</strong> {{ $this->salidaSeleccionada->fecha_salida ? $this->salidaSeleccionada->fecha_salida->format('d/m/Y') : '—' }}</p>
                </div>

                {{-- Evidencias / Actas de desecho --}}
                <div class="pt-2">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wide block mb-1.5">Actas e Informes de Baja:</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($this->salidaSeleccionada->archivos as $doc)
                            <button type="button" 
                                    wire:click="abrirPrevisualizador({{ $doc->id }})" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-rose-50 hover:text-rose-700 border border-slate-200 rounded text-xs text-slate-700 transition">
                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="truncate max-w-[140px]">{{ $doc->nombre_archivo }}</span>
                            </button>
                        @empty
                            <span class="text-xs text-slate-400 italic">No se adjuntaron expedientes o actas a esta salida.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Panel Derecho: Ficha del Mueble Retirado --}}
            <div class="space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Datos del Bien Desincorporado</span>
                
                <div class="flex gap-4 items-start">
                    <div class="w-28 h-28 bg-slate-100 rounded-lg border border-slate-200 flex-shrink-0 flex items-center justify-center overflow-hidden">
                        @if ($fotoUrl = $this->getFotoBase64($this->salidaSeleccionada->mobiliario?->foto))
                            <img src="{{ $fotoUrl }}" alt="Foto mueble" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-2 text-slate-400 text-[10px]">Sin imagen</div>
                        @endif
                    </div>

                    <div class="space-y-1 text-xs text-slate-600 flex-1">
                        <h3 class="text-sm font-bold text-slate-900">{{ $this->salidaSeleccionada->mobiliario?->nombre }}</h3>
                        <p><strong class="text-slate-700">Cód. Patrimonial UNT:</strong> {{ $this->salidaSeleccionada->mobiliario?->codigo_inventario_unt ?? 'No asignado' }}</p>
                        <p><strong class="text-slate-700">Proveedor:</strong> {{ $this->salidaSeleccionada->mobiliario?->proveedor ?? '—' }}</p>
                        <p class="text-slate-500 text-[11px] pt-1">
                            {{ $this->salidaSeleccionada->mobiliario?->descripcion ?: 'Sin descripción adicional.' }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 1: FORMULARIO REGISTRAR / EDITAR BAJA                               --}}
    {{-- ========================================================================= --}}
    @if ($modalFormulario)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $salida_id ? 'Editar Registro de Baja' : 'Registrar Salida Definitiva de Mobiliario' }}
                    </h3>
                    {{-- Botón X corregido con cerrarModal --}}
                    <button type="button" wire:click="cerrarModal" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="guardar" class="p-5 space-y-4">
                    
                    {{-- 1. Buscador asistido del Mueble --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mobiliario a dar de Baja *</label>
                        @if ($mobiliarioSeleccionado)
                            <div class="flex items-center justify-between bg-rose-50 border border-rose-200 p-2.5 rounded-lg text-xs">
                                <div>
                                    <div class="font-bold text-rose-900">{{ $mobiliarioSeleccionado->nombre }}</div>
                                    <div class="text-[11px] text-rose-700 font-mono">{{ $mobiliarioSeleccionado->codigo_vin }} ({{ $mobiliarioSeleccionado->estado }})</div>
                                </div>
                                @if (!$salida_id)
                                    <button type="button" wire:click="cambiarMobiliario" class="text-rose-600 hover:text-rose-800 font-bold text-xs">Cambiar</button>
                                @endif
                            </div>
                        @else
                            <div class="relative">
                                <input type="text" wire:model.live.debounce.250ms="searchMobiliario" placeholder="Buscar por código VIN o nombre del mueble..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-lg outline-none focus:ring-2 focus:ring-rose-500">
                                @if ($this->mobiliariosEncontrados->isNotEmpty())
                                    <div class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg z-20 max-h-40 overflow-y-auto divide-y divide-slate-100">
                                        @foreach ($this->mobiliariosEncontrados as $mob)
                                            <div wire:click="seleccionarMobiliario({{ $mob->id }})" class="p-2 text-xs hover:bg-rose-50 cursor-pointer flex justify-between items-center">
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

                    {{-- 2. Área de Origen (Detección Automática) --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Área de Procedencia (Origen Automático)</label>
                        <select wire:model="area_origen_id" disabled class="w-full text-xs px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg text-slate-600 cursor-not-allowed font-medium">
                            @foreach ($areas as $a)
                                <option value="{{ $a->id }}">{{ $a->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- 3. Causal de Baja --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Motivo / Causal de Baja *</label>
                            <select wire:model="tipo_baja" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 outline-none">
                                <option value="Deterioro irreparable">Deterioro irreparable / Rotura</option>
                                <option value="Obsolescencia">Cumplimiento de Vida Útil / Obsolescencia</option>
                                <option value="Chatarreo">Disposición como Chatarra</option>
                                <option value="Donación">Donación a otra dependencia</option>
                                <option value="Pérdida o Hurto">Pérdida o Hurto</option>
                            </select>
                            @error('tipo_baja') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- 4. Fecha de Salida --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Salida *</label>
                            <input type="date" wire:model="fecha_salida" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 outline-none">
                            @error('fecha_salida') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- 5. Destino Final --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Destino Físico Final *</label>
                            <input type="text" wire:model="destino_final" placeholder="Ej: Almacén Central de Bajas, Reciclaje" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 outline-none">
                            @error('destino_final') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- 6. Responsable Receptor --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Receptor / Firmante <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <input type="text" wire:model="responsable_recepcion" placeholder="Ej: Juan Pérez (Encargado Almacén)" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-rose-500 outline-none">
                            @error('responsable_recepcion') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- 7. Evidencias de Baja con Botón (+) --}}
                    <div class="border-t border-slate-100 pt-3 space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700">Actas, Resoluciones o Fotos de Desecho</label>
                            <label class="cursor-pointer px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md border border-rose-200 text-xs font-semibold flex items-center gap-1 transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Adjuntar Archivo (+)</span>
                                <input type="file" wire:model="tempFile" class="hidden">
                            </label>
                        </div>
                        <div wire:loading wire:target="tempFile" class="text-[11px] text-rose-600 font-semibold">Subiendo archivo adjunto...</div>

                        {{-- Archivos Nuevos --}}
                        @if (!empty($archivosNuevos))
                            <div class="space-y-1">
                                @foreach ($archivosNuevos as $idx => $f)
                                    <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded">
                                        <span class="truncate max-w-xs text-slate-700">{{ $f->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="eliminarArchivoNuevo({{ $idx }})" class="text-rose-600 font-bold">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Archivos Existentes --}}
                        @if (!empty($archivosExistentes))
                            <div class="space-y-1 pt-1">
                                <span class="text-[10px] font-bold text-slate-400 uppercase">Documentos registrados:</span>
                                @foreach ($archivosExistentes as $doc)
                                    <div class="flex items-center justify-between text-xs bg-slate-100 border border-slate-200 px-2.5 py-1.5 rounded">
                                        <span class="truncate max-w-xs text-slate-600">{{ $doc->nombre_archivo }}</span>
                                        <button type="button" wire:click="eliminarArchivoExistente({{ $doc->id }})" class="text-rose-600 font-semibold text-xs">Eliminar</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Botones de Acción corregidos --}}
                    <div class="border-t border-slate-200 pt-3 flex justify-end gap-2">
                        <button type="button" wire:click="cerrarModal" class="px-4 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            {{ $salida_id ? 'Actualizar Registro' : 'Confirmar Baja Definitiva' }}
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- MODAL 2: CONFIRMAR ELIMINACIÓN (REVERSIÓN A ESTADO PREVIO) --}}
    @if ($modalEliminar)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-5 space-y-4 border border-slate-200">
                <div class="flex items-center gap-3 text-rose-600">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h4 class="text-sm font-bold text-slate-800">¿Revertir baja de mobiliario?</h4>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Esta acción eliminará el acta de baja física y **restaurará el mueble a su estado activo previo** (volverá a figurar como Disponible o Asignado según su historial).
                </p>
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('modalEliminar', false)" class="px-3 py-1.5 border border-slate-300 text-xs font-semibold text-slate-600 rounded-lg hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="eliminarSalida" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Confirmar Reversión
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL 3: PREVISUALIZADOR BASE64 --}}
    @if ($modalPreview)
        <div class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
                <div class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <span class="text-xs font-bold text-slate-700 truncate max-w-md">{{ $previewName }}</span>
                    <button wire:click="cerrarPrevisualizador" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>
                <div class="p-4 flex-1 overflow-auto flex items-center justify-center bg-slate-100 min-h-[400px]">
                    @if ($previewType === 'image')
                        <img src="{{ $previewUrl }}" class="max-h-[75vh] max-w-full object-contain rounded shadow">
                    @elseif ($previewType === 'pdf')
                        <iframe src="{{ $previewUrl }}" class="w-full h-[75vh] rounded border border-slate-200"></iframe>
                    @else
                        <div class="text-center p-6 text-slate-500 text-xs">Previsualización no disponible para este formato.</div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>