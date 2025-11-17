<?php

namespace App\Livewire;

use App\Models\Event;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EventLogoUpload extends Component
{
    use WithFileUploads;

    public Event $event;
    public $logo;
    public $message = '';
    public $error = '';

    public function mount(Event $event)
    {
        $this->event = $event;
    }

    public function updatedLogo()
    {
        $this->validate([
            'logo' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);
    }

    public function upload()
    {
        $this->validate([
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        try {
            // Delete old logo if exists
            if ($this->event->logo) {
                Storage::disk('public')->delete($this->event->logo);
            }

            // Store new logo
            $path = $this->logo->store('event-logos', 'public');

            // Update event
            $this->event->update(['logo' => $path]);

            // Reset upload field
            $this->logo = null;

            // Set success message
            $this->message = 'Logo atualizada com sucesso!';
            $this->error = '';

            // Refresh the component
            $this->dispatch('logo-updated');
        } catch (\Exception $e) {
            $this->error = 'Erro ao fazer upload: ' . $e->getMessage();
            $this->message = '';
        }
    }

    public function deleteLogo()
    {
        try {
            if ($this->event->logo) {
                Storage::disk('public')->delete($this->event->logo);
                $this->event->update(['logo' => null]);

                $this->message = 'Logo removida com sucesso!';
                $this->error = '';

                // Refresh the component
                $this->dispatch('logo-updated');
            }
        } catch (\Exception $e) {
            $this->error = 'Erro ao remover logo: ' . $e->getMessage();
            $this->message = '';
        }
    }

    public function render()
    {
        return view('livewire.event-logo-upload');
    }
}
