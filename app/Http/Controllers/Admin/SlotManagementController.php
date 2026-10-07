<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;

class SlotManagementController extends Controller
{
    public function index()
    {
        $slots = ParkingSlot::orderBy('slot_code')->get();
        return view('admin.slots.index', compact('slots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'slot_code' => 'required|string|unique:parking_slots,slot_code',
        ]);

        ParkingSlot::create([
            'slot_code' => strtoupper($request->slot_code),
            'is_occupied' => false,
            'is_active' => true,
        ]);

        return redirect()->back()->with('success', 'Slot parkir berhasil ditambahkan!');
    }

    public function toggleActive($id)
    {
        $slot = ParkingSlot::findOrFail($id);
        $slot->is_active = !$slot->is_active;
        $slot->save();

        return redirect()->back()->with('success', 'Status slot berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $slot = ParkingSlot::findOrFail($id);
        $slot->delete();

        return redirect()->back()->with('success', 'Slot parkir berhasil dihapus!');
    }
}