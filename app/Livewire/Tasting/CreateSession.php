<?php

namespace App\Livewire\Tasting;

use App\Models\TastingSession;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateSession extends Component
{
    public string $name = '';

    public int $max_taste_tags = 5;

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'max_taste_tags' => ['required', 'integer', 'min:1', 'max:10'],
        ]);

        $session = TastingSession::create([
            'user_id' => Auth::id(),
            'name' => $this->name,
            'code' => TastingSession::generateCode(),
            'max_taste_tags' => $this->max_taste_tags,
            'status' => 'setup',
        ]);

        $session->participants()->create([
            'user_id' => Auth::id(),
            'display_name' => Auth::user()->name,
            'is_host' => true,
        ]);

        $this->redirect(route('tasting.show', $session), navigate: true);
    }

    public function render()
    {
        return view('livewire.tasting.create-session')
            ->layout('layouts.app', ['title' => __('Create Tasting Session')]);
    }
}
