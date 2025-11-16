<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($this->getEvents() as $event)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $event['name'] }}
                    </h3>
                    
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            <span>{{ $event['event_date']->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <p class="text-gray-700 dark:text-gray-300 mb-4 line-clamp-3">
                        {{ $event['description'] }}
                    </p>

                    @if ($event['current_price'])
                        <div class="mb-4">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Preço atual:</span>
                            <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                                R$ {{ number_format($event['current_price'], 2, ',', '.') }}
                            </span>
                        </div>
                    @else
                        <div class="mb-4">
                            <span class="text-sm text-red-600 dark:text-red-400">
                                Inscrições não disponíveis no momento
                            </span>
                        </div>
                    @endif

                    @if ($event['current_price'])
                        <a href="{{ route('filament.admin.pages.registration-page', ['event' => $event['id']]) }}" 
                           class="inline-flex items-center justify-center w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors">
                            <x-heroicon-o-pencil-square class="w-5 h-5 mr-2" />
                            Inscrever-se
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center">
                    <x-heroicon-o-calendar class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        Nenhum evento disponível
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Não há eventos disponíveis para inscrição no momento.
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
