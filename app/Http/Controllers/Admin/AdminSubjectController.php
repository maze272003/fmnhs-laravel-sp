<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class AdminSubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('code')->paginate(10);
        return view('admin.subject', compact('subjects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:subjects,code|max:20', // e.g., MATH-101
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        Subject::create($validated);

        return redirect()->back()->with('success', 'Subject created successfully!');
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|max:20|unique:subjects,code,'.$subject->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $subject->update($validated);

        return redirect()->back()->with('success', 'Subject updated successfully!');
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete(); // This will cascade delete grades associated with it!

        return redirect()->back()->with('success', 'Subject deleted successfully!');
    }
}