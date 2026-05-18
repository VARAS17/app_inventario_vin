<div class="p-6">
    <div class="max-w-7xl mx-auto">
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 mr-2 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                Inventario de Útiles y Papelería
            </h1>
            
            <div class="flex items-center gap-4 w-full md:w-auto">
                <input type="text" wire:model.live="search" placeholder="Buscar artículo..." 
                    class="border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500 w-full md:w-64">
                
                <button wire:click="crear" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md flex items-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                    </svg>
                    Nuevo Artículo
                </button>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded mb-4 shadow-sm">
                {{ session('message') }}
            </div>
        @endif

        <!-- TABLA -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Descripción del Artículo</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Stock Actual</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic">Unidad</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase italic text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($utiles as $util)
                    <tr wire:key="util-{{ $util->id }}" class="hover:bg-emerald-50/30 transition border-b border-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">
                            {{ $util->nombre }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-3 py-1 text-sm font-bold rounded-full {{ $util->cantidad <= 5 ? 'bg-red-100 text-red-700' : 'bg-emerald-100 text-emerald-700' }}">
                                {{ $util->cantidad }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 italic text-sm">
                            {{ $util->unidad }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex justify-center gap-3">
                                <button wire:click="editar({{ $util->id }})" class="text-blue-500 hover:text-blue-700 transition" title="Editar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="eliminar({{ $util->id }})" 
                                    wire:confirm="¿Estás seguro de eliminar este artículo del stock?" 
                                    class="text-red-400 hover:text-red-600 transition" title="Eliminar">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-400 italic">No hay artículos en el inventario.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL -->
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        
        <!-- Backdrop con desenfoque separado para no afectar al modal -->
        <div class="fixed inset-0 bg-gray-900/10 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>

        <!-- Caja del Modal nítida -->
        <div class="relative bg-white rounded-2xl shadow-2xl transform transition-all sm:max-w-md sm:w-full overflow-hidden border border-gray-100 z-10">
            <div class="bg-white px-6 py-5">
                <h3 class="text-xl font-bold text-emerald-800 mb-4 border-b pb-2">
                    {{ $util_id ? 'Editar Artículo' : 'Nuevo Artículo de Oficina' }}
                </h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 italic">Nombre / Descripción</label>
                        <input type="text" wire:model="nombre" placeholder="Ej: Papel Bond A4" 
                            class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                        @error('nombre') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Cantidad</label>
                            <input type="number" wire:model="cantidad" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm focus:ring-emerald-500 focus:border-emerald-500">
                            @error('cantidad') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 italic">Unidad de Medida</label>
                            <select wire:model="unidad" class="w-full mt-1 border-gray-300 rounded-lg shadow-sm">
                                <option value="Unidad">Unidad</option>
                                <option value="Cajas">Cajas</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-2">
                <button wire:click="guardar" type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-6 rounded-lg transition shadow-md">
                    {{ $util_id ? 'Actualizar Stock' : 'Agregar Artículo' }}
                </button>
                <button wire:click="closeModal" type="button" class="bg-white border border-gray-300 text-gray-700 font-bold py-2 px-6 rounded-lg hover:bg-gray-50 transition">
                    Cancelar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>