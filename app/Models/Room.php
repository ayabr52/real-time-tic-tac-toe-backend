<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'status', 'next_symbol'];

    public function moves()
    {
        return $this->hasMany(Move::class);
    }
}
