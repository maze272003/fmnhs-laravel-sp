<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VideoConference;
use App\Models\Whiteboard;
use App\Models\WhiteboardElement;
use App\Services\WhiteboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WhiteboardApiController extends Controller
{
    public function __construct(
        private readonly WhiteboardService $whiteboardService,
    ) {}

    /**
     * Create a new whiteboard for a conference.
     */
    public function store(Request $request, VideoConference $conference): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $whiteboard = $this->whiteboardService->create($conference, $validated);

            return response()->json($whiteboard, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Save a whiteboard (delegates to store).
     */
    public function save(Request $request, VideoConference $conference): JsonResponse
    {
        return $this->store($request, $conference);
    }

    /**
     * Load a whiteboard (delegates to show).
     */
    public function load(Whiteboard $whiteboard): JsonResponse
    {
        return $this->show($whiteboard);
    }

    /**
     * Get a whiteboard with its elements.
     */
    public function show(Whiteboard $whiteboard): JsonResponse
    {
        $whiteboard->load('elements');

        return response()->json($whiteboard);
    }

    /**
     * Add an element to the whiteboard.
     */
    public function addElement(Request $request, Whiteboard $whiteboard): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:path,text,shape,image,line'],
            'data' => ['required', 'array'],
            'position_x' => ['nullable', 'numeric'],
            'position_y' => ['nullable', 'numeric'],
        ]);

        try {
            $element = $this->whiteboardService->addElement($whiteboard, $validated);

            return response()->json($element, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove an element from the whiteboard.
     */
    public function removeElement(Whiteboard $whiteboard, WhiteboardElement $element): JsonResponse
    {
        try {
            $this->whiteboardService->removeElement($element);

            return response()->json(['message' => 'Element removed.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Clear all elements from the whiteboard.
     */
    public function clear(Whiteboard $whiteboard): JsonResponse
    {
        try {
            $this->whiteboardService->clearWhiteboard($whiteboard);

            return response()->json(['message' => 'Whiteboard cleared.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /**
     * Export the whiteboard as an image or PDF.
     */
    public function export(Request $request, Whiteboard $whiteboard): JsonResponse
    {
        try {
            $result = $this->whiteboardService->exportAsImage($whiteboard);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
