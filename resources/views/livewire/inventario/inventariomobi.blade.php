<div class="p-6" x-data="{ showImgModal: false, imgModalSrc: '' }">
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Inventario de Mobiliario</h1>
            
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Buscador General -->
                <input type="text" wire:model.live="search" placeholder="Buscar mueble..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 w-full md:w-48">

                <!-- NUEVO: Filtro por Lugar -->
                <select wire:model.live="filtroLugar" class="border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm w-full md:w-auto">
                    <option value="">-- Todos los Lugares --</option>
                    <option value="Oficina principal">Oficina principal</option>
                    <option value="Sala de Reuniones">Sala de Reuniones</option>
                    <option value="Oficina de comunicaciones">Oficina de comunicaciones</option>
                    <option value="Almacen">Almacen</option>
                    <option value="Cocina">Cocina</option>
                </select>

                <!-- NUEVO: Filtro por Encargado -->
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
                    Nuevo inmobiliario
                </button>
            </div>
        </div>

        <!-- Notificación Flotante -->
        <div x-data="{ show: false, message: '' }"
            x-on:mueble-guardado.window="show = true; message = $event.detail.msg; setTimeout(() => show = false, 3000)"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            class="fixed top-5 right-5 z-[100]"
            style="display: none;">
            
            <div class="bg-gray-800 text-white px-6 py-3 rounded-lg shadow-2xl border-r-4 border-amber-500 flex items-center gap-3">
                <span x-text="message"></span>
                <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Imagen</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Mueble</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Material / Color</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Lugar</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Estado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($muebles as $mueble)
                    <tr wire:key="mueble-{{ $mueble->id }}" class="hover:bg-amber-50/30 transition">
                        <td class="px-6 py-4">
                            @if($mueble->imagen)
                                <img src="{{ route('mobiliario.foto', ['path' => $mueble->imagen]) }}" class="h-12 w-12 object-cover rounded-lg border border-gray-200 cursor-pointer" @click="imgModalSrc = '{{ route('mobiliario.foto', ['path' => $mueble->imagen]) }}'; showImgModal = true">
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
                            <p class="text-xs text-amber-600 font-semibold italic">{{ $mueble->personal->nombre ?? 'Sin asignar / Área Común' }}</p>
                        </td>
                        <td class="px-6 py-4 text-gray-600 italic">
                            {{ $mueble->material }} <span class="text-gray-300 mx-1">|</span> {{ $mueble->color }}
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            {{ $mueble->lugar }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-lg 
                                {{ $mueble->estado == 'Bueno' ? 'bg-green-100 text-green-700' : 
                                   ($mueble->estado == 'Regular' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $mueble->estado }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center flex justify-center gap-2">
                            @if($mueble->imagen)
                            <button type="button" @click="imgModalSrc = '{{ route('mobiliario.foto', ['path' => $mueble->imagen]) }}'; showImgModal = true" class="text-amber-500 hover:text-amber-700 p-2" title="Ver foto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>
                            @endif

                            <button type="button" wire:click="editar({{ $mueble->id }})" class="text-blue-400 hover:text-blue-600 p-2" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">
                            No se encontraron registros de mobiliario.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Paginación -->
            <div class="p-4 bg-gray-50 border-t">
                {{ $muebles->links() }}
            </div>
        </div>
    </div>

    <!-- MODAL PARA VER IMAGEN (Lightbox) -->
    <div x-show="showImgModal" 
         class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
         style="display: none;"
         @click.self="showImgModal = false">
        <div class="relative max-w-3xl w-full text-center">
            <button @click="showImgModal = false" class="absolute -top-12 right-0 text-white hover:text-amber-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor border-2 rounded-full">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
            <img :src="imgModalSrc" class="mx-auto w-full h-auto max-h-[80vh] object-contain rounded-lg shadow-2xl">
        </div>
    </div>

    <!-- EL MODAL DE FORMULARIO -->
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/10 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 z-10 overflow-y-auto max-h-[90vh]">

            <h3 class="text-lg font-bold text-amber-700 mb-4 border-b pb-2">
                {{ $mueble_id ? 'Editar Mobiliario' : 'Nuevo Mobiliario' }}
            </h3>

            <div class="space-y-4">
                <!-- Sección de Imagen en el Modal -->
                <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-lg p-4 bg-gray-50">
                    @if ($imagen)
                        <img src="{{ $imagen->temporaryUrl() }}" class="h-32 w-32 object-cover rounded-lg shadow-md mb-2">
                    @elseif($imagen_actual)
                        <img src="{{ route('mobiliario.foto', ['path' => $imagen_actual]) }}" class="h-32 w-32 object-cover rounded-lg shadow-md mb-2">
                    @endif

                    <label class="cursor-pointer bg-white px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                        <span>{{ $imagen || $imagen_actual ? 'Cambiar Foto' : 'Subir Foto' }}</span>
                        <input type="file" wire:model="imagen" class="hidden" accept="image/*">
                    </label>
                    <div wire:loading wire:target="imagen" class="text-xs text-amber-600 mt-2 italic font-bold">Cargando archivo...</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 italic">Descripción del Mueble</label>
                    <input type="text" wire:model="nombre" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-amber-500">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 italic">Material</label>
                        <input type="text" wire:model="material" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 italic">Color</label>
                        <input type="text" wire:model="color" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-amber-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 italic">Lugar</label>
                        <select wire:model="lugar" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-amber-500">
                            <option value="Oficina principal">Oficina principal</option>
                            <option value="Sala de Reuniones">Sala de Reuniones</option>
                            <option value="Oficina de comunicaciones">Oficina de comunicaciones</option>
                            <option value="Almacen">Almacen</option>
                            <option value="Cocina">Cocina</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 italic">Estado</label>
                        <select wire:model="estado" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-amber-500">
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="A la basura">A la basura</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 italic">Responsable</label>
                    <select wire:model="personal_id" class="w-full mt-1 border-gray-300 rounded-md shadow-sm focus:ring-amber-500">
                        <option value="">-- Sin asignar --</option>
                        @foreach($personal_list as $persona)
                            <option value="{{ $persona->id }}">{{ $persona->nombre }} {{ $persona->apellido }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" wire:click="closeModal" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg transition">Cancelar</button>
                <button type="button" wire:click="guardar" wire:loading.attr="disabled" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg font-bold shadow-md transition disabled:opacity-50">
                    {{ $mueble_id ? 'Actualizar' : 'Guardar' }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>