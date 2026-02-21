<?php

use Illuminate\Support\Facades\Broadcast;

// Tasting session updates use a public channel so guests (no login) can subscribe.
// Channel name: tasting.session.{id} - no auth required.
