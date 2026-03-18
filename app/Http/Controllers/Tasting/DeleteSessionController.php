<?php

namespace App\Http\Controllers\Tasting;

use App\Http\Controllers\Controller;
use App\Models\TastingSession;
use Illuminate\Http\RedirectResponse;

class DeleteSessionController extends Controller
{
    public function __invoke(TastingSession $tastingSession): RedirectResponse
    {
        $this->authorize('delete', $tastingSession);

        $tastingSession->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', __('Session deleted.'));
    }
}

