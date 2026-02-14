<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\Student;
use App\Services\PortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PortfolioApiController extends Controller
{
    public function __construct(
        private readonly PortfolioService $portfolioService,
    ) {}

    /**
     * List the authenticated user's portfolio.
     */
    public function index(): JsonResponse
    {
        $student = Student::findOrFail(Auth::guard('student')->id());
        $portfolio = Portfolio::where('student_id', $student->id)->firstOrFail();
        $portfolio->load('items');

        return response()->json($portfolio);
    }

    /**
     * Store an item in a portfolio (delegates to addItem).
     */
    public function storeItem(Request $request, Portfolio $portfolio): JsonResponse
    {
        return $this->addItem($request, $portfolio);
    }

    /**
     * Show a portfolio with its items.
     */
    public function show(Portfolio $portfolio): JsonResponse
    {
        $portfolio->load('items');

        return response()->json($portfolio);
    }

    /**
     * Add an item to a portfolio.
     */
    public function addItem(Request $request, Portfolio $portfolio): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'string'],
            'url' => ['nullable', 'url'],
        ]);

        try {
            $item = $this->portfolioService->addItem($portfolio, $validated);

            return response()->json($item, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove an item from a portfolio.
     */
    public function removeItem(PortfolioItem $item): JsonResponse
    {
        try {
            $this->portfolioService->removeItem($item);

            return response()->json(['message' => 'Item removed.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Add a reflection entry to a portfolio.
     */
    public function addReflection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'portfolio_id' => ['required', 'exists:portfolios,id'],
            'content' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
        ]);

        $student = Student::findOrFail(Auth::guard('student')->id());

        try {
            $reflection = $this->portfolioService->addReflection($student, $validated);

            return response()->json($reflection, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Export a portfolio.
     */
    public function export(Portfolio $portfolio): JsonResponse
    {
        try {
            $path = $this->portfolioService->exportPDF($portfolio);

            return response()->json(['path' => $path]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
