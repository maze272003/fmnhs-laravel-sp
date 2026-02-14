<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Services\SubjectManagementService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSubjectController extends Controller
{
    public function __construct(private readonly SubjectManagementService $subjectManagement)
    {
    }

    /**
     * Display a listing of the subjects.
     */
    public function index(Request $request)
    {
        return view('admin.subject', $this->subjectManagement->getAdminSubjects($request->has('archived')));
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

        $this->subjectManagement->create($validated);

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

        $this->subjectManagement->update($subject, $validated);

        return redirect()->back()->with('success', 'Subject information has been successfully updated!');
    }

    /**
     * Remove the specified subject from storage.
     */
    public function archive(Subject $subject): RedirectResponse
    {
        $this->subjectManagement->archive($subject);
        return redirect()->back()->with('success', 'Subject has been moved to archive.');
    }

    // Function para ibalik ang inarchive
    public function restore($id): RedirectResponse
    {
        $this->subjectManagement->restore((int) $id);
        return redirect()->back()->with('success', 'Subject has been restored successfully.');
    }

    /**
     * Delete the specified subject (soft delete).
     */
    public function destroy($id): RedirectResponse
    {
        try {
            $this->subjectManagement->delete((int) $id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to delete subject.');
        }

        return redirect()->back()->with('success', 'Subject has been deleted successfully.');
    }

    /**
     * Permanently delete the specified subject.
     */
    public function forceDelete($id): RedirectResponse
    {
        try {
            $this->subjectManagement->forceDelete((int) $id);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to permanently delete subject.');
        }

        return redirect()->back()->with('success', 'Subject has been permanently deleted.');
    }
}
