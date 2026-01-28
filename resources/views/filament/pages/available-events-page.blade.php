<x-filament-panels::page >
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($this->getEvents() as $event)
            <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-200">
                @if (!empty($event['logo']))
                    <div class="w-full h-48 bg-gray-100 flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('storage/' . $event['logo']) }}"
                             alt="{{ $event['name'] }}"
                             class="w-full h-full object-cover">
                    </div>
                @endif

                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">
                        {{ $event['name'] }}
                    </h3>

                    <div class="text-sm text-gray-600 mb-4">
                        <div class="flex items-center gap-2 mb-2">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            <span>{{ $event['event_date']->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>

                    <p class="text-gray-700 mb-4 line-clamp-3">
                        {{ $event['description'] }}
                    </p>

                    @if ($event['price_pix'] && $event['price_card'])
                        <div class="mb-4 space-y-2">
                            <div class="text-sm text-gray-600 font-semibold">Valores:</div>
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary-600" viewBox="0 0 50 50" fill="currentColor">
                                        <path d="M 25 0.0390625 C 22.84 0.0390625 20.799531 0.88015625 19.269531 2.4101562 L 9.6796875 12 L 12.929688 12 C 14.529687 12 16.039922 12.619766 17.169922 13.759766 L 23.939453 20.529297 C 24.519453 21.109297 25.480547 21.109531 26.060547 20.519531 L 32.830078 13.759766 C 33.960078 12.619766 35.470312 12 37.070312 12 L 40.320312 12 L 30.730469 2.4101562 C 29.200469 0.88015625 27.16 0.0390625 25 0.0390625 z M 7.6796875 14 L 2.4101562 19.269531 C -0.74984375 22.429531 -0.74984375 27.570469 2.4101562 30.730469 L 7.6796875 36 L 12.929688 36 C 13.999687 36 14.999766 35.580078 15.759766 34.830078 L 22.529297 28.060547 C 23.889297 26.700547 26.110703 26.700547 27.470703 28.060547 L 34.240234 34.830078 C 35.000234 35.580078 36.000312 36 37.070312 36 L 42.320312 36 L 47.589844 30.730469 C 50.749844 27.570469 50.749844 22.429531 47.589844 19.269531 L 42.320312 14 L 37.070312 14 C 36.000313 14 35.000234 14.419922 34.240234 15.169922 L 27.470703 21.939453 C 26.790703 22.619453 25.9 22.960938 25 22.960938 C 24.1 22.960937 23.209297 22.619453 22.529297 21.939453 L 15.759766 15.169922 C 14.999766 14.419922 13.999688 14 12.929688 14 L 7.6796875 14 z M 25 29.037109 C 24.615 29.038359 24.229453 29.185469 23.939453 29.480469 L 17.169922 36.240234 C 16.039922 37.380234 14.529687 38 12.929688 38 L 9.6796875 38 L 19.269531 47.589844 C 20.799531 49.119844 22.84 49.960938 25 49.960938 C 27.16 49.960938 29.200469 49.119844 30.730469 47.589844 L 40.320312 38 L 37.070312 38 C 35.470313 38 33.960078 37.380234 32.830078 36.240234 L 26.060547 29.470703 C 25.770547 29.180703 25.385 29.035859 25 29.037109 z"/>
                                    </svg>
                                    <span class="text-sm text-gray-700 font-medium">PIX</span>
                                </div>
                                <span class="text-lg font-bold text-primary-600">
                                    R$ {{ number_format($event['price_pix'], 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                                <div class="flex items-center gap-2">
                                    <x-heroicon-o-credit-card class="w-5 h-5 text-gray-600" />
                                    <span class="text-sm text-gray-700 font-medium">Cartão</span>
                                </div>
                                <span class="text-lg font-bold text-gray-600">
                                    R$ {{ number_format($event['price_card'], 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <span class="text-sm text-red-600">
                                Inscrições não disponíveis no momento
                            </span>
                        </div>
                    @endif

                    @if ($event['price_pix'] && $event['price_card'])
                        <a href="{{ route('filament.admin.pages.registration-page', ['event' => $event['id']]) }}"
                           class="inline-flex items-center justify-center w-full px-4 py-2 bg-blue-600 hover:bg-blue-900 text-white font-semibold rounded-lg transition-colors">
                            <x-heroicon-o-pencil-square class="w-5 h-5 mr-2" />
                            Inscrever-se
                        </a>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-white rounded-lg shadow-md p-8 text-center">
                    <x-heroicon-o-calendar class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Nenhum evento disponível
                    </h3>
                    <p class="text-gray-600">
                        Não há eventos disponíveis para inscrição no momento.
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
