<div class="space-y-6">
    {{-- Package Information --}}
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Número do Pacote:</span>
                <div class="font-mono font-bold text-lg text-gray-900 dark:text-white">
                    {{ $package->package_number }}
                </div>
            </div>
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Status:</span>
                <div class="mt-1">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
                        {{ match($package->status) {
                            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                            'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
                            'confirmed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
                            'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
                            default => 'bg-gray-100 text-gray-800'
                        } }}">
                        {{ match($package->status) {
                            'draft' => 'Rascunho',
                            'pending' => 'Pendente',
                            'confirmed' => 'Confirmado',
                            'cancelled' => 'Cancelado',
                            default => $package->status
                        } }}
                    </span>
                </div>
            </div>
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Valor Total:</span>
                <div class="text-2xl font-bold text-primary-600 dark:text-primary-400">
                    R$ {{ number_format($package->total_amount, 2, ',', '.') }}
                </div>
            </div>
            <div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Método de Pagamento:</span>
                <div class="font-semibold text-gray-900 dark:text-white">
                    {{ match($package->payment_method) {
                        'pix' => 'PIX',
                        'credit_card' => 'Cartão de Crédito',
                        null => 'Não definido',
                        default => $package->payment_method
                    } }}
                </div>
            </div>
        </div>
        
        @if ($package->payment_id)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <span class="text-sm text-gray-600 dark:text-gray-400">ID do Pagamento:</span>
                <div class="font-mono text-sm text-gray-900 dark:text-white">
                    {{ $package->payment_id }}
                </div>
            </div>
        @endif

        @if ($package->status === 'pending')
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-yellow-600 dark:text-yellow-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-yellow-900 dark:text-yellow-200 mb-1">
                                Pagamento Pendente
                            </h4>
                            <p class="text-sm text-yellow-800 dark:text-yellow-300 mb-3">
                                O pagamento deste pacote ainda não foi confirmado. Clique em um dos botões abaixo para realizar o pagamento.
                            </p>
                            <div class="flex gap-2">
                                <a
                                    href="{{ route('filament.admin.pages.payment-page', ['package' => $package->id]) }}"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200"
                                >
                                    <x-heroicon-o-credit-card class="w-5 h-5 mr-2" />
                                    Pagar Agora
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($package->status === 'draft' && $package->registrations->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                        <div class="flex-1">
                            <h4 class="font-semibold text-blue-900 dark:text-blue-200 mb-1">
                                Rascunho
                            </h4>
                            <p class="text-sm text-blue-800 dark:text-blue-300 mb-3">
                                Este pacote está em rascunho. Finalize o pagamento para confirmar suas inscrições.
                            </p>
                            <div class="flex gap-2">
                                <a
                                    href="{{ route('filament.admin.pages.payment-page', ['package' => $package->id]) }}"
                                    class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg transition-colors duration-200"
                                >
                                    <x-heroicon-o-credit-card class="w-5 h-5 mr-2" />
                                    Continuar para Pagamento
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Registrations List --}}
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
            Inscrições ({{ $package->registrations->count() }})
        </h3>

        <div class="space-y-3">
            @foreach ($package->registrations as $registration)
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900 dark:text-white text-lg">
                                {{ $registration->participant_name }}
                            </div>
                            
                            <div class="mt-2 space-y-1">
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-calendar class="w-4 h-4" />
                                    <span class="font-medium">{{ $registration->event->name }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-clock class="w-4 h-4" />
                                    <span>{{ $registration->event->event_date->format('d/m/Y H:i') }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-envelope class="w-4 h-4" />
                                    <span>{{ $registration->participant_email }}</span>
                                </div>
                                
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-heroicon-o-phone class="w-4 h-4" />
                                    <span>{{ $registration->participant_phone }}</span>
                                </div>
                            </div>

                            @if ($registration->participant_data)
                                <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-900 rounded text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-medium text-gray-900 dark:text-white">Informações Adicionais:</span>
                                    <div class="mt-1">{{ $registration->participant_data }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="ml-4 text-right">
                            <div class="text-sm text-gray-600 dark:text-gray-400">Valor pago:</div>
                            <div class="text-xl font-bold text-gray-900 dark:text-white">
                                R$ {{ number_format($registration->price_paid, 2, ',', '.') }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Timestamps --}}
    <div class="text-sm text-gray-500 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex justify-between">
            <span>Criado em: {{ $package->created_at->format('d/m/Y H:i') }}</span>
            <span>Atualizado em: {{ $package->updated_at->format('d/m/Y H:i') }}</span>
        </div>
    </div>
</div>
