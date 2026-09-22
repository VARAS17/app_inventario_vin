<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    
    <!-- ENCABEZADO Y ACCIÓN PRINCIPAL -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Directorio de Personal</h1>
            <p class="text-sm text-gray-500 mt-1">Gestiona los colaboradores, áreas, cargos y correos del inventario.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button 
                wire:click="crear" 
                class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 rounded-lg shadow-sm hover:shadow transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="w-5 h-5 mr-2 -ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuevo Personal
            </button>
        </div>
    </div>

    <!-- MENSAJES FLASH (ÉXITO) -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
             class="mb-6 flex items-center justify-between p-4 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800 rounded-r-lg shadow-xs transition-all">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800 focus:outline-none">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.416 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.416l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
            </button>
        </div>
    @endif

    <!-- MENSAJES FLASH (ERROR) -->
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" 
             class="mb-6 flex items-center justify-between p-4 bg-red-50 border-l-4 border-red-500 text-red-800 rounded-r-lg shadow-xs transition-all">
            <div class="flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.416l1.293 1.293a1 1 0 001.414-1.414L11.416 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800 focus:outline-none">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.416 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.416l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
            </button>
        </div>
    @endif

    <!-- BARRA DE BÚSQUEDA -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 mb-6 p-4">
        <div class="relative max-w-md">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <input 
                type="text" 
                wire:model.live.debounce.350ms="search" 
                placeholder="Buscar por nombre, cargo, área o correo..." 
                class="w-full pl-10 pr-10 py-2 text-sm bg-gray-50 border border-gray-300 rounded-lg focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-colors">
            
            @if(!empty($search))
                <button 
                    wire:click="$set('search', '')" 
                    title="Limpiar búsqueda"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.416l1.293 1.293a1 1 0 001.414-1.414L11.416 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                </button>
            @endif
        </div>
    </div>

    <!-- TABLA DE RESULTADOS -->
    <div class="bg-white rounded-xl shadow-xs border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Personal</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Área / Oficina</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Cargo</th>
                        <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Correo Electrónico</th>
                        <th scope="col" class="px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($personales as $persona)
                        <tr wire:key="persona-row-{{ $persona->id }}" class="hover:bg-gray-50/80 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <!-- AVATAR CON IMAGEN REAL O INICIALES COMO RESPALDO -->
                                    <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold text-sm flex items-center justify-center ring-2 ring-white overflow-hidden shadow-xs flex-shrink-0">
                                        @if ($persona->foto_perfil)
                                            <img src="{{ route('personal.foto', ['path' => $persona->foto_perfil]) }}" 
                                                 alt="{{ $persona->nombre }}" 
                                                 class="w-full h-full object-cover"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <span style="display: none;" class="w-full h-full flex items-center justify-center">
                                                {{ $persona->initials() }}
                                            </span>
                                        @else
                                            <span>{{ $persona->initials() }}</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ $persona->nombre }} {{ $persona->apellido }}
                                        </div>
                                        <div class="text-xs text-indigo-600 font-medium">
                                            {{ $persona->grado_academico }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <!-- COLUMNA ÁREA -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                                    {{ $persona->area->nombre ?? 'Sin área asignada' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $persona->cargo }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="mailto:{{ $persona->correo }}" class="text-sm text-gray-600 hover:text-indigo-600 flex items-center gap-1.5 transition-colors">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $persona->correo }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <button 
                                        wire:click="editar({{ $persona->id }})" 
                                        title="Editar personal"
                                        class="p-1.5 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>

                                    <button 
                                        wire:click="eliminar({{ $persona->id }})" 
                                        wire:confirm="¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer."
                                        title="Eliminar personal"
                                        class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p class="text-base font-medium text-gray-800">No se encontró personal</p>
                                    <p class="text-xs text-gray-400 mt-1 max-w-sm">No hay registros que coincidan con "{{ $search }}".</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINACIÓN -->
        @if ($personales->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $personales->links() }}
            </div>
        @endif
    </div>

    <!-- MODAL (CREAR / EDITAR) -->
    @if ($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                
                <!-- Fondo oscuro / Backdrop -->
                <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" 
                     wire:click="closeModal" 
                     aria-hidden="true"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <!-- Contenedor del Modal -->
                <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    
                    <!-- Header Modal -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h3 class="text-lg font-semibold text-gray-900" id="modal-title">
                            {{ $personal_id ? 'Editar Personal' : 'Registrar Personal' }}
                        </h3>
                        <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 rounded-lg p-1 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.416 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.416l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
                        </button>
                    </div>

                    <!-- Formulario -->
                    <form wire:submit.prevent="guardar">
                        <div class="px-6 py-5 space-y-4">

                            <!-- FOTO DE PERFIL CON LIVE PREVIEW -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wider text-gray-600 mb-2">Foto de Perfil</label>
                                <div class="flex items-center gap-4">
                                    <div class="relative w-16 h-16 rounded-full overflow-hidden bg-gray-100 border border-gray-300 flex items-center justify-center flex-shrink-0">
                                        @if ($foto_perfil)
                                            {{-- Previsualización temporal de la nueva foto seleccionada --}}
                                            <img src="{{ $foto_perfil->temporaryUrl() }}" class="w-full h-full object-cover">
                                        @elseif ($foto_actual)
                                            {{-- Foto actual ya guardada en el disco local --}}
                                            <img src="{{ route('personal.foto', ['path' => $foto_actual]) }}" class="w-full h-full object-cover" onerror="this.style.display='none'">
                                        @else
                                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        @endif

                                        <!-- Indicador de carga de archivo -->
                                        <div wire:loading wire:target="foto_perfil" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                            <svg class="animate-spin w-5 h-5 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-1">
                                        <input 
                                            type="file" 
                                            wire:model="foto_perfil" 
                                            accept="image/png, image/jpeg, image/jpg" 
                                            class="text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                                        <p class="text-[11px] text-gray-400 mt-1">PNG, JPG o JPEG hasta 2MB.</p>
                                        @error('foto_perfil') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- CAMPO ÁREA DE TRABAJO -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Área o Dependencia <span class="text-red-500">*</span></label>
                                <select wire:model.blur="area_id" class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white @error('area_id') border-red-400 bg-red-50/30 @else border-gray-300 @enderror">
                                    <option value="">-- Seleccionar Área --</option>
                                    @foreach ($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->nombre }}</option>
                                    @endforeach
                                </select>
                                @error('area_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- NOMBRE Y APELLIDO -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="nombre" placeholder="Ej. Juan" class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('nombre') border-red-400 bg-red-50/30 @else border-gray-300 @enderror">
                                    @error('nombre') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Apellido <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="apellido" placeholder="Ej. Pérez" class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('apellido') border-red-400 bg-red-50/30 @else border-gray-300 @enderror">
                                    @error('apellido') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- GRADO ACADÉMICO Y CARGO -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Grado Académico <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="grado_academico" placeholder="Ej. Lic., Ing., Dr., Mtro." class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('grado_academico') border-red-400 bg-red-50/30 @else border-gray-300 @enderror">
                                    @error('grado_academico') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Cargo <span class="text-red-500">*</span></label>
                                    <input type="text" wire:model.blur="cargo" placeholder="Ej. Encargado de TI" class="w-full text-sm px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('cargo') border-red-400 bg-red-50/30 @else border-gray-300 @enderror">
                                    @error('cargo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <!-- CORREO ELECTRÓNICO -->
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1">Correo Electrónico <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                                        </svg>
                                    </div>
                                    <input type="email" wire:model.blur="correo" placeholder="correo@institucion.gob.mx" class="w-full text-sm pl-9 pr-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none @error('correo') border-red-400 bg-red-50/30 @else border-gray-300 @enderror">
                                </div>
                                @error('correo') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                        </div>

                        <!-- Footer con Botones -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button 
                                type="button" 
                                wire:click="closeModal" 
                                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none transition-colors">
                                Cancelar
                            </button>
                            
                            <button 
                                type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="guardar, foto_perfil"
                                class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 active:scale-98 rounded-lg shadow-sm focus:outline-none disabled:opacity-50 transition-all">
                                
                                <span wire:loading.remove wire:target="guardar">
                                    {{ $personal_id ? 'Actualizar' : 'Guardar' }}
                                </span>
                                
                                <span wire:loading wire:target="guardar" class="flex items-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

</div>