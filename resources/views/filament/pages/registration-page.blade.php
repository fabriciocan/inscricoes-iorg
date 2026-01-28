<div class="space-y-6 mt-10">
    @if ($event)
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 border border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                <div class="flex-1">
                    <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">
                        {{ $event->name }}
                    </h2>
                    <div class="flex items-center gap-4 text-sm text-gray-600 mb-3">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            <span>{{ $event->event_date->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <p class="text-gray-700 text-sm md:text-base">
                        {{ $event->description }}
                    </p>
                </div>
                <div class="lg:ml-6 mt-4 lg:mt-0">
                    @php
                        $prices = $this->getCurrentPrices();
                    @endphp
                    @if ($prices)
                        <div class="text-sm text-gray-600 font-semibold mb-2">Preço por inscrição:</div>
                        <div class="grid grid-cols-2 lg:grid-cols-1 gap-2">
                            <div class="flex items-center justify-between bg-primary-50 rounded-lg p-2 lg:min-w-[200px]">
                                <div class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-primary-600" viewBox="0 0 50 50" fill="currentColor">
                                        <path d="M 25 0.0390625 C 22.84 0.0390625 20.799531 0.88015625 19.269531 2.4101562 L 9.6796875 12 L 12.929688 12 C 14.529687 12 16.039922 12.619766 17.169922 13.759766 L 23.939453 20.529297 C 24.519453 21.109297 25.480547 21.109531 26.060547 20.519531 L 32.830078 13.759766 C 33.960078 12.619766 35.470312 12 37.070312 12 L 40.320312 12 L 30.730469 2.4101562 C 29.200469 0.88015625 27.16 0.0390625 25 0.0390625 z M 7.6796875 14 L 2.4101562 19.269531 C -0.74984375 22.429531 -0.74984375 27.570469 2.4101562 30.730469 L 7.6796875 36 L 12.929688 36 C 13.999687 36 14.999766 35.580078 15.759766 34.830078 L 22.529297 28.060547 C 23.889297 26.700547 26.110703 26.700547 27.470703 28.060547 L 34.240234 34.830078 C 35.000234 35.580078 36.000312 36 37.070312 36 L 42.320312 36 L 47.589844 30.730469 C 50.749844 27.570469 50.749844 22.429531 47.589844 19.269531 L 42.320312 14 L 37.070312 14 C 36.000313 14 35.000234 14.419922 34.240234 15.169922 L 27.470703 21.939453 C 26.790703 22.619453 25.9 22.960938 25 22.960938 C 24.1 22.960937 23.209297 22.619453 22.529297 21.939453 L 15.759766 15.169922 C 14.999766 14.419922 13.999688 14 12.929688 14 L 7.6796875 14 z M 25 29.037109 C 24.615 29.038359 24.229453 29.185469 23.939453 29.480469 L 17.169922 36.240234 C 16.039922 37.380234 14.529687 38 12.929688 38 L 9.6796875 38 L 19.269531 47.589844 C 20.799531 49.119844 22.84 49.960938 25 49.960938 C 27.16 49.960938 29.200469 49.119844 30.730469 47.589844 L 40.320312 38 L 37.070312 38 C 35.470313 38 33.960078 37.380234 32.830078 36.240234 L 26.060547 29.470703 C 25.770547 29.180703 25.385 29.035859 25 29.037109 z"/>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-900">PIX</span>
                                </div>
                                <span class="text-base md:text-lg font-bold text-primary-600">
                                    R$ {{ number_format($prices['pix'], 2, ',', '.') }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2 lg:min-w-[200px]">
                                <div class="flex items-center gap-1.5">
                                    <x-heroicon-o-credit-card class="w-4 h-4 flex-shrink-0 text-gray-600" />
                                    <span class="text-xs font-medium text-gray-900">Cartão</span>
                                </div>
                                <span class="text-base md:text-lg font-bold text-gray-600">
                                    R$ {{ number_format($prices['card'], 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @else
                        <span class="text-sm text-red-600">Preços não disponíveis</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if ($showForm)
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">
                    Nova Inscrição
                </h3>
                <x-filament::button
                    color="gray"
                    size="sm"
                    wire:click="toggleForm"
                >
                    Cancelar
                </x-filament::button>
            </div>

            <form wire:submit="submitForm" class="space-y-6">
                {{ $this->form }}

                <div class="mt-6 flex justify-end gap-3">
                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="toggleForm"
                    >
                        Cancelar
                    </x-filament::button>
                    <x-filament::button
                        type="submit"
                        color="success"
                        size="lg"
                    >
                        <x-heroicon-o-plus-circle class="w-5 h-5 mr-2" />
                        Adicionar Inscrição
                    </x-filament::button>
                </div>
            </form>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-1">
                        Adicionar Nova Inscrição
                    </h3>
                    <p class="text-sm text-gray-600">
                        Preencha o formulário com os dados do participante
                    </p>
                </div>
                <x-filament::button
                    color="success"
                    icon="heroicon-o-plus-circle"
                    wire:click="toggleForm"
                >
                    Nova Inscrição
                </x-filament::button>
            </div>
        </div>
    @endif

    @if (!empty($registrations))
        <div class="bg-white rounded-lg shadow-md p-4 md:p-6 border border-gray-200">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <h3 class="text-base md:text-lg font-semibold text-gray-900">
                    Inscrições Adicionadas ({{ count($registrations) }})
                </h3>
                <div class="w-full md:w-auto">
                    <div class="text-sm text-gray-600 font-semibold mb-2">Total por método:</div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="flex items-center justify-between bg-primary-50 rounded-lg p-2">
                            <div class="flex items-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0 text-primary-600" viewBox="0 0 50 50" fill="currentColor">
                                    <path d="M 25 0.0390625 C 22.84 0.0390625 20.799531 0.88015625 19.269531 2.4101562 L 9.6796875 12 L 12.929688 12 C 14.529687 12 16.039922 12.619766 17.169922 13.759766 L 23.939453 20.529297 C 24.519453 21.109297 25.480547 21.109531 26.060547 20.519531 L 32.830078 13.759766 C 33.960078 12.619766 35.470312 12 37.070312 12 L 40.320312 12 L 30.730469 2.4101562 C 29.200469 0.88015625 27.16 0.0390625 25 0.0390625 z M 7.6796875 14 L 2.4101562 19.269531 C -0.74984375 22.429531 -0.74984375 27.570469 2.4101562 30.730469 L 7.6796875 36 L 12.929688 36 C 13.999687 36 14.999766 35.580078 15.759766 34.830078 L 22.529297 28.060547 C 23.889297 26.700547 26.110703 26.700547 27.470703 28.060547 L 34.240234 34.830078 C 35.000234 35.580078 36.000312 36 37.070312 36 L 42.320312 36 L 47.589844 30.730469 C 50.749844 27.570469 50.749844 22.429531 47.589844 19.269531 L 42.320312 14 L 37.070312 14 C 36.000313 14 35.000234 14.419922 34.240234 15.169922 L 27.470703 21.939453 C 26.790703 22.619453 25.9 22.960938 25 22.960938 C 24.1 22.960937 23.209297 22.619453 22.529297 21.939453 L 15.759766 15.169922 C 14.999766 14.419922 13.999688 14 12.929688 14 L 7.6796875 14 z M 25 29.037109 C 24.615 29.038359 24.229453 29.185469 23.939453 29.480469 L 17.169922 36.240234 C 16.039922 37.380234 14.529687 38 12.929688 38 L 9.6796875 38 L 19.269531 47.589844 C 20.799531 49.119844 22.84 49.960938 25 49.960938 C 27.16 49.960938 29.200469 49.119844 30.730469 47.589844 L 40.320312 38 L 37.070312 38 C 35.470313 38 33.960078 37.380234 32.830078 36.240234 L 26.060547 29.470703 C 25.770547 29.180703 25.385 29.035859 25 29.037109 z"/>
                                </svg>
                                <span class="text-xs font-medium text-gray-900">PIX</span>
                            </div>
                            <span class="text-base md:text-lg font-bold text-primary-600">
                                R$ {{ number_format($this->getPackageTotalForMethod('pix'), 2, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between bg-gray-50 rounded-lg p-2">
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-o-credit-card class="w-4 h-4 flex-shrink-0 text-gray-600" />
                                <span class="text-xs font-medium text-gray-900">Cartão</span>
                            </div>
                            <span class="text-base md:text-lg font-bold text-gray-600">
                                R$ {{ number_format($this->getPackageTotalForMethod('credit_card'), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($registrations as $registration)
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 p-3 md:p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 text-sm md:text-base truncate">
                                {{ $registration['participant_name'] }}
                            </div>
                            <div class="text-xs md:text-sm text-gray-600 mt-1 space-y-1">
                                <div class="flex items-center gap-1 truncate">
                                    <x-heroicon-o-envelope class="w-3 h-3 md:w-4 md:h-4 flex-shrink-0" />
                                    <span class="truncate">{{ $registration['participant_email'] }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <x-heroicon-o-phone class="w-3 h-3 md:w-4 md:h-4 flex-shrink-0" />
                                    <span>{{ $registration['participant_phone'] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between sm:justify-end gap-3 sm:gap-4">

                            <x-filament::button
                                color="danger"
                                size="sm"
                                wire:click="removeRegistration({{ $registration['id'] }})"
                                wire:confirm="Tem certeza que deseja remover esta inscrição?"
                            >
                                <x-heroicon-o-trash class="w-4 h-4" />
                            </x-filament::button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    size="xl"
                    wire:click="proceedToPayment"
                >
                    Prosseguir para Pagamento
                    <x-heroicon-o-arrow-right class="w-5 h-5 ml-2" />
                </x-filament::button>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-8 text-center border border-gray-200">
            <x-heroicon-o-user-group class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                Nenhuma inscrição adicionada
            </h3>
            <p class="text-gray-600">
                Clique no botão acima para adicionar sua primeira inscrição.
            </p>
        </div>
    @endif

    @if ($package)
        <div class="text-center text-sm text-gray-500">
            Número do Pacote: <span class="font-mono font-semibold">{{ $package->package_number }}</span>
        </div>
    @endif
</div>
