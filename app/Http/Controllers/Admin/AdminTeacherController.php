<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminTeacherController extends Controller
{
    public function __construct(
        private TeacherRepositoryInterface $teacherRepository
    ) {}

    public function index(Request $request)
    {
        $search = $request->input('search');
        $viewArchived = $request->has('archived');

        if ($viewArchived) {
            $teachers = $search
                ? $this->teacherRepository->searchArchivedPaginate($search, 10)
                : $this->teacherRepository->getArchivedPaginate(10);
        } else {
            $teachers = $search
                ? $this->teacherRepository->searchPaginate($search, 10)
                : $this->teacherRepository->with('advisorySection')->orderBy('last_name')->paginate(10);
        }

        $teachers->withQueryString();

        return view('admin.manage_teacher', compact('teachers', 'viewArchived'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|unique:teachers,employee_id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email',
            'department' => 'required|string',
        ]);

        $validated['password'] = Hash::make('password');

        $this->teacherRepository->create($validated);

        return back()->with('success', 'New faculty member added successfully!');
    }

    public function update(Request $request, $id)
    {
        $teacher = $this->teacherRepository->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:teachers,email,' . $teacher->id,
            'department' => 'required|string',
        ]);

        $this->teacherRepository->update($id, $validated);
        return back()->with('success', 'Faculty profile updated successfully!');
    }

    public function archive($id)
    {
        $this->teacherRepository->delete($id);
        return back()->with('success', 'Faculty member moved to archive.');
    }

    public function restore($id)
    {
        $this->teacherRepository->restore($id);
        return back()->with('success', 'Faculty access restored successfully!');
    }
}
