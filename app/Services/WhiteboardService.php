<?php

namespace App\Services;

use App\Models\VideoConference;
use App\Models\Whiteboard;
use App\Models\WhiteboardElement;
use Illuminate\Support\Facades\Storage;

class WhiteboardService
{
    /**
     * Create a new whiteboard for a conference.
     */
    public function create(VideoConference $conference, array $data = []): Whiteboard
    {
        return Whiteboard::create([
            'conference_id' => $conference->id,
            'title' => $data['title'] ?? 'Whiteboard - ' . now()->format('Y-m-d H:i'),
            'session_data' => $data['session_data'] ?? [],
            'created_by_type' => $data['created_by_type'] ?? null,
            'created_by_id' => $data['created_by_id'] ?? null,
        ]);
    }

    /**
     * Add an element to a whiteboard.
     */
    public function addElement(Whiteboard $whiteboard, array $elementData): WhiteboardElement
    {
        return WhiteboardElement::create([
            'whiteboard_id' => $whiteboard->id,
            'type' => $elementData['type'],
            'data' => $elementData['data'] ?? [],
            'layer' => $elementData['layer'] ?? $whiteboard->elements()->max('layer') + 1,
            'created_by_type' => $elementData['created_by_type'] ?? null,
            'created_by_id' => $elementData['created_by_id'] ?? null,
        ]);
    }

    /**
     * Remove an element from a whiteboard.
     */
    public function removeElement(WhiteboardElement $element): void
    {
        $element->delete();
    }

    /**
     * Clear all elements from a whiteboard.
     */
    public function clearWhiteboard(Whiteboard $whiteboard): void
    {
        $whiteboard->elements()->delete();

        $whiteboard->update(['session_data' => []]);
    }

    /**
     * Get the full state of a whiteboard including all elements.
     */
    public function getWhiteboardState(Whiteboard $whiteboard): array
    {
        $elements = $whiteboard->elements()
            ->orderBy('layer')
            ->get();

        return [
            'id' => $whiteboard->id,
            'title' => $whiteboard->title,
            'session_data' => $whiteboard->session_data,
            'elements' => $elements->map(fn (WhiteboardElement $el) => [
                'id' => $el->id,
                'type' => $el->type,
                'data' => $el->data,
                'layer' => $el->layer,
            ])->toArray(),
            'element_count' => $elements->count(),
        ];
    }

    /**
     * Export the whiteboard state as a JSON-based image descriptor for rendering.
     */
    public function exportAsImage(Whiteboard $whiteboard): array
    {
        $state = $this->getWhiteboardState($whiteboard);

        $exportPath = "whiteboards/export-{$whiteboard->id}-" . now()->timestamp . '.json';
        Storage::disk('local')->put($exportPath, json_encode($state));

        return [
            'whiteboard_id' => $whiteboard->id,
            'export_path' => $exportPath,
            'element_count' => $state['element_count'],
            'exported_at' => now()->toIso8601String(),
        ];
    }
}
