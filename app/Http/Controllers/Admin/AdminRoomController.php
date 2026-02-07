<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RoomManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminRoomController extends Controller
{
    public function __construct(private readonly RoomManagementService $roomManagement)
    {
    }

    public function index(Request $request)
    {
        $rooms = $this->roomManagement->list($request->input('search'));
        return view('admin.rooms', compact('rooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name',
            'building' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $admin = Auth::guard('admin')->user();
        $this->roomManagement->create($validated, $admin);

        return redirect()->back()->with('success', 'Room created successfully!');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:rooms,name,' . (int) $id,
            'building' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'is_available' => 'required|boolean',
        ]);

        $this->roomManagement->update((int) $id, $validated);

        return redirect()->back()->with('success', 'Room updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $this->roomManagement->delete((int) $id);
        } catch (ValidationException $exception) {
            return redirect()->back()->withErrors($exception->errors());
        }

        return redirect()->back()->with('success', 'Room deleted successfully!');
    }
}
