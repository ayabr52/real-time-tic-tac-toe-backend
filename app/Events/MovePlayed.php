<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class MovePlayed implements ShouldBroadcast
{
    public $roomName;
    public $cell;
    public $symbol;
    public $nextSymbol;

    public function __construct($roomName, $cell, $symbol, $nextSymbol)
    {
        $this->roomName = $roomName;
        $this->cell = $cell;
        $this->symbol = $symbol;
        $this->nextSymbol = $nextSymbol;

        \Log::info("EVENT CONSTRUCTOR CALLED", [
            'room' => $roomName,
            'cell' => $cell,
            'symbol' => $symbol,
            'next' => $nextSymbol
        ]);
    }

    public function broadcastOn()
    {
        \Log::info("BROADCAST ON CHANNEL: game." . $this->roomName);
        return new Channel("game." . $this->roomName);
    }

    public function broadcastAs()
    {
        return "MovePlayed";
    }

    public function broadcastWith()
    {
        \Log::info("BROADCAST DATA", [
            'cell' => $this->cell,
            'symbol' => $this->symbol,
            'next_symbol' => $this->nextSymbol
        ]);

        return [
            'cell' => $this->cell,
            'symbol' => $this->symbol,
            'next_symbol' => $this->nextSymbol,
        ];
    }
}
