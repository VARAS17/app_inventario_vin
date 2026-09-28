<div class="p-6">
    <!-- Encabezado -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Catálogo y Stock Actual</h1>
            <p class="text-sm text-gray-500">Existencias físicas de útiles en la oficina</p>
        </div>
        <button 
            wire:click="crear" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 shadow-sm flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nuevo Útil
        </button>
    </div>

    <!-- Mensajes Flash -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-300 text-green-800">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-300 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Buscador -->
    <div class="mb-4">
        <input 
            wire:model.live.debounce.300ms="search" 
            type="text" 
            placeholder="Buscar por nombre, marca o unidad..." 
            class="w-full md:w-1/3 px-4 py-2 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:outline-none"
        />
    </div>

    <!-- Tabla de Existencias -->
    <div class="bg-white shadow-sm overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-gray-600 uppercase font-semibold text-xs">
                <tr>
                    <th class="px-6 py-3 text-left">Útil / Artículo</th>
                    <th class="px-6 py-3 text-left">Marca</th>
                    <th class="px-6 py-3 text-left">Unidad</th>
                    <th class="px-6 py-3 text-center">Stock Actual</th>
                    <th class="px-6 py-3 text-center">Stock Mínimo</th>
                    <th class="px-6 py-3 text-center">Estado</th>
                    <th class="px-6 py-3 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($utilesList as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $item->nombre }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->marca ?? '-' }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $item->unidad }}</td>
                        <td class="px-6 py-4 text-center font-bold text-base">
                            {{ $item->stock_actual }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-500">{{ $item->stock_minimo }}</td>
                        <td class="px-6 py-4 text-center">
                            @if ($item->stock_actual <= 0)
                                <span class="px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                                    Agotado
                                </span>
                            @elseif ($item->stock_actual <= $item->stock_minimo)
                                <span class="px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                                    Por Agotarse
                                </span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                                    Disponible
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button wire:click="editar({{ $item->id }})" class="text-blue-600 hover:text-blue-800 font-medium">Editar</button>
                            <button 
                                wire:click="eliminar({{ $item->id }})" 
                                wire:confirm="¿Seguro que deseas eliminar este útil del catálogo?" 
                                class="text-red-600 hover:text-red-800 font-medium">
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                            No se encontraron útiles registrados en el catálogo.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="p-4 border-t border-gray-200">
            {{ $utilesList->links() }}
        </div>
    </div>

    <!-- Modal Formulario -->
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white shadow-xl max-w-md w-full overflow-hidden border border-gray-300">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $util_id ? 'Editar Útil' : 'Registrar Nuevo Útil' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 font-bold text-xl leading-none">&times;</button>
                </div>

                <form wire:submit.prevent="guardar" class="p-6 space-y-4">
                    <!-- Nombre -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre del Útil *</label>
                        <input type="text" wire:model="nombre" class="w-full px-3 py-2 border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Ej: Hojas Bond A4 75gr">
                        @error('nombre') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <!-- Marca -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Marca</label>
                        <input type="text" wire:model="marca" class="w-full px-3 py-2 border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:outline-none" placeholder="Ej: Atlas, Faber-Castell, etc.">
                        @error('marca') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <!-- Unidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Unidad de Medida *</label>
                        <select wire:model="unidad" class="w-full px-3 py-2 border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            <option value="Unidad">Unidad</option>
                            <option value="Cajas">Cajas</option>
                            <option value="Paquetes">Paquetes</option>
                            <option value="Millares">Millares</option>
                            <option value="Docenas">Docenas</option>
                        </select>
                        @error('unidad') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <!-- Stock Actual y Mínimo -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                {{ $util_id ? 'Stock Actual *' : 'Stock Inicial *' }}
                            </label>
                            <input type="number" wire:model="stock_actual" min="0" class="w-full px-3 py-2 border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            @error('stock_actual') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alerta Stock Mínimo *</label>
                            <input type="number" wire:model="stock_minimo" min="0" class="w-full px-3 py-2 border border-gray-300 focus:ring-1 focus:ring-blue-500 focus:outline-none">
                            @error('stock_minimo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Botones Modal -->
                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" wire:click="closeModal" class="px-4 py-2 text-gray-600 border border-gray-300 hover:bg-gray-100">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700">
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>