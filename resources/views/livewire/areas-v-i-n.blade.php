<div class="p-6 bg-gray-50 min-h-screen">
    <!-- Encabezado y Botón Crear -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Áreas</h1>
            <p class="text-sm text-gray-500">Mantenimiento y catálogo de oficinas/áreas.</p>
        </div>
        <button 
            wire:click="create" 
            class="px-4 py-2 bg-blue-600 border border-transparent text-sm font-medium text-white hover:bg-blue-700 transition-colors"
        >
            + Nueva Área
        </button>
    </div>

    <!-- Mensaje de Notificación -->
    @if (session()->has('message'))
        <div class="mb-4 p-3 border-l-4 border-green-500 bg-green-50 text-green-700 text-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- Barra de Búsqueda -->
    <div class="mb-4">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar por nombre..." 
            class="w-full md:w-80 px-3 py-2 border border-gray-300 bg-white text-sm focus:outline-none focus:border-blue-500"
        />
    </div>

    <!-- Tabla (Sin bordes redondeados) -->
    <div class="border border-gray-300 bg-white overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="border-b border-gray-300 bg-gray-100 text-gray-700 font-semibold uppercase text-xs">
                    <th class="py-3 px-4 border-r border-gray-300 w-16 text-center">#</th>
                    <th class="py-3 px-4 border-r border-gray-300">Nombre del Área</th>
                    <th class="py-3 px-4 text-center w-40">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($areas as $area)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3 px-4 border-r border-gray-200 text-center text-gray-500 font-mono">
                            {{ $area->id }}
                        </td>
                        <td class="py-3 px-4 border-r border-gray-200 font-medium text-gray-900">
                            {{ $area->nombre }}
                        </td>
                        <td class="py-3 px-4 text-center space-x-2">
                            <button 
                                wire:click="edit({{ $area->id }})" 
                                class="text-blue-600 hover:text-blue-900 font-medium text-xs uppercase"
                            >
                                Editar
                            </button>
                            <span class="text-gray-300">|</span>
                            <button 
                                wire:click="delete({{ $area->id }})" 
                                wire:confirm="¿Deseas eliminar esta área?" 
                                class="text-red-600 hover:text-red-900 font-medium text-xs uppercase"
                            >
                                Eliminar
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-6 text-center text-gray-500">
                            No hay áreas registradas.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $areas->links() }}
    </div>

    <!-- Modal (Crear / Editar) -->
    @if($isModalOpen)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
            <div class="bg-white border border-gray-300 shadow-xl w-full max-w-md p-6">
                <!-- Título del Modal -->
                <div class="border-b border-gray-200 pb-3 mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $area_id ? 'Editar Área' : 'Nueva Área' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 text-xl font-bold leading-none">
                        &times;
                    </button>
                </div>

                <!-- Formulario -->
                <form wire:submit.prevent="store">
                    <div class="mb-4">
                        <label for="nombre" class="block text-sm font-medium text-gray-700 mb-1">
                            Nombre del Área <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nombre" 
                            wire:model="nombre" 
                            placeholder="Ej. Mesa de Partes, TI, Administración..." 
                            class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-blue-500"
                        />
                        @error('nombre') 
                            <span class="text-xs text-red-600 mt-1 block">{{ $message }}</span> 
                        @enderror
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end gap-2 border-t border-gray-200 pt-4">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 transition-colors"
                        >
                            {{ $area_id ? 'Guardar Cambios' : 'Registrar' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>