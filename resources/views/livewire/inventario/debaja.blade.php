<div class="p-6">
    {{-- Encabezado --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dados de Baja</h1>
            <p class="text-sm text-gray-500">Historial de artículos malogrados o fuera de servicio</p>
        </div>
        
        <div class="relative">
            <input type="text" wire:model.live="search" 
                   class="w-full md:w-80 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm" 
                   placeholder="Buscar por nombre o tipo...">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Artículo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Detalles de Origen</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Fecha Baja</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bajas as $baja)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    @if($baja->imagen)
                                        {{-- CORRECCIÓN AQUÍ: Usando la ruta 'personal.foto' para disco local --}}
                                        <img class="h-10 w-10 rounded-lg object-cover border border-gray-200" 
                                             src="{{ route('personal.foto', ['path' => $baja->imagen]) }}" 
                                             alt="{{ $baja->nombre }}">
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $baja->nombre }}</div>
                                    <div class="text-[10px] text-gray-500 uppercase font-semibold">
                                        Responsable: {{ $baja->personal->nombre ?? 'Sin asignar' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 uppercase">
                                {{ $baja->tipo_inventario }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-[11px] text-gray-600 space-y-1">
                                @if($baja->detalles)
                                    @foreach($baja->detalles as $key => $value)
                                        <p><span class="font-bold text-gray-400 uppercase">{{ $key }}:</span> {{ $value }}</p>
                                    @endforeach
                                @else
                                    <span class="text-gray-400 italic">Sin detalles</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $baja->fecha_baja ? $baja->fecha_baja->format('d/m/Y') : $baja->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <button wire:click="eliminarPermanente({{ $baja->id }})" 
                                    wire:confirm="¿Estás seguro de eliminar este registro permanentemente? Esta acción no se puede deshacer."
                                    class="text-red-400 hover:text-red-600 transition-colors p-2 hover:bg-red-50 rounded-full">
                                <svg class="h-5 w-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic">
                            No se encontraron registros de bajas en el historial.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bajas->links() }}
    </div>
</div>