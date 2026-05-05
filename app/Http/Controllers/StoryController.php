<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Story;
use App\Http\Resources\Collections\StoryCollection;

class StoryController extends Controller
{
    public function __invoke(Request $request)
    {
        $categories = Story::query()
            ->when($request->has('search'), function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%');
            })
            ->when($request->has('date_from'), function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->date_from);
            })
            ->when($request->has('date_to'), function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->date_to);
            })
            ->when($request->has('category'), function ($q) use ($request) {
                $q->where('category_id', '=', $request->category);
            })->paginate($request->input('limit', 20));

        return new StoryCollection($categories);
    }
}
