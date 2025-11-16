<x-filament-panels::page>
    <div class="max-w-4xl mx-auto space-y-6">
        {{-- Package Summary --}}
        @if ($package)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    Resumo do Pacote
                </h2>

                <div class="space-y-3 mb-6">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Número do Pacote:</span>
                        <span class="font-mono font-semibold text-gray-900 dark:text-white">
                            {{ $package->package_number }}
                        </span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Total de Inscrições:</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $this->getRegistrationsCount() }}
                        </span>
                    </div>

                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between items-center">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Valor Total:</span>
                            <span class="text-3xl font-bold text-primary-600 dark:text-primary-400">
                                R$ {{ number_format($this->getPackageTotal(), 2, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Registrations List --}}
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Inscrições:</h3>
                    <div class="space-y-2">
                        @foreach ($package->registrations as $registration)
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ $registration->participant_name }}
                                    </div>
                                    <div class="text-gray-600 dark:text-gray-400">
                                        {{ $registration->event->name }}
                                    </div>
                                </div>
                                <div class="font-semibold text-gray-900 dark:text-white">
                                    R$ {{ number_format($registration->price_paid, 2, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Payment Method Selection --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 border border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    Selecione o Método de Pagamento
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    {{-- PIX Option --}}
                    <button
                        wire:click="selectMethod('pix')"
                        class="relative p-6 rounded-lg border-2 transition-all
                            {{ $selectedMethod === 'pix' 
                                ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20' 
                                : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700' 
                            }}"
                    >
                        @if ($selectedMethod === 'pix')
                            <div class="absolute top-3 right-3">
                                <x-heroicon-s-check-circle class="w-6 h-6 text-primary-600" />
                            </div>
                        @endif

                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-3 flex items-center justify-center rounded-full
                                {{ $selectedMethod === 'pix'
                                    ? 'bg-primary-100 dark:bg-primary-900'
                                    : 'bg-gray-100 dark:bg-gray-800'
                                }}">
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="w-10 h-10 {{ $selectedMethod === 'pix' ? 'text-primary-600' : 'text-gray-600' }}"
                                     viewBox="0 0 50 50"
                                     fill="currentColor">
                                    <path d="M 25 0.0390625 C 22.84 0.0390625 20.799531 0.88015625 19.269531 2.4101562 L 9.6796875 12 L 12.929688 12 C 14.529687 12 16.039922 12.619766 17.169922 13.759766 L 23.939453 20.529297 C 24.519453 21.109297 25.480547 21.109531 26.060547 20.519531 L 32.830078 13.759766 C 33.960078 12.619766 35.470312 12 37.070312 12 L 40.320312 12 L 30.730469 2.4101562 C 29.200469 0.88015625 27.16 0.0390625 25 0.0390625 z M 7.6796875 14 L 2.4101562 19.269531 C -0.74984375 22.429531 -0.74984375 27.570469 2.4101562 30.730469 L 7.6796875 36 L 12.929688 36 C 13.999687 36 14.999766 35.580078 15.759766 34.830078 L 22.529297 28.060547 C 23.889297 26.700547 26.110703 26.700547 27.470703 28.060547 L 34.240234 34.830078 C 35.000234 35.580078 36.000312 36 37.070312 36 L 42.320312 36 L 47.589844 30.730469 C 50.749844 27.570469 50.749844 22.429531 47.589844 19.269531 L 42.320312 14 L 37.070312 14 C 36.000313 14 35.000234 14.419922 34.240234 15.169922 L 27.470703 21.939453 C 26.790703 22.619453 25.9 22.960938 25 22.960938 C 24.1 22.960937 23.209297 22.619453 22.529297 21.939453 L 15.759766 15.169922 C 14.999766 14.419922 13.999688 14 12.929688 14 L 7.6796875 14 z M 25 29.037109 C 24.615 29.038359 24.229453 29.185469 23.939453 29.480469 L 17.169922 36.240234 C 16.039922 37.380234 14.529687 38 12.929688 38 L 9.6796875 38 L 19.269531 47.589844 C 20.799531 49.119844 22.84 49.960938 25 49.960938 C 27.16 49.960938 29.200469 49.119844 30.730469 47.589844 L 40.320312 38 L 37.070312 38 C 35.470313 38 33.960078 37.380234 32.830078 36.240234 L 26.060547 29.470703 C 25.770547 29.180703 25.385 29.035859 25 29.037109 z"/>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                PIX
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Pagamento instantâneo
                            </p>
                        </div>
                    </button>

                    {{-- Credit Card Option --}}
                    <button
                        wire:click="selectMethod('credit_card')"
                        class="relative p-6 rounded-lg border-2 transition-all
                            {{ $selectedMethod === 'credit_card' 
                                ? 'border-primary-600 bg-primary-50 dark:bg-primary-900/20' 
                                : 'border-gray-200 dark:border-gray-700 hover:border-primary-300 dark:hover:border-primary-700' 
                            }}"
                    >
                        @if ($selectedMethod === 'credit_card')
                            <div class="absolute top-3 right-3">
                                <x-heroicon-s-check-circle class="w-6 h-6 text-primary-600" />
                            </div>
                        @endif

                        <div class="flex flex-col items-center text-center">
                            <div class="w-16 h-16 mb-3 flex items-center justify-center rounded-full 
                                {{ $selectedMethod === 'credit_card' 
                                    ? 'bg-primary-100 dark:bg-primary-900' 
                                    : 'bg-gray-100 dark:bg-gray-800' 
                                }}">
                                <x-heroicon-o-credit-card class="w-10 h-10 {{ $selectedMethod === 'credit_card' ? 'text-primary-600' : 'text-gray-600' }}" />
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">
                                Cartão de Crédito
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Parcelamento disponível
                            </p>
                        </div>
                    </button>
                </div>

                {{-- Process Payment Button --}}
                <div class="flex justify-between items-center">
                    <a href="{{ route('filament.admin.pages.my-registrations-page') }}" 
                       class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
                        ← Voltar para Minhas Inscrições
                    </a>

                    <x-filament::button
                        size="xl"
                        wire:click="processPayment"
                        :disabled="!$selectedMethod"
                    >
                        <x-heroicon-o-arrow-right class="w-5 h-5 mr-2" />
                        Prosseguir para Pagamento
                    </x-filament::button>
                </div>
            </div>

            {{-- Security Notice --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
                <div class="flex items-start gap-3">
                    <x-heroicon-o-shield-check class="w-6 h-6 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-800 dark:text-blue-300">
                        <p class="font-semibold mb-1">Pagamento Seguro</p>
                        <p>Você será redirecionado para o ambiente seguro do Mercado Pago para concluir o pagamento. 
                           Seus dados estão protegidos e criptografados.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
