<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EventLogoController extends Controller
{
    public function upload(Request $request, Event $event)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        try {
            $request->validate([
                'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            // Delete old logo if exists
            if ($event->logo) {
                Storage::disk('public')->delete($event->logo);
            }

            // Store new logo
            $path = $request->file('logo')->store('event-logos', 'public');

            // Update event
            $event->update(['logo' => $path]);

            return redirect()->route('filament.admin.resources.events.edit', $event)
                ->with('success', 'Logo atualizada com sucesso!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erro ao fazer upload: ' . $e->getMessage()]);
        }
    }

    public function delete(Event $event)
    {
        $user = Auth::user();

        if (!$user || !$user->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        if ($event->logo) {
            Storage::disk('public')->delete($event->logo);
            $event->update(['logo' => null]);
        }

        return redirect()->route('filament.admin.resources.events.edit', $event)
            ->with('success', 'Logo removida com sucesso!');
    }
}
