<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Services\PortfolioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortfolioApiController extends Controller
{
    public function __construct(
        private readonly PortfolioService $portfolioService,
    ) {}

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
            'file' => ['nullable', 'file', 'max:20480'],
            'url' => ['nullable', 'url'],
        ]);

        try {
            $item = $this->portfolioService->addItem(
                $portfolio,
                $validated,
                $request->file('file')
            );

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

        try {
            $reflection = $this->portfolioService->addReflection($validated);

            return response()->json($reflection, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Export a portfolio.
     */
    public function export(Request $request, Portfolio $portfolio): JsonResponse
    {
        $validated = $request->validate([
            'format' => ['sometimes', 'string', 'in:pdf,html,json'],
        ]);

        try {
            $result = $this->portfolioService->exportPortfolio(
                $portfolio,
                $validated['format'] ?? 'pdf'
            );

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
