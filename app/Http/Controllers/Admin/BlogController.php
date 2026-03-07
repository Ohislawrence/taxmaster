<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        // Render the admin blog index page (Vue Inertia page)
        return Inertia::render('Admin/Blog/Index');
    }

    public function create()
    {
        return Inertia::render('Admin/Blog/Create');
    }

    public function edit($id)
    {
        return Inertia::render('Admin/Blog/Edit', ['id' => $id]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ]);
        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('blog', 'public');
        }
        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($data['title']);
        $post = BlogPost::create($data);

        session()->flash('success', 'Post created.');
        return redirect()->route('admin.blog.index');
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:2048',
            'published_at' => 'nullable|date',
        ]);
        if ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::disk('public')->delete($post->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('blog', 'public');
        }
        $data['slug'] = Str::slug($data['title']);
        $post->update($data);

        session()->flash('success', 'Post updated.');
        return redirect()->route('admin.blog.index');
    }

    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        session()->flash('success', 'Post deleted.');
        return redirect()->route('admin.blog.index');
    }
}
