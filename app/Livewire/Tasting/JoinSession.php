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

        $user_id = Auth::id();
        $existing = $session->participants()
            ->when($user_id, fn ($q) => $q->where('user_id', $user_id))
            ->when(! $user_id, fn ($q) => $q->where('display_name', $this->display_name))
            ->first();

        if ($existing) {
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
        $this->redirect(route('tasting.show', $session), navigate: true);
    }

    public function render()
    {
        return view('livewire.tasting.join-session')->layout('layouts.auth', ['title' => __('Join Tasting Session')]);
    }
}
