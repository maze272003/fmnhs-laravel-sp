<?php

namespace App\Services;

use App\Models\Portfolio;
use App\Models\PortfolioItem;
use App\Models\Reflection;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class PortfolioService
{
    /**
     * Create a portfolio for a student.
     */
    public function create(Student $student, array $data): Portfolio
    {
        return Portfolio::create([
            'student_id' => $student->id,
            'title' => $data['title'] ?? "{$student->first_name}'s Portfolio",
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? false,
        ]);
    }

    /**
     * Add an item to a portfolio.
     */
    public function addItem(Portfolio $portfolio, array $item): PortfolioItem
    {
        return PortfolioItem::create([
            'portfolio_id' => $portfolio->id,
            'title' => $item['title'],
            'type' => $item['type'] ?? 'document',
            'file_path' => $item['file_path'] ?? null,
            'url' => $item['url'] ?? null,
            'description' => $item['description'] ?? null,
        ]);
    }

    /**
     * Remove an item from a portfolio.
     */
    public function removeItem(PortfolioItem $item): void
    {
        if ($item->file_path && Storage::exists($item->file_path)) {
            Storage::delete($item->file_path);
        }

        $item->delete();
    }

    /**
     * Add a reflection entry for a student.
     */
    public function addReflection(Student $student, array $data): Reflection
    {
        return Reflection::create([
            'student_id' => $student->id,
            'portfolio_item_id' => $data['portfolio_item_id'] ?? null,
            'title' => $data['title'],
            'content' => $data['content'],
        ]);
    }

    /**
     * Export a portfolio as a text-based report (PDF placeholder).
     */
    public function exportPDF(Portfolio $portfolio): string
    {
        $portfolio->load(['items', 'student']);

        $content = "PORTFOLIO: {$portfolio->title}\n";
        $content .= str_repeat('=', 40) . "\n";
        $content .= "Student: {$portfolio->student->first_name} {$portfolio->student->last_name}\n";
        $content .= "Description: {$portfolio->description}\n\n";

        $content .= "ITEMS:\n";
        foreach ($portfolio->items as $index => $item) {
            $content .= ($index + 1) . ". {$item->title} ({$item->type})\n";
            if ($item->description) {
                $content .= "   {$item->description}\n";
            }
        }

        $path = "portfolios/export-{$portfolio->id}-" . now()->timestamp . '.txt';
        Storage::disk('local')->put($path, $content);

        return $path;
    }

    /**
     * Get a public portfolio with its items.
     */
    public function getPublicPortfolio(Portfolio $portfolio): ?array
    {
        if (!$portfolio->is_public) {
            return null;
        }

        $portfolio->load(['items', 'student']);

        return [
            'id' => $portfolio->id,
            'title' => $portfolio->title,
            'description' => $portfolio->description,
            'student_name' => "{$portfolio->student->first_name} {$portfolio->student->last_name}",
            'items' => $portfolio->items->map(fn (PortfolioItem $i) => [
                'id' => $i->id,
                'title' => $i->title,
                'type' => $i->type,
                'description' => $i->description,
                'url' => $i->url,
            ])->toArray(),
            'item_count' => $portfolio->items->count(),
        ];
    }
}
