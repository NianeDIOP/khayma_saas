<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BlogPostController extends Controller
{
    public function index()
    {
        $posts = BlogPost::with('author:id,name')
            ->orderByDesc('created_at')
            ->get();

        return inertia('Admin/BlogPosts/Index', compact('posts'));
    }

    public function create()
    {
        return inertia('Admin/BlogPosts/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'excerpt'      => 'nullable|string|max:1000',
            'body'         => 'required|string|max:100000',
            'category'     => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['author_id'] = Auth::id();
        $validated['slug'] = BlogPost::generateSlug($validated['title']);
        $validated['is_published'] = $validated['is_published'] ?? false;

        if ($validated['is_published']) {
            $validated['published_at'] = now();
        }

        BlogPost::create($validated);

        return redirect()->route('admin.blog-posts.index')->with('success', 'Article créé.');
    }

    public function edit(BlogPost $blogPost)
    {
        return inertia('Admin/BlogPosts/Form', ['post' => $blogPost]);
    }

    public function update(Request $request, BlogPost $blogPost)
    {
        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'excerpt'      => 'nullable|string|max:1000',
            'body'         => 'required|string|max:100000',
            'category'     => 'nullable|string|max:100',
            'is_published' => 'nullable|boolean',
        ]);

        $validated['is_published'] = $validated['is_published'] ?? false;

        if ($validated['is_published'] && ! $blogPost->published_at) {
            $validated['published_at'] = now();
        } elseif (! $validated['is_published']) {
            $validated['published_at'] = null;
        }

        $blogPost->update($validated);

        return redirect()->route('admin.blog-posts.index')->with('success', 'Article mis à jour.');
    }

    public function destroy(BlogPost $blogPost)
    {
        $blogPost->delete();

        return back()->with('success', 'Article supprimé.');
    }
}
