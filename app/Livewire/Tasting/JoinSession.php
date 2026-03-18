<?php

namespace App\Livewire\Tasting;

use App\Events\PlayerJoined;
use App\Models\TastingSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JoinSession extends Component
{
    public string $code = '';

    public string $display_name = '';

    public function mount(): void
    {
        $this->code = request()->query('code', '');
        if (Auth::check()) {
            $this->display_name = Auth::user()->name;
        }
    }

    public function join(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6'],
            'display_name' => ['required', 'string', 'max:100'],
        ]);

        $session = TastingSession::where('code', strtoupper($this->code))->first();

        if (! $session) {
            $this->addError('code', __('No session found with this code.'));

            return;
        }

        $user = Auth::user();
        $user_id = $user?->id;

        // When logged in: always bind by user_id (never by display name).
        // When guest: only reattach if we have a matching session_participant_id in the session.
        if ($user_id) {
            $existing = $session->participants()
                ->where('user_id', $user_id)
                ->first();
        } else {
            $existing = null;
            $participantId = session('tasting_participant_id');
            if ($participantId) {
                $existing = $session->participants()
                    ->where('id', $participantId)
                    ->first();
            }
        }

        if ($existing) {
            // Rejoin: restore participant if they had left, and (for guests) restore session participant id.
            if ($existing->left_at !== null) {
                $existing->update([
                    'left_at' => null,
                    'joined_at' => now(),
                    'display_name' => $this->display_name,
                ]);

                broadcast(new PlayerJoined(
                    $session->id,
                    $existing->id,
                    $existing->display_name,
                    $user_id === null
                ))->toOthers();
            }

            session()->put('tasting_participant_id', $existing->id);
            session()->put('tasting_display_name', $this->display_name);
            session()->put('tasting_open_avatar_modal', true);
            $this->redirect(route('tasting.show', $session), navigate: true);

            return;
        }

        $participant = $session->participants()->create([
            'user_id' => $user_id,
            'display_name' => $this->display_name,
            'is_host' => false,
        ]);

        broadcast(new PlayerJoined(
            $session->id,
            $participant->id,
            $this->display_name,
            $user_id === null
        ))->toOthers();

        session()->put('tasting_participant_id', $participant->id);
        session()->put('tasting_display_name', $this->display_name);
        session()->put('tasting_open_avatar_modal', true);
        $this->redirect(route('tasting.show', $session), navigate: true);
    }

    public function render()
    {
        return view('livewire.tasting.join-session')->layout('layouts.auth', ['title' => __('Join Tasting Session')]);
    }
}
