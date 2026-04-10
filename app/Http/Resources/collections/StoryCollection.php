<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StoryCollection extends ResourceCollection
{
    public function toResponse($request)
    {
        return response()->json([
            'data' => StoryResource::collection($this->collection),
            'meta' => [
                'total' => $this->collection->count(),
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ]
            ]
        ]);
    }
}
