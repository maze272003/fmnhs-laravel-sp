<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSubjectController extends Controller
{
    /**
     * Display a listing of the subjects.
     */
    public function index(): View
    {
        // Paginating by 10 and ordering by code for better organization
        $subjects = Subject::orderBy('code')->paginate(10);
        
        return view('admin.subject', compact('subjects'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|unique:subjects,code|max:20|string|uppercase', // e.g., MATH-101
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Subject::create($validated);

        return redirect()->back()->with('success', 'New subject has been added to the curriculum!');
    }

    /**
     * Update the specified subject in storage.
     * Ginamit natin ang Route Model Binding para mas malinis (Subject $subject)
     */
    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $validated = $request->validate([
            // Inignore natin ang ID ng current subject para hindi mag-error ang unique rule
            'code' => 'required|max:20|string|uppercase|unique:subjects,code,' . $subject->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $subject->update($validated);

        return redirect()->back()->with('success', 'Subject information has been successfully updated!');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        /**
         * Paalala: Dahil sa cascade delete sa migration, 
         * ang lahat ng grades, schedules, at assignments 
         * na nakalink sa subject na ito ay awtomatikong mabubura.
         */
        $subject->delete();

        return redirect()->back()->with('success', 'Subject and all associated records have been removed.');
    }
}