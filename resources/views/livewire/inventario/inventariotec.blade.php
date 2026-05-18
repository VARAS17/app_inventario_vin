<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Inventario Tecnológico</h1>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <input type="text" wire:model.live="search" placeholder="Buscar..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 w-full md:w-64">
                
                <button wire:click="crear" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo Equipo
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4 shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Equipo / Responsable</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Marca / Serie / Lugar</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($equipos as $equipo)
                    <tr wire:key="equipo-{{ $equipo->id }}" class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900">{{ $equipo->nombre }}</div>
                            <div class="text-xs text-blue-600 font-semibold">{{ $equipo->user->nombre ?? 'Sin asignar' }}</div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <div class="text-sm font-bold">{{ $equipo->marca }}</div>
                            <div class="text-xs italic text-gray-400">S/N: {{ $equipo->serie ?? 'N/A' }}</div>
                            <div class="text-xs font-medium text-blue-500 mt-1">📍 {{ $equipo->lugar }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-3">
                                <button wire:click="editar({{ $equipo->id }})" class="text-blue-500 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="eliminar({{ $equipo->id }})" wire:confirm="¿Seguro?" class="text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL -->
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-0">
        
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" wire:click="closeModal"></div>

        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-lg sm:w-full overflow-hidden border border-gray-100 z-10">
            <div class="bg-white px-6 py-5">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="text-xl font-bold text-blue-900">
                        {{ $equipo_id ? 'Editar Equipo' : 'Nuevo Equipo' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Nombre del Equipo</label>
                        <input type="text" wire:model="nombre" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Marca</label>
                            <input type="text" wire:model="marca" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                            @error('marca') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">N° de Serie</label>
                            <input type="text" wire:model="serie" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- CAMPO LUGAR COMO SELECT PARA EVITAR ERRORES DE ENUM -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Lugar / Ubicación</label>
                        <select wire:model="lugar" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                            <option value="Oficina principal">Oficina principal</option>
                            <option value="Sala de Reuniones">Sala de Reuniones</option>
                            <option value="Oficina de comunicaciones">Oficina de comunicaciones</option>
                            <option value="Almacen">Almacen</option>
                            <option value="Cocina">Cocina</option>
                        </select>
                        @error('lugar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Estado</label>
                        <select wire:model="estado" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                            <option value="En funcionamiento">En funcionamiento</option>
                            <option value="En reparación">En reparación</option>
                            <option value="Obsoleto">Obsoleto</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Asignar a:</label>
                        <select wire:model="user_id" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                            <option value="">-- Sin asignar --</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->nombre }} {{ $user->apellido }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                <button wire:click="guardar" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md">
                    {{ $equipo_id ? 'Guardar Cambios' : 'Registrar Equipo' }}
                </button>
                <button wire:click="closeModal" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>