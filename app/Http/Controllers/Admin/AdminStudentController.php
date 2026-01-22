<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;
use App\Contracts\Services\NotificationServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminStudentController extends Controller
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository,
        private SectionRepositoryInterface $sectionRepository,
        private NotificationServiceInterface $notificationService
    ) {}

    public function index(Request $request)
    {
        if ($request->filled('search')) {
            $students = $this->studentRepository->searchPaginate($request->input('search'), 10);
        } else {
            $students = $this->studentRepository->with('section')->orderBy('last_name')->paginate(10);
        }

        $sections = $this->sectionRepository->all();

        return view('admin.manage_student', compact('students', 'sections'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'lrn' => 'required|numeric|unique:students,lrn|digits_between:10,12',
            'email' => 'required|email|unique:students,email',
            'section_id' => 'required|exists:sections,id',
        ]);

        $validated['password'] = Hash::make($request->lrn);

        $student = $this->studentRepository->create($validated);

        try {
            $this->notificationService->sendWelcomeEmail($student->id, 'student');
        } catch (\Exception $e) {
            \Log::error('Mail failed: ' . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Student registered to section successfully!');
    }

    public function update(Request $request, $id)
    {
        $student = $this->studentRepository->findOrFail($id);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:students,email,' . $student->id,
            'section_id' => 'required|exists:sections,id',
            'new_password' => 'nullable|min:6'
        ]);

        if ($request->filled('new_password')) {
            $validated['password'] = Hash::make($request->new_password);
        }

        $this->studentRepository->update($id, $validated);
        return redirect()->back()->with('success', 'Student record updated!');
    }

    public function destroy($id)
    {
        $this->studentRepository->delete($id);
        return redirect()->back()->with('success', 'Student deleted.');
    }
}
