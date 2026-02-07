<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYearConfig;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminSchoolYearController extends Controller
{
    public function index()
    {
        $schoolYears = SchoolYearConfig::orderBy('school_year', 'desc')->paginate(10);
        return view('admin.school_year', compact('schoolYears'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_year' => 'required|string|max:20|unique:school_year_configs,school_year',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $validated['status'] = 'upcoming';
        $validated['is_active'] = false;

        $config = SchoolYearConfig::create($validated);

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'SchoolYearConfig', $config->id, 'created',
            null, null, $config->toArray(),
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', 'School year created successfully!');
    }

    public function activate($id)
    {
        $config = SchoolYearConfig::findOrFail($id);
        $config->activate();

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'SchoolYearConfig', $config->id, 'updated',
            'status', 'upcoming', 'active',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', "School year {$config->school_year} activated!");
    }

    public function close($id)
    {
        $config = SchoolYearConfig::findOrFail($id);
        $config->close();

        $admin = Auth::guard('admin')->user();
        AuditTrail::log(
            'SchoolYearConfig', $config->id, 'updated',
            'status', 'active', 'closed',
            'admin', $admin->id ?? null, $admin->name ?? 'Admin'
        );

        return redirect()->back()->with('success', "School year {$config->school_year} closed.");
    }
}
