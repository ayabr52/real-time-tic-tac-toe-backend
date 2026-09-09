<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('game.{roomId}', function () {
    return true;
});

