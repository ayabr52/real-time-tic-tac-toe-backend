<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Events\MovePlayed;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * Class GameController
 *
 * Handles game room lifecycle, player actions, game board state retrieval,
 * real-time event broadcasting via Laravel Reverb, and room cleanup.
 *
 * @package App\Http\Controllers
 * @author IT. Aya
 */
class GameController extends Controller
{
    /**
     * Creates a new game room or resets an existing room with the same name.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createRoom(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|min:2|max:30',
        ]);

        // If the room already exists, remove it along with its moves to free up the name
        $oldRoom = Room::where('name', $request->name)->first();
        if ($oldRoom) {
            $oldRoom->moves()->delete();
            $oldRoom->delete();
        }

        // Initialize new room instance
        $room = Room::create([
            'name'        => $request->name,
            'status'      => 'waiting',
            'next_symbol' => 'X',
        ]);

        return response()->json([
            'room_name' => $room->name,
        ]);
    }

    /**
     * Joins an existing game room and updates its status to active.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function joinRoom(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|exists:rooms,name',
        ]);

        $room = Room::where('name', $request->name)->first();

        // Transition room state from waiting to playing upon second player join
        if ($room->status === 'waiting') {
            $room->update(['status' => 'playing']);
        }

        return response()->json([
            'room_name' => $room->name,
            'status'    => $room->status,
        ]);
    }

    /**
     * Fetches the current 9-cell board state and current turn for a given room.
     *
     * @param string $name Room unique identifier name
     * @return JsonResponse
     */
    public function getRoomState(string $name): JsonResponse
    {
        $room = Room::where('name', $name)->firstOrFail();

        // Initialize empty 9-cell grid
        $squares = array_fill(0, 9, null);

        // Populate grid with played moves
        foreach ($room->moves as $move) {
            $squares[$move->cell] = $move->symbol;
        }

        return response()->json([
            'room_name'   => $room->name,
            'status'      => $room->status,
            'next_symbol' => $room->next_symbol,
            'squares'     => $squares,
        ]);
    }

    /**
     * Validates and records a player's move, updates turn symbol, and broadcasts the WebSocket event.
     *
     * @param Request $request
     * @param string $name Room unique identifier name
     * @return JsonResponse
     */
    public function playMove(Request $request, string $name): JsonResponse
    {
        Log::info("PLAYMOVE CALLED", [
            'room'   => $name,
            'cell'   => $request->cell,
            'symbol' => $request->symbol
        ]);

        $request->validate([
            'cell'   => 'required|integer|min:0|max:8',
            'symbol' => 'required|string|in:X,O',
        ]);

        $room = Room::where('name', $name)->firstOrFail();

        // Prevent overwriting an already occupied cell
        if ($room->moves()->where('cell', $request->cell)->exists()) {
            Log::info("CELL ALREADY TAKEN");
            return response()->json(['error' => 'Cell already taken'], 400);
        }

        // Persist move record
        $move = $room->moves()->create([
            'cell'   => $request->cell,
            'symbol' => $request->symbol,
        ]);

        // Toggle turn symbol
        $room->update([
            'next_symbol' => $request->symbol === 'X' ? 'O' : 'X',
        ]);

        Log::info("ABOUT TO FIRE EVENT");

        // Broadcast move event to all other clients subscribed to the channel
        broadcast(new MovePlayed(
            $room->name,
            $request->cell,
            $request->symbol,
            $room->next_symbol
        ))->toOthers();

        Log::info("EVENT FIRED SUCCESSFULLY");

        return response()->json([
            'success' => true,
            'cell'    => $request->cell,
            'symbol'  => $request->symbol,
        ]);
    }

    /**
     * Alternative method to fetch room details and formatted grid array.
     *
     * @param string $name
     * @return JsonResponse
     */
    public function showRoom(string $name): JsonResponse
    {
        $room = Room::where('name', $name)->firstOrFail();

        // Retrieve played moves chronologically
        $moves = $room->moves()->orderBy('id')->get();

        // Construct game grid
        $squares = array_fill(0, 9, null);
        foreach ($moves as $move) {
            $squares[$move->cell] = $move->symbol;
        }

        return response()->json([
            'room_name'   => $room->name,
            'squares'     => $squares,
            'next_symbol' => $room->next_symbol,
        ]);
    }

    /**
     * Clears all moves in a room and broadcasts a reset signal to active players.
     *
     * @param string $name
     * @return JsonResponse
     */
    public function resetRoom(string $name): JsonResponse
    {
        $room = Room::where('name', $name)->firstOrFail();

        // Wipe all existing move records
        $room->moves()->delete();

        // Reset turn indicator back to 'X'
        $room->update([
            'next_symbol' => 'X',
            'status'      => 'playing',
        ]);

        // Broadcast reset event using flag values (-1 and 'RESET')
        broadcast(new MovePlayed(
            $room->name,
            -1,
            'RESET',
            'X'
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    /**
     * Permanently deletes a room and its dependent moves from the database.
     *
     * @param string $name
     * @return JsonResponse
     */
    public function deleteRoom(string $name): JsonResponse
    {
        $room = Room::where('name', $name)->first();

        if ($room) {
            // Delete child moves first to preserve database referential integrity
            $room->moves()->delete();
            $room->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully'
        ]);
    }
}
