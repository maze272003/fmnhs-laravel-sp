<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Services\PortfolioService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentPortfolioController extends Controller
{
    public function __construct(
        private readonly PortfolioService $portfolioService,
    ) {}

    /**
     * List student's portfolios.
     */
    public function index(): View
    {
        $studentId = Auth::guard('student')->id();

        $portfolios = Portfolio::where('student_id', $studentId)
            ->withCount('items')
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('student.portfolios.index', compact('portfolios'));
    }

    /**
     * Show create form.
     */
    public function create(): View
    {
        return view('student.portfolios.create');
    }

    /**
     * Show a portfolio.
     */
    public function show(Portfolio $portfolio): View
    {
        $portfolio->load('items');

        return view('student.portfolios.show', compact('portfolio'));
    }

    /**
     * Add an item to a portfolio.
     */
    public function addItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'portfolio_id' => ['required', 'exists:portfolios,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
        ]);

        $portfolio = Portfolio::findOrFail($validated['portfolio_id']);

        try {
            $this->portfolioService->addItem($portfolio, $validated);

            return redirect()
                ->route('student.portfolio.index')
                ->with('success', 'Item added to portfolio.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to add item: '.$e->getMessage());
        }
    }

    /**
     * Remove an item from a portfolio.
     */
    public function removeItem(PortfolioItem $item): RedirectResponse
    {
        $portfolioId = $item->portfolio_id;

        try {
            $this->portfolioService->removeItem($item);

            return redirect()
                ->route('student.portfolio.index')
                ->with('success', 'Item removed from portfolio.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to remove item: '.$e->getMessage());
        }
    }

    /**
     * Store a new portfolio item.
     */
    public function storeItem(Request $request): RedirectResponse
    {
        return $this->addItem($request);
    }

    /**
     * Update a portfolio item.
     */
    public function updateItem(Request $request, PortfolioItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
        ]);

        try {
            $item->update($validated);

            return redirect()
                ->route('student.portfolio.index')
                ->with('success', 'Item updated successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to update item: '.$e->getMessage());
        }
    }

    /**
     * Destroy a portfolio item.
     */
    public function destroyItem(PortfolioItem $item): RedirectResponse
    {
        return $this->removeItem($item);
    }

    /**
     * Store a reflection.
     */
    public function storeReflection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $student = Auth::guard('student')->user();

        try {
            $this->portfolioService->addReflection($student, $validated);

            return redirect()
                ->back()
                ->with('success', 'Reflection added successfully.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to add reflection: '.$e->getMessage());
        }
    }
}
