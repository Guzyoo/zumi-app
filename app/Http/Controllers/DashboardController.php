<?php

namespace App\Http\Controllers;

use App\Models\ParkingSlot;

class DashboardController extends Controller
{
    public function index()
    {
        $slots = ParkingSlot::orderBy('slot_code')->get();
        $totalAvailable = ParkingSlot::where('is_occupied', false)->count();

        return view('dashboard', compact('slots', 'totalAvailable'));
    }
}