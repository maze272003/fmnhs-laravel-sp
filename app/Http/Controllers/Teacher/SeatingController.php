<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\SeatingArrangement;
use App\Models\Section;
use App\Services\SeatingOptimizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SeatingController extends Controller
{
    public function __construct(
        private readonly SeatingOptimizationService $seatingService,
    ) {}

    /**
     * List seating arrangements.
     */
    public function index(): View
    {
        $teacherId = Auth::guard('teacher')->id();

        $arrangements = SeatingArrangement::where('teacher_id', $teacherId)
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('teacher.seating.index', compact('arrangements'));
    }

    /**
     * Store a new seating arrangement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'section_id' => ['required', 'exists:sections,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
        ]);

        try {
            $section = Section::findOrFail($validated['section_id']);
            $room = ! empty($validated['room_id']) ? Room::find($validated['room_id']) : null;

            $arrangement = $this->seatingService->createArrangement($section, $room);

            return redirect()
                ->route('teacher.seating.show', $arrangement)
                ->with('success', 'Seating arrangement created successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to create arrangement: '.$e->getMessage());
        }
    }

    /**
     * Show a seating arrangement.
     */
    public function show(SeatingArrangement $arrangement): View
    {
        $arrangement->load('seats');

        return view('teacher.seating.show', compact('arrangement'));
    }

    /**
     * Optimize a seating arrangement using the service.
     */
    public function optimize(SeatingArrangement $arrangement): RedirectResponse
    {
        try {
            $this->seatingService->optimizeSeating($arrangement);

            return redirect()
                ->route('teacher.seating.show', $arrangement)
                ->with('success', 'Seating arrangement optimized successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Optimization failed: '.$e->getMessage());
        }
    }

    /**
     * Print a seating arrangement.
     */
    public function print(SeatingArrangement $arrangement): View
    {
        $arrangement->load('seats');

        return view('teacher.seating.print', compact('arrangement'));
    }
}
