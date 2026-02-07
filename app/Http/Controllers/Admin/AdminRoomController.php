<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = Room::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('building', 'LIKE', "%{$search}%");
            });
        }

        $rooms = $query->orderBy('building')->orderBy('name')->paginate(10);
        return view('admin.rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name',
            'building' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $validated['is_available'] = true;

        $room = Room::create($validated);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'Room', $room->id, 'created',
            null, null, $room->toArray(),
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', 'Room created successfully!');
    }

    public function update(Request $request, $id)
    {
        $room = Room::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name,' . $room->id,
            'building' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'is_available' => 'required|boolean',
        ]);

        $room->update($validated);

        return redirect()->back()->with('success', 'Room updated successfully!');
    }

    public function destroy($id)
    {
        $room = Room::findOrFail($id);

        if ($room->schedules()->exists()) {
            return redirect()->back()->withErrors(['error' => 'Cannot delete room with active schedules.']);
        }

        $room->delete();

        return redirect()->back()->with('success', 'Room deleted successfully!');
    }
}
