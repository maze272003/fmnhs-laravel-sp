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
    public function index(Request $request)
{
    // I-check kung ang user ay nasa 'archived' view base sa URL parameter (?archived=1)
    $viewArchived = $request->has('archived');

    // Kunin ang iyong data (halimbawa)
    $subjects = Subject::when($viewArchived, function ($query) {
            return $query->onlyTrashed();
        })->paginate(10);

    // SIGURADUHIN na kasama ang 'viewArchived' dito:
    return view('admin.subject', compact('subjects', 'viewArchived'));
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
    public function archive(Subject $subject): RedirectResponse
    {
        $subject->delete(); // Dahil sa SoftDeletes, ma-aarchive lang ito
        return redirect()->back()->with('success', 'Subject has been moved to archive.');
    }

    // Function para ibalik ang inarchive
    public function restore($id): RedirectResponse
    {
        $subject = Subject::onlyTrashed()->findOrFail($id);
        $subject->restore();
        return redirect()->back()->with('success', 'Subject has been restored successfully.');
    }
}