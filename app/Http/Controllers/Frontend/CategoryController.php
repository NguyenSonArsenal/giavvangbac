<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('status', 1)
            ->withCount(['posts' => function ($q) {
                $q->where('status', 1);
            }])
            ->orderBy('name')
            ->get();

        return view('frontend.category.index', compact('categories'));
    }

    public function show($slug)
    {
        $category = Category::where('slug', $slug)->where('status', 1)->firstOrFail();

        $posts = Post::active()
            ->where('category_id', $category->id)
            ->with('category')
            ->latest()
            ->paginate(12);

        return view('frontend.post.index', compact('posts', 'category'));
    }
}
