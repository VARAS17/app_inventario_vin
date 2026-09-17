<div class="p-4 md:p-6 bg-slate-50 min-h-screen space-y-6 text-slate-800">

    {{-- Notificaciones Flash --}}
    @if (session()->has('message'))
        <div class="flex items-center p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-lg shadow-sm">
            <svg class="w-5 h-5 text-emerald-500 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span class="text-emerald-800 font-medium text-sm">{{ session('message') }}</span>
        </div>
    @endif

    {{-- CABECERA: Buscador y Botón Crear --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-4 rounded-xl shadow-xs border border-slate-200">
        <div>
            <h1 class="text-xl font-bold text-slate-900 tracking-tight">Inventario de Tecnología</h1>
            <p class="text-xs text-slate-500">Gestión de activos, equipos, documentos e historial local.</p>
        </div>

        <div class="flex items-center gap-3">
            {{-- Buscador reactivo --}}
            <div class="relative w-full sm:w-72">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Buscar por VIN, nombre, serie..." 
                    class="w-full pl-9 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-lg focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:bg-white transition"
                />
            </div>

            {{-- Botón Registrar --}}
            <button 
                wire:click="openCreateModal"
                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white text-sm font-medium rounded-lg shadow-sm transition whitespace-nowrap cursor-pointer">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Equipo
            </button>
        </div>
    </div>

    {{-- SECCIÓN SUPERIOR: Tabla con 8 ítems paginados --}}
    <div class="bg-white rounded-xl shadow-xs border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200 uppercase text-xs tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Cód. VIN</th>
                        <th class="py-3 px-4">Equipo / Nombre</th>
                        <th class="py-3 px-4">Marca</th>
                        <th class="py-3 px-4">Serie</th>
                        <th class="py-3 px-4">Estado</th>
                        <th class="py-3 px-4">Ingreso</th>
                        <th class="py-3 px-4 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($tecnologias as $tec)
                        <tr 
                            wire:key="row-{{ $tec->id }}"
                            wire:click="selectItem({{ $tec->id }})"
                            class="cursor-pointer transition hover:bg-slate-50/80 {{ $selectedId === $tec->id ? 'bg-indigo-50/80 font-medium' : '' }}"
                        >
                            <td class="py-3 px-4">
                                <span class="font-mono text-xs px-2 py-1 bg-slate-100 text-slate-700 rounded border border-slate-200">
                                    {{ $tec->codigo_vin }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-900 font-medium">{{ $tec->nombre }}</td>
                            <td class="py-3 px-4">{{ $tec->marca }}</td>
                            <td class="py-3 px-4 font-mono text-xs text-slate-500">{{ $tec->serie ?? 'S/N' }}</td>
                            <td class="py-3 px-4">
                                @if($tec->estado === 'Disponible')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        ● Disponible
                                    </span>
                                @elseif($tec->estado === 'Asignado')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        ● Asignado
                                    </span>
                                @elseif($tec->estado === 'Prestado')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        ● Prestado
                                    </span>
                                @elseif($tec->estado === 'De baja')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        ● Dado de baja
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-600 text-slate-200">
                                        ● Mantenimiento
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-xs">
                                {{ $tec->fecha_ingreso ? \Carbon\Carbon::parse($tec->fecha_ingreso)->format('d/m/Y') : '-' }}
                            </td>
                            <td class="py-3 px-4 text-center" onclick="event.stopPropagation()">
                                <div class="inline-flex items-center gap-1">
                                    {{-- Editar --}}
                                    <button 
                                        wire:click="openEditModal({{ $tec->id }})" 
                                        class="p-1 text-slate-400 hover:text-indigo-600 rounded transition cursor-pointer" 
                                        title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>

                                    {{-- Eliminar --}}
                                    <button 
                                        wire:click="delete({{ $tec->id }})"
                                        wire:confirm="¿Seguro que deseas eliminar este registro? Se borrarán sus fotos y todos los PDFs adjuntos."
                                        class="p-1 text-slate-400 hover:text-rose-600 rounded transition cursor-pointer" 
                                        title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400 text-sm">
                                No se encontraron registros de tecnologías.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Paginación fija de 8 --}}
        <div class="px-4 py-3 border-t border-slate-100 bg-white">
            {{ $tecnologias->links() }}
        </div>
    </div>

    {{-- SECCIÓN INFERIOR: División vertical en dos columnas --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- COLUMNA IZQUIERDA: Detalles + Foto + Lista de PDFs con Ojo 👁 --}}
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 flex flex-col justify-between">
            <div>
                <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ficha Técnica del Equipo
                </h2>

                @if ($selectedTecnologia)
                    {{-- Datos Técnicos y Foto --}}
                    <div class="flex flex-col sm:flex-row gap-5 items-start">
                        <div class="w-full sm:w-36 h-36 bg-slate-100 rounded-xl overflow-hidden border border-slate-200 shrink-0 flex items-center justify-center">
                            @if ($selectedTecnologia->foto)
                                <img 
                                    src="{{ route('tecnologia.foto', $selectedTecnologia->foto) }}" 
                                    alt="Foto de {{ $selectedTecnologia->nombre }}" 
                                    class="w-full h-full object-cover"
                                >
                            @else
                                <div class="text-center p-3 text-slate-400">
                                    <svg class="w-8 h-8 mx-auto stroke-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span class="text-[10px] mt-1 block">Sin imagen</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 space-y-1.5 text-xs w-full">
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <span class="text-slate-400">Código VIN:</span>
                                <span class="font-mono font-bold text-slate-800">{{ $selectedTecnologia->codigo_vin }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <span class="text-slate-400">Equipo:</span>
                                <span class="font-semibold text-slate-800">{{ $selectedTecnologia->nombre }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <span class="text-slate-400">Marca / Serie:</span>
                                <span class="text-slate-700">{{ $selectedTecnologia->marca }} - {{ $selectedTecnologia->serie ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <span class="text-slate-400">Estado:</span>
                                <span class="font-medium text-slate-800">{{ $selectedTecnologia->estado }}</span>
                            </div>
                            <div class="flex justify-between border-b border-slate-100 pb-1">
                                <span class="text-slate-400">Proveedor:</span>
                                <span class="text-slate-700">{{ $selectedTecnologia->proveedor }}</span>
                            </div>
                            <div class="flex justify-between pb-1">
                                <span class="text-slate-400">Fecha Ingreso:</span>
                                <span class="text-slate-700">
                                    {{ $selectedTecnologia->fecha_ingreso ? \Carbon\Carbon::parse($selectedTecnologia->fecha_ingreso)->format('d/m/Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- SUBSECCIÓN: Documentos PDF Adjuntos con Icono Ojo 👁 --}}
                    <div class="mt-5 pt-4 border-t border-slate-100">
                        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            Documentos Adjuntos (PDF)
                        </h3>

                        <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                            @forelse ($selectedTecnologia->archivos as $archivo)
                                <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="text-rose-500 font-bold text-[10px] bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">PDF</span>
                                        <span class="font-medium text-slate-700 truncate" title="{{ $archivo->nombre }}">
                                            {{ $archivo->nombre_archivo }}
                                        </span>
                                    </div>

                                    {{-- Icono de OJO para previsualizar --}}
                                    <a 
                                        href="{{ route('tecnologia.pdf', $archivo->ruta_archivo) }}" 
                                        target="_blank" 
                                        class="p-1 text-slate-400 hover:text-indigo-600 rounded transition cursor-pointer shrink-0" 
                                        title="Previsualizar Documento"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                </div>
                            @empty
                                <div class="p-2.5 bg-slate-50/60 rounded-lg text-slate-400 text-xs text-center border border-dashed border-slate-200">
                                    Sin documentos asociados a este equipo.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="h-48 flex flex-col items-center justify-center text-slate-400 border border-dashed border-slate-200 rounded-xl bg-slate-50/50">
                        <svg class="w-8 h-8 mb-2 stroke-1 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                        </svg>
                        <p class="text-xs">Selecciona un equipo para visualizar su información y documentos.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- COLUMNA DERECHA: Historial (Espacio reservado) --}}
        <div class="bg-white rounded-xl shadow-xs border border-slate-200 p-5 flex flex-col">
            <h2 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Historial de Movimientos
            </h2>

            <div class="flex-1 flex flex-col items-center justify-center p-6 border border-dashed border-slate-200 rounded-xl bg-slate-50/50 text-center">
                <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-xs font-semibold text-slate-700">Módulo de Historial en Espera</h3>
                <p class="text-xs text-slate-400 mt-1 max-w-xs">
                    Aquí se mostrarán las asignaciones y cambios de estado del equipo.
                </p>
            </div>
        </div>

    </div>

    {{-- MODAL REACTIVO: Crear / Editar con Subida Dinámica de PDFs --}}
    @if ($isOpenModal)
        <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-xl w-full p-6 transition-all max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-bold text-slate-900">
                        {{ $isEditMode ? 'Modificar Registro Tecnológico' : 'Registrar Nuevo Equipo' }}
                    </h3>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600 transition cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        {{-- Código VIN --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Código VIN (Automático)</label>
                            <input 
                                type="text" 
                                wire:model="codigo_vin" 
                                readonly 
                                class="w-full bg-slate-100 border border-slate-200 font-mono font-bold text-indigo-700 text-sm rounded-lg p-2.5 cursor-not-allowed"
                            />
                            @error('codigo_vin') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Nombre / Tipo --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre / Equipo *</label>
                            <input 
                                type="text" 
                                wire:model="nombre" 
                                placeholder="Ej: Laptop Dell Latitude" 
                                class="w-full border border-slate-300 text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            />
                            @error('nombre') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Marca --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Marca *</label>
                            <input 
                                type="text" 
                                wire:model="marca" 
                                placeholder="Ej: Dell, HP, Lenovo" 
                                class="w-full border border-slate-300 text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            />
                            @error('marca') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Serie --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Número de Serie</label>
                            <input 
                                type="text" 
                                wire:model="serie" 
                                placeholder="Ej: 5CD1234XYZ" 
                                class="w-full border border-slate-300 text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            />
                            @error('serie') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Estado --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Estado Operativo *</label>
                            <select 
                                wire:model="estado" 
                                class="w-full border border-slate-300 text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden">
                                <option value="Disponible">Disponible</option>
                                <option value="Asignado">Asignado</option>
                                <option value="Prestado">Prestado</option>
                                <option value="En Mantenimiento">En Mantenimiento</option>
                                <option value="De baja">De baja</option>
                            </select>
                            @error('estado') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Fecha de Ingreso --}}
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha de Ingreso *</label>
                            <input 
                                type="date" 
                                wire:model="fecha_ingreso" 
                                class="w-full border border-slate-300 text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                            />
                            @error('fecha_ingreso') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    {{-- Proveedor --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Proveedor / Factura *</label>
                        <input 
                            type="text" 
                            wire:model="proveedor" 
                            placeholder="Ej: Distribuidora Tech S.A.C." 
                            class="w-full border border-slate-300 text-sm rounded-lg p-2.5 focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                        />
                        @error('proveedor') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- Foto Principal --}}
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">
                            Fotografía del Equipo {{ $isEditMode ? '(Opcional para actualizar)' : '*' }}
                        </label>
                        
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0">
                                @if ($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="w-full h-full object-cover">
                                @elseif ($foto_existente)
                                    <img src="{{ route('tecnologia.foto', $foto_existente) }}" class="w-full h-full object-cover">
                                @else
                                    <svg class="w-6 h-6 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                @endif
                            </div>

                            <input 
                                type="file" 
                                wire:model="foto" 
                                accept="image/*"
                                class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer"
                            />
                        </div>
                        @error('foto') <span class="text-rose-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    {{-- SECCIÓN DE DOCUMENTOS PDF (MÚLTIPLES + BOTÓN +) --}}
                    <div class="pt-3 border-t border-slate-100">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <label class="text-xs font-bold text-slate-800">Documentos Adjuntos (PDFs)</label>
                                <p class="text-[11px] text-slate-400">Manuales, pólizas, facturas escaneadas, etc.</p>
                            </div>

                            {{-- Botón + para agregar nueva fila de PDF --}}
                            <button 
                                type="button" 
                                wire:click="addArchivoInput"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar PDF +
                            </button>
                        </div>

                        {{-- Lista de PDFs existentes (en modo edición) --}}
                        @if ($isEditMode && count($archivos_existentes) > 0)
                            <div class="space-y-1.5 mb-3">
                                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Archivos ya cargados:</span>
                                @foreach ($archivos_existentes as $doc)
                                    <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200 text-xs" wire:key="existente-{{ $doc->id }}">
                                        <span class="truncate font-medium text-slate-700">{{ $doc->nombre_archivo }}</span>
                                        <button 
                                            type="button"
                                            wire:click="eliminarArchivoExistente({{ $doc->id }})"
                                            wire:confirm="¿Seguro que deseas eliminar este documento?"
                                            class="p-1 text-rose-500 hover:text-rose-700 rounded transition cursor-pointer"
                                            title="Eliminar archivo"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Filas dinámicas de nuevos PDFs --}}
                        <div class="space-y-2.5">
                            @foreach ($nuevos_archivos as $index => $nuevo)
                                <div class="flex items-start gap-2 p-2.5 rounded-lg border border-slate-200 bg-slate-50/50" wire:key="nuevo-pdf-{{ $index }}">
                                    <div class="flex-1 space-y-1.5">
                                        <input 
                                            type="text" 
                                            wire:model="nuevos_archivos.{{ $index }}.nombre"
                                            placeholder="Nombre del documento (ej. Garantía)"
                                            class="w-full text-xs border border-slate-300 rounded-md p-2 bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-hidden"
                                        />
                                        @error("nuevos_archivos.{$index}.nombre") <span class="text-rose-500 text-[11px] block">{{ $message }}</span> @enderror

                                        <input 
                                            type="file" 
                                            wire:model="nuevos_archivos.{{ $index }}.archivo"
                                            accept="application/pdf"
                                            class="text-xs text-slate-500 file:mr-2 file:py-1 file:px-2.5 file:rounded-md file:border-0 file:text-[11px] file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer w-full"
                                        />
                                        @error("nuevos_archivos.{$index}.archivo") <span class="text-rose-500 text-[11px] block">{{ $message }}</span> @enderror
                                    </div>

                                    {{-- Botón para remover fila --}}
                                    <button 
                                        type="button" 
                                        wire:click="removeArchivoInput({{ $index }})"
                                        class="p-1.5 text-slate-400 hover:text-rose-500 transition cursor-pointer shrink-0"
                                        title="Quitar"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Botones de Acción --}}
                    <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
                        <button 
                            type="button" 
                            wire:click="closeModal" 
                            class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg transition cursor-pointer">
                            Cancelar
                        </button>
                        <button 
                            type="submit" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg shadow-sm transition inline-flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                            <span wire:loading.remove>{{ $isEditMode ? 'Actualizar Datos' : 'Guardar en Local' }}</span>
                            <span wire:loading>Guardando archivos...</span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif

</div>