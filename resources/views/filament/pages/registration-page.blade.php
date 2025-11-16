<div class="space-y-6">
    @if ($event)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                        {{ $event->name }}
                    </h2>
                    <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400 mb-3">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-calendar class="w-4 h-4" />
                            <span>{{ $event->event_date->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                    <p class="text-gray-700 dark:text-gray-300">
                        {{ $event->description }}
                    </p>
                </div>
                <div class="ml-6 text-right">
                    <span class="text-sm text-gray-600 dark:text-gray-400 block mb-1">Preço por inscrição:</span>
                    <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                        R$ {{ number_format($this->getCurrentPrice() ?? 0, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                    Adicionar Inscrição
                </h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Clique no botão abaixo para adicionar uma nova inscrição ao evento
                </p>
            </div>
            {{ ($this->addRegistrationAction)(['event_id' => $event->id, 'package_id' => $package?->id]) }}
        </div>
    </div>

    @if (!empty($registrations))
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Inscrições Adicionadas ({{ count($registrations) }})
                </h3>
                <div class="text-right">
                    <span class="text-sm text-gray-600 dark:text-gray-400 block">Total:</span>
                    <span class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                        R$ {{ number_format($this->getPackageTotal(), 2, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                @foreach ($registrations as $registration)
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 dark:text-white">
                                {{ $registration['participant_name'] }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-envelope class="w-4 h-4" />
                                    {{ $registration['participant_email'] }}
                                </span>
                                <span class="mx-2">•</span>
                                <span class="inline-flex items-center gap-1">
                                    <x-heroicon-o-phone class="w-4 h-4" />
                                    {{ $registration['participant_phone'] }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 ml-4">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">
                                R$ {{ number_format($registration['price_paid'], 2, ',', '.') }}
                            </span>
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 text-center border border-gray-200 dark:border-gray-700">
            <x-heroicon-o-user-group class="w-16 h-16 mx-auto text-gray-400 mb-4" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                Nenhuma inscrição adicionada
            </h3>
            <p class="text-gray-600 dark:text-gray-400">
                Clique no botão acima para adicionar sua primeira inscrição.
            </p>
        </div>
    @endif

    @if ($package)
        <div class="text-center text-sm text-gray-500 dark:text-gray-400">
            Número do Pacote: <span class="font-mono font-semibold">{{ $package->package_number }}</span>
        </div>
    @endif

    <x-filament-actions::modals />
</div>
