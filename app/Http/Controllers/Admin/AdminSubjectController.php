<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminSubjectController extends Controller
{
    public function __construct(
        private SubjectRepositoryInterface $subjectRepository
    ) {}

    public function index(Request $request): View
    {
        $viewArchived = $request->has('archived');

        if ($viewArchived) {
            $subjects = $this->subjectRepository->getArchivedPaginate(10);
        } else {
            $subjects = $this->subjectRepository->orderBy('name')->paginate(10);
        }

        return view('admin.subject', compact('subjects', 'viewArchived'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|unique:subjects,code|max:20|string|uppercase',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $this->subjectRepository->create($validated);

        return redirect()->back()->with('success', 'New subject has been added to the curriculum!');
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $subject = $this->subjectRepository->findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|max:20|string|uppercase|unique:subjects,code,' . $subject->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $this->subjectRepository->update($id, $validated);

        return redirect()->back()->with('success', 'Subject information has been successfully updated!');
    }

    public function archive($id): RedirectResponse
    {
        $this->subjectRepository->delete($id);
        return redirect()->back()->with('success', 'Subject has been moved to archive.');
    }

    public function restore($id): RedirectResponse
    {
        $this->subjectRepository->restore($id);
        return redirect()->back()->with('success', 'Subject has been restored successfully.');
    }
}
