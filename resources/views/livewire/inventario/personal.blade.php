<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800">Gestión de Personal</h1>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <input type="text" wire:model.live="search" placeholder="Buscar personal..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 w-full md:w-64">
                
                <button wire:click="crear" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    Nuevo Personal
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 shadow-sm flex justify-between">
                <span>{{ session('message') }}</span>
                <button type="button" class="font-bold" onclick="this.parentElement.remove()">&times;</button>
            </div>
        @endif

        <!-- Tabla -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Foto / Nombre</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Cargo / Grado</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($personales as $persona)
                    <tr wire:key="persona-{{ $persona->id }}" class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <!-- Foto de Perfil circular -->
                                <div class="h-10 w-10 rounded-full overflow-hidden bg-gray-200 border border-gray-100">
                                    @if($persona->foto_perfil)
                                        <img src="{{ asset('storage/' . $persona->foto_perfil) }}" alt="Foto" class="h-full w-full object-cover">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-gray-400 font-bold bg-blue-100 text-blue-600 text-xs">
                                            {{ substr($persona->nombre, 0, 1) }}{{ substr($persona->apellido, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">{{ $persona->nombre }} {{ $persona->apellido }}</div>
                                    <div class="text-xs text-gray-500">ID: #{{ $persona->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">
                            <div class="text-sm font-bold text-blue-700">{{ $persona->cargo }}</div>
                            <div class="text-xs italic text-gray-500">{{ $persona->grado_academico }}</div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-3">
                                <button wire:click="editar({{ $persona->id }})" class="text-blue-500 hover:text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                                <button wire:click="eliminar({{ $persona->id }})" wire:confirm="¿Estás seguro de eliminar este registro? Esto podría afectar a los equipos asignados." class="text-red-400 hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-gray-500 italic">No se encontró personal registrado.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="px-6 py-4 border-t">
                {{ $personales->links() }}
            </div>
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
                        {{ $personal_id ? 'Editar Personal' : 'Nuevo Registro de Personal' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">&times;</button>
                </div>
                
                <div class="space-y-4">
                    <!-- Foto de Perfil (Input) -->
                    <div class="flex flex-col items-center mb-4">
                        <div class="relative">
                            <div class="h-24 w-24 rounded-full overflow-hidden bg-gray-100 border-2 border-blue-100 shadow-inner">
                                @if ($foto_perfil)
                                    <img src="{{ $foto_perfil->temporaryUrl() }}" class="h-full w-full object-cover">
                                @elseif ($foto_actual)
                                    <img src="{{ asset('storage/' . $foto_actual) }}" class="h-full w-full object-cover">
                                @else
                                    <div class="h-full w-full flex items-center justify-center text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <input type="file" wire:model="foto_perfil" id="foto_perfil" class="hidden">
                        <label for="foto_perfil" class="mt-2 text-xs font-bold text-blue-600 cursor-pointer hover:underline">
                            {{ $foto_perfil || $foto_actual ? 'Cambiar Foto' : 'Subir Foto' }}
                        </label>
                        @error('foto_perfil') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Nombre</label>
                            <input type="text" wire:model="nombre" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                            @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Apellido</label>
                            <input type="text" wire:model="apellido" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                            @error('apellido') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Cargo / Puesto</label>
                        <input type="text" wire:model="cargo" placeholder="Ej. Analista de Sistemas" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                        @error('cargo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Grado Académico</label>
                        <select wire:model="grado_academico" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-blue-500">
                            <option value="">Seleccione un grado</option>
                            <option value="Bachiller">Bachiller</option>
                            <option value="Licenciado">Licenciado</option>
                            <option value="Ingeniero">Ingeniero</option>
                            <option value="Magister">Magister</option>
                            <option value="Doctor">Doctor</option>
                            <option value="Técnico">Técnico</option>
                        </select>
                        @error('grado_academico') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                <button wire:click="guardar" wire:loading.attr="disabled" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md disabled:opacity-50">
                    <span wire:loading.remove>Guardar Registro</span>
                    <span wire:loading>Procesando...</span>
                </button>
                <button wire:click="closeModal" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>