<div class="p-6"> <!-- Root element unico -->
    <div class="max-w-7xl mx-auto">
        
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Inventario de Mobiliario</h1>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <input type="text" wire:model.live="search" placeholder="Buscar mueble..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-amber-500 focus:border-amber-500 w-full md:w-64">
                
                <!-- BOTÓN CREAR: Importante que sea type="button" -->
                <button type="button" wire:click="crear" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md flex items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Nuevo
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="bg-amber-100 border border-amber-400 text-amber-700 px-4 py-3 rounded mb-4">
                {{ session('message') }}
            </div>
        @endif

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Mueble</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Material / Color</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Lugar</th> <!-- Nuevo Header -->
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Estado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($muebles as $mueble)
                    <tr wire:key="mueble-{{ $mueble->id }}" class="hover:bg-amber-50/30 transition">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $mueble->nombre }}
                            <p class="text-xs text-amber-600 font-semibold">{{ $mueble->user->nombre ?? 'Área Común' }}</p>
                        </td>
                        <td class="px-6 py-4 text-gray-600 italic">
                            {{ $mueble->material }} <span class="text-gray-300 mx-1">|</span> {{ $mueble->color }}
                        </td>
                        <td class="px-6 py-4 text-gray-600"> <!-- Nueva Celda -->
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
                            <button type="button" wire:click="editar({{ $mueble->id }})" class="text-blue-400 hover:text-blue-600 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button type="button" wire:click="eliminar({{ $mueble->id }})" 
                                wire:confirm="¿Seguro que deseas eliminar?" class="text-red-400 hover:text-red-600 p-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- EL MODAL -->
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center">

        <!-- Fondo -->
        <div class="absolute inset-0 bg-black/10 backdrop-blur-sm"
             wire:click="closeModal"></div>

        <!-- Modal -->
        <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-lg p-6 z-10">

            <h3 class="text-lg font-bold text-amber-700 mb-4 border-b pb-2">
                {{ $mueble_id ? 'Editar Mobiliario' : 'Nuevo Mobiliario' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Descripción</label>
                    <input type="text" wire:model="nombre"
                        class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Material</label>
                        <input type="text" wire:model="material"
                            class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Color</label>
                        <input type="text" wire:model="color"
                            class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                    </div>
                </div>

                <!-- Nuevo campo Lugar en el modal -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Lugar</label>
                    <select wire:model="lugar" class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                        <option value="Oficina principal">Oficina principal</option>
                        <option value="Sala de Reuniones">Sala de Reuniones</option>
                        <option value="Oficina de comunicaciones">Oficina de comunicaciones</option>
                        <option value="Almacen">Almacen</option>
                        <option value="Cocina">Cocina</option>
                    </select>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button"
                    wire:click="guardar"
                    class="bg-amber-600 text-white px-4 py-2 rounded-lg">
                    Guardar
                </button>

                <button type="button"
                    wire:click="closeModal"
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg">
                    Cancelar
                </button>
            </div>

        </div>
    </div>
    @endif
</div>