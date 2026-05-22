<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Inventario Tecnológico</h1>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <input type="text" wire:model.live="search" placeholder="Buscar equipo..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 w-full md:w-64">
                
                <button wire:click="exportar" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Exportar CSV
                </button>
                
                <button wire:click="crear" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center shrink-0">
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
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Marca / Serie</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Lugar</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Estado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($equipos as $equipo)
                    <tr wire:key="equipo-{{ $equipo->id }}" class="hover:bg-blue-50/30 transition">
                        <!-- Columna 1: Equipo / Responsable -->
                        <td class="px-6 py-4">
                            <div class="text-lg font-bold text-gray-900 leading-tight">{{ $equipo->nombre }}</div>
                            <div class="text-sm text-blue-600 font-medium italic">
                                {{ $equipo->personal ? $equipo->personal->nombre . ' ' . $equipo->personal->apellido : 'Sin asignar' }}
                            </div>
                        </td>

                        <!-- Columna 2: Marca / Serie -->
                        <td class="px-6 py-4">
                            <div class="text-lg font-semibold text-gray-800">{{ $equipo->marca }}</div>
                            <div class="text-sm text-gray-500 italic">S/N: {{ $equipo->serie ?? 'N/A' }}</div>
                        </td>

                        <!-- Columna 3: Lugar -->
                        <td class="px-6 py-4">
                            <div class="flex items-center text-gray-600 font-medium">
                                <span class="mr-1">📍</span> {{ $equipo->lugar }}
                            </div>
                        </td>

                        <!-- Columna 4: Estado -->
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-xs font-bold rounded-full 
                                {{ $equipo->estado == 'En funcionamiento' ? 'bg-green-100 text-green-700' : 
                                   ($equipo->estado == 'Guardado' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                {{ $equipo->estado }}
                            </span>
                        </td>

                        <!-- Columna 5: Acciones -->
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-2">
                                <button wire:click="editar({{ $equipo->id }})" class="p-2 text-blue-500 hover:bg-blue-100 rounded-lg transition" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="eliminar({{ $equipo->id }})" wire:confirm="¿Seguro que deseas eliminar este equipo?" class="p-2 text-red-400 hover:bg-red-100 rounded-lg transition" title="Eliminar">
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
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/30 backdrop-blur-sm" wire:click="closeModal"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden z-10">
            <div class="px-6 py-5">
                <h3 class="text-xl font-bold text-blue-900 border-b pb-2 mb-4">
                    {{ $equipo_id ? 'Editar Equipo' : 'Nuevo Equipo' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Nombre del Equipo</label>
                        <input type="text" wire:model="nombre" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Marca</label>
                            <input type="text" wire:model="marca" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">N° de Serie</label>
                            <input type="text" wire:model="serie" class="w-full mt-1 border-gray-300 rounded-lg focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Lugar</label>
                            <select wire:model="lugar" class="w-full mt-1 border-gray-300 rounded-lg">
                                <option value="Oficina principal">Oficina principal</option>
                                <option value="Sala de Reuniones">Sala de Reuniones</option>
                                <option value="Oficina de comunicaciones">Oficina de comunicaciones</option>
                                <option value="Almacen">Almacen</option>
                                <option value="Cocina">Cocina</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Estado</label>
                            <select wire:model="estado" class="w-full mt-1 border-gray-300 rounded-lg">
                                <option value="En funcionamiento">En funcionamiento</option>
                                <option value="Guardado">Guardado</option>
                                <option value="Malogrado">Malogrado</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Asignar a Responsable:</label>
                        <select wire:model="personal_id" class="w-full mt-1 border-gray-300 rounded-lg">
                            <option value="">-- Sin asignar --</option>
                            @foreach($personales as $persona)
                                <option value="{{ $persona->id }}">{{ $persona->nombre }} {{ $persona->apellido }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                <button wire:click="guardar" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow-md transition">
                    {{ $equipo_id ? 'Actualizar' : 'Registrar' }}
                </button>
                <button wire:click="closeModal" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>