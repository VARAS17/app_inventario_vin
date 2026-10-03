<div class="p-4 space-y-4 max-w-7xl mx-auto flex flex-col min-h-screen">

    {{-- ALERTAS DE MENSAJES Y ERRORES DE AUDITORÍA --}}
    @if (session()->has('message'))
        <div class="p-3 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
            <button type="button" class="text-emerald-500 hover:text-emerald-700" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-3 bg-rose-50 border-l-4 border-rose-500 text-rose-800 rounded-r-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" class="text-rose-500 hover:text-rose-700" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- SECCIÓN SUPERIOR: TABLA GENERAL (PAGINACIÓN 10 + BUSCADOR + FILTROS)     --}}
    {{-- ========================================================================= --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm flex flex-col p-4 space-y-3">
        
        {{-- Barra de herramientas superior --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <h1 class="text-lg font-bold text-slate-800 tracking-tight flex items-center gap-2">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Catálogo de Mobiliario
                </h1>
                
                {{-- Filtro de Estado --}}
                <select wire:model.live="filtroEstado" class="text-xs bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="activos">Activos (Disponibles / Asignados)</option>
                    <option value="baja">Dados de Baja</option>
                    <option value="todos">Todos los registros</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                {{-- Barra Buscadora --}}
                <div class="relative w-full sm:w-64">
                    <input type="text" 
                           wire:model.live.debounce.300ms="search" 
                           placeholder="Buscar por código, nombre, proveedor..." 
                           class="w-full text-xs pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:bg-white outline-none transition">
                    <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                {{-- Botón Exportar CSV --}}
                <button wire:click="exportar" title="Exportar a CSV / Excel" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>CSV</span>
                </button>

                {{-- Botón Nuevo Mueble --}}
                <button wire:click="openCreateModal" class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-lg shadow-sm transition flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Nuevo Mueble</span>
                </button>
            </div>
        </div>

        {{-- Tabla de Datos --}}
        <div class="overflow-x-auto rounded-lg border border-slate-200">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-700 font-semibold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-2.5 px-3">Código Único</th>
                        <th class="py-2.5 px-3">Cód. UNT</th>
                        <th class="py-2.5 px-3">Mobiliario</th>
                        <th class="py-2.5 px-3">Proveedor</th>
                        <th class="py-2.5 px-3">Estado</th>
                        <th class="py-2.5 px-3">Fecha Ingreso</th>
                        <th class="py-2.5 px-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($mobiliarios as $mueble)
                        <tr wire:click="selectItem({{ $mueble->id }})" 
                            class="cursor-pointer transition-colors hover:bg-indigo-50/50 {{ $selectedId === $mueble->id ? 'bg-indigo-50 border-l-4 border-indigo-600 font-medium text-slate-900' : '' }}">
                            
                            <td class="py-2 px-3 font-semibold text-indigo-700">{{ $mueble->codigo_vin }}</td>
                            <td class="py-2 px-3 text-slate-500">{{ $mueble->codigo_inventario_unt ?? '—' }}</td>
                            <td class="py-2 px-3 text-slate-800 font-medium">{{ $mueble->nombre }}</td>
                            <td class="py-2 px-3 text-slate-500">{{ $mueble->proveedor ?? '—' }}</td>
                            <td class="py-2 px-3">
                                @if ($mueble->estado === 'Disponible')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">Disponible</span>
                                @elseif ($mueble->estado === 'Asignado')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800 border border-blue-300">Asignado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 border border-rose-300">De baja</span>
                                @endif
                            </td>
                            <td class="py-2 px-3 text-slate-500">{{ $mueble->fecha_ingreso ? $mueble->fecha_ingreso->format('d/m/Y') : '—' }}</td>
                            
                            <td class="py-2 px-3 text-right space-x-1" wire:click.stop>
                                <button wire:click="openEditModal({{ $mueble->id }})" title="Editar" class="p-1 hover:bg-slate-200 text-slate-600 rounded transition">
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                                <button wire:click="confirmarEliminar({{ $mueble->id }})" title="Eliminar" class="p-1 hover:bg-slate-200 text-slate-600 rounded transition">
                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-6 text-center text-slate-400">
                                No se encontraron muebles registrados con los filtros aplicados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación fija de 10 --}}
        <div>
            {{ $mobiliarios->links() }}
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- SECCIÓN INFERIOR: PANEL DIVIDIDO VERTICALMENTE (DETALLES + 3 MOVIMIENTOS) --}}
    {{-- ========================================================================= --}}
    @if ($this->selectedMobiliario)
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-4 grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
            
            {{-- SECCIÓN IZQUIERDA: IMAGEN + CAMPOS DEL MUEBLE SELECCIONADO --}}
            <div class="border-b lg:border-b-0 lg:border-r border-slate-200 lg:pr-6 space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Ficha del Activo</span>
                    <span class="text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2 py-0.5 rounded">
                        {{ $this->selectedMobiliario->codigo_vin }}
                    </span>
                </div>

                <div class="flex flex-col sm:flex-row gap-4 items-start">
                    {{-- Foto del Mueble (o Placeholder SVG si no tiene) --}}
                    <div class="w-full sm:w-36 h-36 bg-slate-100 rounded-lg border border-slate-200 flex-shrink-0 flex items-center justify-center overflow-hidden relative group">
                        @if ($this->selectedMobiliario->foto && \Illuminate\Support\Facades\Storage::disk('local')->exists($this->selectedMobiliario->foto))
                            @php
                                $mime = \Illuminate\Support\Facades\Storage::disk('local')->mimeType($this->selectedMobiliario->foto);
                                $b64  = base64_encode(\Illuminate\Support\Facades\Storage::disk('local')->get($this->selectedMobiliario->foto));
                            @endphp
                            <img src="data:{{ $mime }};base64,{{ $b64 }}" alt="Foto mueble" class="w-full h-full object-cover">
                        @else
                            <div class="text-center p-2 text-slate-400">
                                <svg class="w-10 h-10 mx-auto text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span class="text-[10px] uppercase font-semibold">Sin imagen</span>
                            </div>
                        @endif
                    </div>

                    {{-- Datos del Mueble --}}
                    <div class="space-y-1.5 text-xs text-slate-600 flex-1">
                        <h2 class="text-base font-bold text-slate-900 leading-snug">{{ $this->selectedMobiliario->nombre }}</h2>
                        
                        <p><strong class="text-slate-700">Cód. Patrimonial UNT:</strong> {{ $this->selectedMobiliario->codigo_inventario_unt ?? 'No asignado' }}</p>
                        <p><strong class="text-slate-700">Proveedor:</strong> {{ $this->selectedMobiliario->proveedor ?? 'No especificado' }}</p>
                        <p><strong class="text-slate-700">Fecha de Alta:</strong> {{ $this->selectedMobiliario->fecha_ingreso ? $this->selectedMobiliario->fecha_ingreso->format('d/m/Y') : '—' }}</p>
                        <p><strong class="text-slate-700">Descripción / Detalles:</strong></p>
                        <p class="text-slate-500 bg-slate-50 p-2 rounded border border-slate-200 text-[11px] leading-relaxed">
                            {{ $this->selectedMobiliario->descripcion ?: 'Sin descripción detallada.' }}
                        </p>
                    </div>
                </div>

                {{-- Evidencias adjuntas al bien (Archivos de alta) --}}
                <div class="pt-2 border-t border-slate-100">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wide block mb-1.5">Documentos / Evidencias de Alta:</span>
                    <div class="flex flex-wrap gap-2">
                        @forelse ($this->selectedMobiliario->archivos as $archivo)
                            <button type="button" 
                                    wire:click="abrirPrevisualizador({{ $archivo->id }})" 
                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 border border-slate-200 rounded text-xs text-slate-700 transition">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                <span class="truncate max-w-[140px]">{{ $archivo->nombre_archivo }}</span>
                            </button>
                        @empty
                            <span class="text-xs text-slate-400 italic">No se adjuntaron documentos de compra o alta.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- SECCIÓN DERECHA: LOS 3 ÚLTIMOS MOVIMIENTOS DEL MUEBLE --}}
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Últimos 3 Movimientos (Trazabilidad)</span>
                    <span class="text-[11px] text-slate-400">Historial ordenado cronológicamente</span>
                </div>

                <div class="space-y-2.5">
                    @forelse ($this->ultimosMovimientos as $mov)
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg flex items-start justify-between gap-3">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $mov['badge'] }}">
                                        {{ $mov['tipo'] }}
                                    </span>
                                    <span class="text-xs font-semibold text-slate-800">{{ $mov['titulo'] }}</span>
                                </div>
                                <p class="text-xs text-slate-600 pl-1">{{ $mov['detalle'] }}</p>
                            </div>
                            <span class="text-[11px] font-medium text-slate-400 whitespace-nowrap">
                                {{ $mov['fecha']->format('d/m/Y') }}
                            </span>
                        </div>
                    @empty
                        <div class="py-8 text-center text-slate-400 text-xs">
                            Sin historial de movimientos para este mueble.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    @else
        {{-- Placeholder cuando no hay ningún mueble seleccionado --}}
        <div class="bg-slate-50 border-2 border-dashed border-slate-200 rounded-xl p-8 text-center text-slate-400">
            <svg class="w-10 h-10 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
            <p class="text-xs font-medium">Haga clic sobre cualquier fila de la tabla para inspeccionar su ficha técnica, imagen y sus últimos 3 movimientos.</p>
        </div>
    @endif


    {{-- ========================================================================= --}}
    {{-- MODAL 1: FORMULARIO ALTA / EDICIÓN                                        --}}
    {{-- ========================================================================= --}}
    @if ($isOpenModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                
                {{-- Cabecera Modal --}}
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800">
                        {{ $isEditMode ? 'Editar Datos del Mobiliario' : 'Registrar Nuevo Mobiliario (Alta)' }}
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
                </div>

                {{-- Formulario --}}
                <form wire:submit.prevent="save" class="p-5 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        {{-- Código Correlativo Autogenerado --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Código Único (VIN)</label>
                            <input type="text" wire:model="codigo_vin" readonly class="w-full text-xs px-3 py-2 bg-slate-100 border border-slate-300 rounded-lg font-mono font-bold text-indigo-700 outline-none">
                            @error('codigo_vin') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Código Patrimonial UNT (Opcional) --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Cód. Patrimonial UNT <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <input type="text" wire:model="codigo_inventario_unt" placeholder="Ej: 7464839201" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('codigo_inventario_unt') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nombre del Mueble --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Nombre del Mobiliario *</label>
                            <input type="text" wire:model="nombre" placeholder="Ej: Silla giratoria ergonómica de oficina" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('nombre') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Proveedor --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Proveedor / Factura <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <input type="text" wire:model="proveedor" placeholder="Ej: Muebles del Norte S.A.C." class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('proveedor') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fecha de Ingreso --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Fecha de Ingreso *</label>
                            <input type="date" wire:model="fecha_ingreso" class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none">
                            @error('fecha_ingreso') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Descripción / Características --}}
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Descripción / Material / Color <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <textarea wire:model="descripcion" rows="2" placeholder="Ej: Estructura metálica, tapiz color negro, regulador de altura..." class="w-full text-xs px-3 py-2 bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none"></textarea>
                            @error('descripcion') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Foto Principal (Opcional) --}}
                        <div class="sm:col-span-2 border-t border-slate-100 pt-3">
                            <label class="block text-xs font-bold text-slate-700 mb-1">Foto Principal del Mobiliario <span class="text-slate-400 font-normal">(Opcional)</span></label>
                            <div class="flex items-center gap-3">
                                <input type="file" wire:model="foto" accept="image/*" class="text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                                <div wire:loading wire:target="foto" class="text-[11px] text-indigo-600 font-semibold">Cargando foto...</div>
                            </div>
                            @error('foto') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Evidencias y Documentos Incrementales (+) --}}
                        <div class="sm:col-span-2 border-t border-slate-100 pt-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-bold text-slate-700">Archivos y Evidencias de Alta (Facturas, Guías, etc.)</label>
                                <label class="cursor-pointer px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-md border border-indigo-200 text-xs font-semibold flex items-center gap-1 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                    <span>Agregar Archivo (+)</span>
                                    <input type="file" wire:model="tempFile" class="hidden">
                                </label>
                            </div>
                            <div wire:loading wire:target="tempFile" class="text-[11px] text-indigo-600 font-semibold">Subiendo archivo adjunto...</div>

                            {{-- Lista de Archivos Nuevos a subir --}}
                            @if (!empty($archivosNuevos))
                                <div class="space-y-1">
                                    @foreach ($archivosNuevos as $index => $file)
                                        <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-200 px-2.5 py-1.5 rounded">
                                            <span class="truncate max-w-sm text-slate-700">{{ $file->getClientOriginalName() }}</span>
                                            <button type="button" wire:click="eliminarArchivoNuevo({{ $index }})" class="text-rose-600 hover:text-rose-800 font-bold">&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Lista de Archivos Existentes (en modo edición) --}}
                            @if (!empty($archivosExistentes))
                                <div class="space-y-1 pt-1">
                                    <span class="text-[10px] font-bold text-slate-400 uppercase">Archivos guardados en sistema:</span>
                                    @foreach ($archivosExistentes as $doc)
                                        <div class="flex items-center justify-between text-xs bg-slate-100 border border-slate-200 px-2.5 py-1.5 rounded">
                                            <span class="truncate max-w-sm text-slate-600">{{ $doc->nombre_archivo }}</span>
                                            <button type="button" wire:click="eliminarArchivoExistente({{ $doc->id }})" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">Eliminar</button>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                    </div>

                    {{-- Botones de Acción --}}
                    <div class="border-t border-slate-200 pt-4 flex justify-end gap-2">
                        <button type="button" wire:click="closeModal" class="px-4 py-1.5 border border-slate-300 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            {{ $isEditMode ? 'Actualizar Mobiliario' : 'Guardar y Registrar' }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 2: CONFIRMACIÓN DE ELIMINACIÓN CON AUDITORÍA BLINDADA               --}}
    {{-- ========================================================================= --}}
    @if ($modalEliminar)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-xl shadow-xl max-w-md w-full p-5 space-y-4 border border-slate-200">
                <div class="flex items-center gap-3 text-rose-600">
                    <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <h4 class="text-sm font-bold text-slate-800">¿Eliminar registro de mobiliario?</h4>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed">
                    Esta acción eliminará permanentemente la ficha del mueble y sus archivos físicos asociados. Solo está permitida si el bien **no tiene transferencias ni salidas registradas**.
                </p>
                <div class="flex justify-end gap-2 pt-2">
                    <button wire:click="$set('modalEliminar', false)" class="px-3 py-1.5 border border-slate-300 text-xs font-semibold text-slate-600 rounded-lg hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="eliminar" class="px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-lg shadow-sm transition">
                        Confirmar Eliminación
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- MODAL 3: PREVISUALIZADOR INTEGRADO (BASE64)                               --}}
    {{-- ========================================================================= --}}
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
                        <div class="text-center p-6 text-slate-500 text-xs">
                            Previsualización no disponible para este formato.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

</div>