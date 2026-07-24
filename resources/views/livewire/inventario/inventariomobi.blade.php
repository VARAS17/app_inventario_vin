<div class="p-6" x-data="{ showImgModal: false, imgModalSrc: '' }">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Inventario de Mobiliario</h1>
            
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Buscador General -->
                <input type="text" wire:model.live="search" placeholder="Buscar mueble..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 w-full md:w-48">

                <!-- Filtro por Lugar -->
                <select wire:model.live="filtroLugar" class="border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm w-full md:w-auto">
                    <option value="">-- Todos los Lugares --</option>
                    @foreach($lugares_list as $l)
                        <option value="{{ $l }}">{{ $l }}</option>
                    @endforeach
                </select>

                <!-- Filtro por Encargado -->
                <select wire:model.live="filtroPersonal" class="border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm w-full md:w-auto">
                    <option value="">-- Todos los Encargados --</option>
                    @foreach($personal_list as $persona)
                        <option value="{{ $persona->id }}">{{ $persona->nombre }} {{ $persona->apellido }}</option>
                    @endforeach
                </select>

                <button wire:click="exportar" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exportar
                </button>
                
                <button wire:click="crear" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo mobiliario
                </button>
            </div>
        </div>

        <!-- Notificación Flotante (Éxito) -->
        <div x-data="{ show: false, message: '' }"
            x-on:mueble-guardado.window="show = true; message = $event.detail.msg; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            class="fixed top-5 right-5 z-[100]"
            style="display: none;">
            <div class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-2xl border-r-4 border-green-500 flex items-center gap-3">
                <span x-text="message"></span>
                <svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Tabla de Contenido -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase w-10">#</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Imagen</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Mueble</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Material / Color</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Lugar</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Estado</th>
                            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($muebles as $index => $mueble)
                        <tr wire:key="mueble-{{ $mueble->id }}" class="hover:bg-amber-50/30 transition">
                            <td class="px-6 py-4 text-sm font-bold text-gray-400">
                                {{ ($muebles->currentPage() - 1) * $muebles->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 py-4">
                                @if($mueble->imagen)
                                    <img src="{{ route('mobiliario.foto', ['path' => $mueble->imagen]) }}" 
                                         class="h-12 w-12 object-cover rounded-lg border border-gray-200 cursor-pointer hover:scale-110 transition-transform" 
                                         @click="imgModalSrc = '{{ route('mobiliario.foto', ['path' => $mueble->imagen]) }}'; showImgModal = true">
                                @else
                                    <div class="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center border border-dashed border-gray-300">
                                        <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $mueble->nombre }}
                                <p class="text-xs text-amber-600 font-semibold italic">
                                    {{ $mueble->personal ? $mueble->personal->nombre . ' ' . $mueble->personal->apellido : 'Sin asignar / Área Común' }}
                                </p>
                            </td>
                            <td class="px-6 py-4 text-gray-600 italic text-sm">
                                {{ $mueble->material }} <span class="text-gray-300 mx-1">|</span> {{ $mueble->color }}
                            </td>
                            <td class="px-6 py-4 text-gray-600 text-sm">
                                {{ $mueble->lugar }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 text-xs font-bold rounded-lg 
                                    {{ $mueble->estado == 'Bueno' ? 'bg-green-100 text-green-700' : 
                                       ($mueble->estado == 'Regular' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    {{ $mueble->estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="editar({{ $mueble->id }})" class="text-blue-400 hover:text-blue-600 p-1 transition" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button onclick="confirm('¿Estás seguro de eliminar este mueble permanentemente?') || event.stopImmediatePropagation()" 
                                            wire:click="eliminar({{ $mueble->id }})" class="text-red-400 hover:text-red-600 p-1 transition" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500 italic">
                                No se encontraron registros de mobiliario con esos filtros.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="p-4 bg-gray-50 border-t">
                {{ $muebles->links() }}
            </div>
        </div>
    </div>

    <!-- LIGHTBOX (Ver imagen ampliada) -->
    <div x-show="showImgModal" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm"
         style="display: none;"
         @keydown.escape.window="showImgModal = false">
        <div class="relative max-w-4xl w-full text-center">
            <button @click="showImgModal = false" class="absolute -top-12 right-0 text-white hover:text-amber-500 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="imgModalSrc" class="mx-auto max-h-[85vh] rounded-lg shadow-2xl border-4 border-white/10">
        </div>
    </div>

    <!-- MODAL FORMULARIO -->
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" wire:click="closeModal"></div>
        
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6 z-10 overflow-y-auto max-h-[95vh]">
            
            <!-- Header Modal -->
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-xl font-bold text-amber-800">
                    {{ $mueble_id ? 'Editar Mobiliario' : 'Registrar Nuevo Mueble' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="space-y-4">
                <!-- Dropzone de Imagen -->
                <div class="relative group">
                    <div class="flex flex-col items-center justify-center border-2 border-dashed {{ $errors->has('imagen') ? 'border-red-400 bg-red-50' : 'border-gray-300 bg-gray-50' }} rounded-xl p-4 transition-all">
                        @if ($imagen)
                            <img src="{{ $imagen->temporaryUrl() }}" class="h-40 w-40 object-cover rounded-lg shadow-md mb-2">
                        @elseif($imagen_actual)
                            <img src="{{ route('mobiliario.foto', ['path' => $imagen_actual]) }}" class="h-40 w-40 object-cover rounded-lg shadow-md mb-2">
                        @else
                            <div class="h-20 w-20 bg-gray-200 rounded-full flex items-center justify-center mb-2">
                                <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif

                        <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-lg shadow-sm text-sm font-bold text-gray-700 hover:bg-amber-50 hover:border-amber-300 transition">
                            <span>{{ $imagen || $imagen_actual ? 'Cambiar Imagen' : 'Seleccionar Imagen' }}</span>
                            <input type="file" wire:model="imagen" class="hidden" accept="image/*">
                        </label>
                        <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-widest">Máximo 2MB - JPG, PNG</p>
                        
                        <!-- Cargando Imagen -->
                        <div wire:loading wire:target="imagen" class="absolute inset-0 bg-white/80 flex items-center justify-center rounded-xl">
                            <div class="flex items-center gap-2 text-amber-600 font-bold">
                                <svg class="animate-spin h-5 w-5" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Procesando...
                            </div>
                        </div>
                    </div>
                    @error('imagen') <span class="text-red-500 text-xs font-bold mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Nombre -->
                <div>
                    <label class="block text-sm font-bold text-gray-700">Nombre del Mueble</label>
                    <input type="text" wire:model="nombre" 
                        class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 @error('nombre') border-red-500 @enderror"
                        placeholder="Ej: Silla Ergonómica">
                    @error('nombre') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Material -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Material</label>
                        <input type="text" wire:model="material" 
                            class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 @error('material') border-red-500 @enderror">
                        @error('material') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Color</label>
                        <input type="text" wire:model="color" 
                            class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 @error('color') border-red-500 @enderror">
                        @error('color') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <!-- Lugar -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Ubicación</label>
                        <select wire:model="lugar" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 @error('lugar') border-red-500 @enderror">
                            <option value="Oficina principal">Oficina principal</option>
                            <option value="Sala de Reuniones">Sala de Reuniones</option>
                            <option value="Oficina de comunicaciones">Oficina de comunicaciones</option>
                            <option value="Almacen">Almacen</option>
                            <option value="Cocina">Cocina</option>
                        </select>
                        @error('lugar') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700">Estado</label>
                        <select wire:model="estado" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 @error('estado') border-red-500 @enderror">
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="A la basura">A la basura</option>
                        </select>
                        @error('estado') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Aviso de "A la basura" -->
                @if($estado === 'A la basura')
                <div class="bg-red-50 border-l-4 border-red-500 p-3 rounded flex gap-2 items-start">
                    <svg class="h-5 w-5 text-red-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                    <p class="text-xs text-red-700 font-bold">Atención: Al guardar con este estado, el mueble se moverá definitivamente al historial de bajas.</p>
                </div>
                @endif

                <!-- Responsable -->
                <div>
                    <label class="block text-sm font-bold text-gray-700">Responsable / Encargado</label>
                    <select wire:model="personal_id" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500">
                        <option value="">-- Sin asignar (Área Común) --</option>
                        @foreach($personal_list as $persona)
                            <option value="{{ $persona->id }}">{{ $persona->nombre }} {{ $persona->apellido }}</option>
                        @endforeach
                    </select>
                    @error('personal_id') <span class="text-red-500 text-xs font-bold">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="mt-8 flex justify-end gap-3 border-t pt-4">
                <button type="button" wire:click="closeModal" 
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2 rounded-xl font-bold transition">
                    Cancelar
                </button>
                <button type="button" wire:click="guardar" wire:loading.attr="disabled" 
                    class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-amber-200 transition flex items-center gap-2">
                    <span wire:loading wire:target="guardar">
                        <svg class="animate-spin h-4 w-4 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </span>
                    {{ $mueble_id ? 'Actualizar Datos' : 'Guardar Mobiliario' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>