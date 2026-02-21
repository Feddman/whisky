<?php

namespace App\Http\Controllers\Tasting;

use App\Events\PlayerLeft;
use App\Models\SessionParticipant;
use App\Models\TastingSession;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LeaveSessionController extends Controller
{
    public function __invoke(TastingSession $tastingSession): RedirectResponse
    {
        $participant = null;

        if (auth()->check()) {
            $participant = $tastingSession->participants()
                ->where('user_id', auth()->id())
                ->whereNull('left_at')
                ->first();
        } else {
            $id = session('tasting_participant_id');
            if ($id) {
                $participant = SessionParticipant::where('id', $id)
                    ->where('tasting_session_id', $tastingSession->id)
                    ->whereNull('left_at')
                    ->first();
            }
        }

        if ($participant && ! $participant->is_host) {
            $participant->update(['left_at' => now()]);
            broadcast(new PlayerLeft($tastingSession->id, $participant->id))->toOthers();
        }

        session()->forget(['tasting_participant_id', 'tasting_display_name']);

        return redirect()->route('tasting.join');
    }
}
