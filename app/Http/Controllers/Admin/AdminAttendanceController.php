<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;

class AdminAttendanceController extends Controller
{
    public function __construct(
        private AttendanceRepositoryInterface $attendanceRepository,
        private TeacherRepositoryInterface $teacherRepository,
        private SectionRepositoryInterface $sectionRepository
    ) {}

    public function index(Request $request)
    {
        $teachers = $this->teacherRepository->orderBy('last_name')->all();
        $sections = $this->sectionRepository->orderBy('grade_level')->all();

        $records = $this->attendanceRepository->with(['student', 'teacher', 'subject', 'section']);

        if ($request->filled('date')) $records->where('date', $request->date);
        if ($request->filled('teacher_id')) $records->where('teacher_id', $request->teacher_id);
        if ($request->filled('section_id')) $records->where('section_id', $request->section_id);
        if ($request->filled('status')) $records->where('status', $request->status);

        $records = $records->orderBy('date', 'desc')->paginate(20)->withQueryString();

        return view('admin.attendancelogs', compact('records', 'teachers', 'sections'));
    }
}
