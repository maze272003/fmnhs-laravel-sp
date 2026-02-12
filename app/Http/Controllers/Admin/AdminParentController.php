<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminParentController extends Controller
{
    /**
     * List all parents.
     */
    public function index(): View
    {
        $parents = ParentModel::orderBy('name')
            ->paginate(20);

        return view('admin.parents.index', compact('parents'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('admin.parents.create');
    }

    /**
     * Store a new parent.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:parents,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validated['password'] = bcrypt($validated['password']);

        ParentModel::create($validated);

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'Parent created successfully.');
    }

    /**
     * Show a parent.
     */
    public function show(ParentModel $parent): View
    {
        $parent->load('students');

        return view('admin.parents.show', compact('parent'));
    }

    /**
     * Show edit form.
     */
    public function edit(ParentModel $parent): View
    {
        return view('admin.parents.edit', compact('parent'));
    }

    /**
     * Update a parent.
     */
    public function update(Request $request, ParentModel $parent): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:parents,email,'.$parent->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }

        $parent->update($validated);

        return redirect()
            ->route('admin.parents.show', $parent)
            ->with('success', 'Parent updated successfully.');
    }

    /**
     * Delete a parent.
     */
    public function destroy(ParentModel $parent): RedirectResponse
    {
        $parent->delete();

        return redirect()
            ->route('admin.parents.index')
            ->with('success', 'Parent deleted successfully.');
    }
}
