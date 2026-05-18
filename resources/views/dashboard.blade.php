<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-zinc-800 leading-tight italic">
            {{ __('Dashboard Principal') }}
        </h2>
    </x-slot>

    <div class="space-y-8">
        <h1 class="text-3xl font-bold text-zinc-800 mb-8">Resumen de Dirección</h1>

        <!-- Cards Estadísticas -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Card Tecnología -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-zinc-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 uppercase">Equipos Tecnológicos</p>
                        <h3 class="text-2xl font-bold text-blue-600">{{ \App\Models\Tecnologia::count() }}</h3>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <span class="text-blue-600 text-2xl font-bold">🖥️</span>
                    </div>
                </div>
            </div>

            <!-- Card Mobiliario -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-zinc-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 uppercase">Mobiliario Total</p>
                        <h3 class="text-2xl font-bold text-amber-600">{{ \App\Models\Mobiliario::count() }}</h3>
                    </div>
                    <div class="p-3 bg-amber-50 rounded-xl">
                        <span class="text-amber-600 text-2xl font-bold">🪑</span>
                    </div>
                </div>
            </div>

            <!-- Card Útiles -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-zinc-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-zinc-500 uppercase">Artículos Útiles</p>
                        <h3 class="text-2xl font-bold text-indigo-600">{{ \App\Models\Util::count() }}</h3>
                    </div>
                    <div class="p-3 bg-indigo-50 rounded-xl">
                        <span class="text-indigo-600 text-2xl font-bold">📦</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Alerta Malogrados -->
            <div class="bg-white rounded-2xl shadow-sm border border-red-200 overflow-hidden">
                <div class="p-4 bg-red-50 border-b border-red-100 font-bold text-red-800">Equipos en Mal Estado</div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-100">
                        @forelse(\App\Models\Tecnologia::where('estado', 'Malogrado')->get() as $tech)
                            <li class="py-3 flex justify-between">
                                <span>{{ $tech->nombre }} ({{ $tech->marca }})</span>
                                <span class="text-red-500 font-bold">S/N: {{ $tech->serie }}</span>
                            </li>
                        @empty
                            <p class="text-gray-500">Todo operativo.</p>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Alerta Stock Bajo -->
            <div class="bg-white rounded-2xl shadow-sm border border-amber-200 overflow-hidden">
                <div class="p-4 bg-amber-50 border-b border-amber-100 font-bold text-amber-800">Suministros Críticos (Stock <= 5)</div>
                <div class="p-4">
                    <ul class="divide-y divide-gray-100">
                        @forelse(\App\Models\Util::where('cantidad', '<=', 5)->get() as $util)
                            <li class="py-3 flex justify-between font-bold">
                                <span>{{ $util->nombre }}</span>
                                <span class="text-red-600">{{ $util->cantidad }} {{ $util->unidad }}</span>
                            </li>
                        @empty
                            <p class="text-gray-500 italic text-sm">Stock suficiente en todos los artículos.</p>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>