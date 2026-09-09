<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Move extends Model
{
    protected $fillable = ['room_id', 'cell', 'symbol'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }
}

