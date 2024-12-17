<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'query'    => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'source'   => 'nullable|string|max:255',
            'date'     => 'nullable|date',
            'author'   => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $query = Article::query();

        // Multi-value filters
        if ($request->has('source')) {
            $sources = explode(',', $request->source);
            $query->whereIn('source', $sources);
        }

        if ($request->has('category')) {
            $categories = explode(',', $request->category);
            $query->whereIn('category', $categories);
        }

        // Other filters
        if (!empty($validated['query'])) {
            $query->where(function ($q) use ($validated) {
                $q->where('title', 'LIKE', "%{$validated['query']}%")
                  ->orWhere('description', 'LIKE', "%{$validated['query']}%");
            });
        }

        if (!empty($validated['date'])) {
            $query->whereDate('published_at', $validated['date']);
        }
        if (!empty($validated['author'])) {
            $query->where('author', 'LIKE', "%{$validated['author']}%");
        }

        if ($request->has('sort')) {
            $sort = $request->sort;
            if ($sort === 'latest') {
                $query->orderBy('published_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('published_at', 'asc');
            }
        }

        $perPage = $validated['per_page'] ?? 10;
        $articles = $query->paginate($perPage);

        return response()->json($articles);
    }
}
