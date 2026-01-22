<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\Repositories\ScheduleRepositoryInterface;
use App\Contracts\Repositories\SubjectRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\SectionRepositoryInterface;

class AdminScheduleController extends Controller
{
    public function __construct(
        private ScheduleRepositoryInterface $scheduleRepository,
        private SubjectRepositoryInterface $subjectRepository,
        private TeacherRepositoryInterface $teacherRepository,
        private SectionRepositoryInterface $sectionRepository
    ) {}

    public function index()
    {
        $subjects = $this->subjectRepository->all();
        $teachers = $this->teacherRepository->all();
        $sections = $this->sectionRepository->all();

        $schedules = $this->scheduleRepository->with(['subject', 'teacher', 'section'])
            ->orderBy('day')
            ->orderBy('start_time')
            ->paginate(10);

        return view('admin.schedule', compact('subjects', 'teachers', 'sections', 'schedules'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'section_id' => 'required|exists:sections,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
            'day'        => 'required|string',
            'start_time'=> 'required|date_format:H:i', 
            'end_time'  => 'required|date_format:H:i|after:start_time',
            'room'      => 'required|string'
        ]);

        $this->scheduleRepository->create($request->all());
        
        return back()->with('success', 'Schedule Added Successfully!');
    }

    public function destroy($id)
    {
        $this->scheduleRepository->delete($id);
        return back()->with('success', 'Schedule Deleted!');
    }
}
