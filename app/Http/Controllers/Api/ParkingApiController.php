<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use App\Events\ParkingSlotUpdated;
use Illuminate\Http\Request;

class ParkingApiController extends Controller
{
    public function updateSlots(Request $request)
    {
        $validated = $request->validate([
            'slots' => 'required|array',
            'slots.*.slot_code' => 'required|string',
            'slots.*.is_occupied' => 'required|boolean',
        ]);

        $updatedSlots = [];

        foreach ($validated['slots'] as $slotData) {
            $slot = ParkingSlot::updateOrCreate(
                ['slot_code' => $slotData['slot_code']],
                [
                    'is_occupied' => $slotData['is_occupied'],
                    'last_updated_at' => now(),
                ]
            );

            $updatedSlots[] = [
                'slot_code' => $slot->slot_code,
                'is_occupied' => $slot->is_occupied,
            ];
        }

        $totalAvailable = ParkingSlot::where('is_occupied', false)->count();

        // Broadcast data ke Reverb WebSocket
        broadcast(new ParkingSlotUpdated($updatedSlots, $totalAvailable));

        return response()->json([
            'message' => 'Parking status updated successfully',
            'total_available' => $totalAvailable,
        ]);
    }
}