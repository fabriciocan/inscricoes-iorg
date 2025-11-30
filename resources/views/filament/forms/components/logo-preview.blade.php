@php
    $record = $getRecord();
@endphp

@if($record)
    @livewire('event-logo-upload', ['event' => $record], key('event-logo-upload-' . $record->id))
@else
    <div class="border border-yellow-300 rounded-lg p-4 bg-yellow-50">
        <p class="text-sm text-yellow-800">
            Salve o evento primeiro para poder fazer upload da logo.
        </p>
    </div>
@endif
