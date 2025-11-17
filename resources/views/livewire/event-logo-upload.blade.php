<div class="flex flex-col gap-4">
    @if($event->logo)
        <div class="flex flex-col gap-2">
            <div class="w-full max-w-md" wire:key="logo-{{ $event->id }}-{{ $event->updated_at }}">
                <img src="{{ asset('storage/' . $event->logo) }}"
                     alt="Logo do evento"
                     class="w-full h-auto rounded-lg border border-gray-200 shadow-sm">
            </div>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ asset('storage/' . $event->logo) }}"
                   target="_blank"
                   class="text-blue-600 hover:text-blue-800 underline">
                    Abrir logo em nova aba
                </a>
                <button type="button"
                        wire:click="deleteLogo"
                        wire:confirm="Tem certeza que deseja remover a logo?"
                        class="text-red-600 hover:text-red-800 underline">
                    Remover logo
                </button>
            </div>
        </div>
    @else
        <div class="w-full max-w-md h-48 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
            <p class="text-gray-500 text-sm">Nenhuma logo definida</p>
        </div>
    @endif

    <div class="border border-green-300 rounded-lg p-4 bg-green-50">
        <p class="text-sm font-medium text-green-700 mb-3">
            Upload de Nova Logo
        </p>

        @if($message)
            <div class="mb-3 p-2 bg-green-100 border border-green-400 text-green-700 rounded text-sm">
                {{ $message }}
            </div>
        @endif

        @if($error)
            <div class="mb-3 p-2 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                {{ $error }}
            </div>
        @endif

        @error('logo')
            <div class="mb-3 p-2 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                {{ $message }}
            </div>
        @enderror

        <div class="space-y-3">
            <div>
                <input type="file"
                       wire:model="logo"
                       id="logo-upload-{{ $event->id }}"
                       accept="image/png,image/jpeg,image/jpg,image/gif,image/webp"
                       class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none">
                <p class="mt-1 text-xs text-gray-600">
                    Formatos aceitos: PNG, JPG, GIF, WebP. Tamanho máximo: 2MB
                </p>

                @if ($logo)
                    <div class="mt-2 text-sm text-green-600">
                        Arquivo selecionado: {{ $logo->getClientOriginalName() }}
                    </div>
                @endif

                <div wire:loading wire:target="logo" class="mt-2 text-sm text-blue-600">
                    Processando arquivo...
                </div>
            </div>

            <button type="button"
                    wire:click="upload"
                    wire:loading.attr="disabled"
                    wire:target="upload"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                <span wire:loading.remove wire:target="upload">Fazer Upload da Logo</span>
                <span wire:loading wire:target="upload">Fazendo upload...</span>
            </button>
        </div>
    </div>
</div>
