<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: CreateRoomsTable
 *
 * Defines the schema structure for the 'rooms' table, managing active game rooms,
 * current player turns, and operational statuses for multiplayer sessions.
 *
 * @author IT. Aya
 */
return new class extends Migration
{
    /**
     * Run the database migrations.
     *
     * Creates the 'rooms' table with unique names, status tracking, and turn indicators.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();

            // Unique room identifier chosen by the host player
            $table->string('name')->unique();

            // Current match lifecycle state: 'waiting', 'playing', or 'finished'
            $table->string('status')->default('waiting');

            // Symbol designated for the upcoming move: 'X' or 'O'
            $table->string('next_symbol')->default('X');

            $table->timestamps();
        });
    }

    /**
     * Reverse the database migrations.
     *
     * Drops the 'rooms' table if it exists.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
