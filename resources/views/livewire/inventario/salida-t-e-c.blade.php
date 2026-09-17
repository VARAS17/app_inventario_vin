<div class="p-6 bg-slate-100 min-h-screen">
    {{-- Encabezado --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Módulo de Salidas de Activos</h1>
            <p class="text-sm text-slate-500">Selecciona un equipo para registrar su salida definitiva o baja de la oficina.</p>
        </div>

        {{-- Buscador reactivo --}}
        <div class="w-full md:w-80">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Buscar por VIN, nombre, serie..."
                class="w-full px-4 py-2 text-sm bg-white border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none shadow-sm"
            />
        </div>
    </div>

    {{-- Alerta de éxito --}}
    @if (session()->has('mensaje'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-lg shadow-sm flex items-center justify-between">
            <span>{{ session('mensaje') }}</span>
            <button type="button" wire:click="$refresh" class="text-emerald-500 hover:text-emerald-700">&times;</button>
        </div>
    @endif

    {{-- Grid de Activos Disponibles para Salida --}}
    @if($activos->isEmpty())
        <div class="bg-white rounded-xl p-12 text-center shadow-sm border border-slate-200">
            <p class="text-slate-500 text-base font-medium">No se encontraron activos disponibles para dar de baja.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach($activos as $item)
                <div class="bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between">
                    {{-- Foto en Base64 --}}
                    <div class="h-44 bg-slate-200 relative overflow-hidden flex items-center justify-center">
                        @if($item->foto)
                            <img 
                                src="{{ $this->getFotoSrc($item->foto) }}" 
                                alt="{{ $item->nombre }}" 
                                class="w-full h-full object-cover"
                            />
                        @else
                            <span class="text-slate-400 text-xs">Sin foto disponible</span>
                        @endif

                        <span class="absolute top-2 right-2 px-2 py-1 text-xs font-semibold rounded-full bg-slate-900/80 text-white backdrop-blur-sm">
                            {{ $item->codigo_vin }}
                        </span>
                    </div>

                    {{-- Datos del Activo --}}
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <h3 class="font-bold text-slate-800 text-base leading-tight">{{ $item->nombre }}</h3>
                                <span class="text-xs px-2 py-0.5 rounded-full font-medium 
                                    @if($item->estado === 'Disponible') bg-emerald-100 text-emerald-700
                                    @elseif($item->estado === 'Asignado') bg-blue-100 text-blue-700
                                    @elseif($item->estado === 'Prestado') bg-amber-100 text-amber-700
                                    @else bg-purple-100 text-purple-700 @endif">
                                    {{ $item->estado }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mb-2">Marca: <span class="font-medium text-slate-700">{{ $item->marca }}</span> | Serie: <span class="font-medium text-slate-700">{{ $item->serie ?? 'S/N' }}</span></p>
                            <p class="text-xs text-slate-400">Ingreso: {{ \Carbon\Carbon::parse($item->fecha_ingreso)->format('d/m/Y') }}</p>
                        </div>

                        {{-- Botón de Acción --}}
                        <div class="mt-4 pt-3 border-t border-slate-100">
                            <button 
                                wire:click="seleccionarActivo({{ $item->id }})" 
                                class="w-full py-2 px-3 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white font-medium text-xs rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Dar Salida / Baja
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- MODAL PARA PROCESAR LA SALIDA --}}
    @if($modalAbierto && $tecnologiaSeleccionada)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-fadeIn">
                
                {{-- Header Modal --}}
                <div class="bg-slate-900 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Registrar Salida de Activo</h2>
                        <p class="text-xs text-slate-300">El activo pasará a estado "De baja"</p>
                    </div>
                    <button wire:click="cerrarModal" class="text-slate-400 hover:text-white text-2xl font-bold">&times;</button>
                </div>

                <form wire:submit.prevent="guardarSalida" class="p-6 space-y-4">
                    
                    {{-- Tarjeta informativa del Activo seleccionado --}}
                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3 flex items-center gap-4">
                        <div class="w-14 h-14 bg-slate-200 rounded-lg overflow-hidden flex-shrink-0">
                            @if($tecnologiaSeleccionada->foto)
                                <img src="{{ $this->getFotoSrc($tecnologiaSeleccionada->foto) }}" class="w-full h-full object-cover">
                            @endif
                        </div>
                        <div class="flex-1">
                            <p class="font-bold text-slate-800 text-sm">{{ $tecnologiaSeleccionada->nombre }} ({{ $tecnologiaSeleccionada->marca }})</p>
                            <p class="text-xs text-slate-500">VIN: <span class="text-slate-700 font-semibold">{{ $tecnologiaSeleccionada->codigo_vin }}</span> | Serie: {{ $tecnologiaSeleccionada->serie ?? 'N/A' }}</p>
                            <p class="text-xs text-amber-600 font-medium">Estado actual: {{ $tecnologiaSeleccionada->estado }}</p>
                        </div>
                    </div>

                    {{-- Formulario --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha de Salida *</label>
                            <input type="date" wire:model="fecha_salida" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                            @error('fecha_salida') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Responsable *</label>
                            <input type="text" wire:model="responsable" placeholder="Ej: Juan Pérez" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                            @error('responsable') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Área / Lugar de Destino *</label>
                            <input type="text" wire:model="area_destino" placeholder="Ej: Sede Lima, Almacén Central, Reciclaje" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                            @error('area_destino') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Motivo de Salida *</label>
                            <input type="text" wire:model="motivo" placeholder="Ej: Obsolescencia técnica, Donación, Daño irreparable" class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:outline-none">
                            @error('motivo') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Carga múltiple de Archivos --}}
                    <div class="pt-2">
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Documentos Adjuntos (Actas, fotos, informes)</label>
                        <div class="border-2 border-dashed border-slate-300 rounded-xl p-4 text-center hover:border-red-400 transition-colors bg-slate-50">
                            <input type="file" wire:model="archivos" multiple id="upload-files" class="hidden">
                            <label for="upload-files" class="cursor-pointer flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-slate-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <span class="text-xs font-semibold text-red-600">Haz clic para subir archivos</span>
                                <span class="text-[10px] text-slate-400">Puedes seleccionar 1 o varios archivos</span>
                            </label>
                        </div>
                        @error('archivos.*') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror

                        {{-- Lista previa de archivos seleccionados --}}
                        @if(!empty($archivos))
                            <div class="mt-3 space-y-1">
                                <p class="text-[11px] font-semibold text-slate-500">Archivos seleccionados:</p>
                                @foreach($archivos as $index => $archivo)
                                    <div class="flex items-center justify-between text-xs bg-slate-100 px-3 py-1.5 rounded-lg border border-slate-200">
                                        <span class="truncate max-w-[85%] text-slate-700 font-medium">{{ $archivo->getClientOriginalName() }}</span>
                                        <button type="button" wire:click="eliminarArchivoTemporal({{ $index }})" class="text-red-500 hover:text-red-700 font-bold">&times;</button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    {{-- Indicador de carga de Livewire --}}
                    <div wire:loading wire:target="guardarSalida, archivos" class="text-center py-2">
                        <span class="text-xs text-red-600 font-semibold animate-pulse">Procesando información y archivos locales...</span>
                    </div>

                    {{-- Acciones --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-slate-100">
                        <button type="button" wire:click="cerrarModal" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg">
                            Cancelar
                        </button>
                        <button type="submit" wire:loading.attr="disabled" class="px-5 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg shadow-sm">
                            Confirmar Salida
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>