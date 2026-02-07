<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\SchoolYearRepositoryInterface;
use App\Services\SchoolYearManagementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSchoolYearController extends Controller
{
    public function __construct(
        private readonly SchoolYearRepositoryInterface $schoolYears,
        private readonly SchoolYearManagementService $schoolYearManagement
    ) {
    }

    public function index()
    {
        $schoolYears = $this->schoolYears->paginateDesc(10);
        return view('admin.school_year', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20|unique:school_year_configs,school_year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $admin = Auth::guard('admin')->user();
        $this->schoolYearManagement->create($validated, $admin);

        return redirect()->back()->with('success', 'School year created successfully!');
    }

    public function activate($id)
    {
        $admin = Auth::guard('admin')->user();
        $config = $this->schoolYearManagement->activate((int) $id, $admin);

        return redirect()->back()->with('success', "School year {$config->school_year} activated!");
    }

    public function close($id)
    {
        $admin = Auth::guard('admin')->user();
        $config = $this->schoolYearManagement->close((int) $id, $admin);

        return redirect()->back()->with('success', "School year {$config->school_year} closed.");
    }
}
