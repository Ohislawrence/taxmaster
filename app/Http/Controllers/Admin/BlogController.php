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
        // Return admin blog index with posts so the page doesn't need to call API (avoids API auth/session differences)
        $posts = BlogPost::with('user')->orderByDesc('published_at')->get();
        return Inertia::render('Admin/Blog/Index', [
            'posts' => $posts,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Blog/Create');
    }

    public function edit($id)
    {
        $post = BlogPost::with('user')->findOrFail($id);
        return Inertia::render('Admin/Blog/Edit', [
            'id' => $id,
            'post' => $post,
        ]);
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
            $file = $request->file('cover_image');

            // Generate a unique name
            $fileName = time() . '_' . $file->getClientOriginalName();

            // Move the file directly to public/uploads/blog
            $file->move(public_path('uploads/blog'), $fileName);

            // Save the relative path in the database
            $data['cover_image'] = 'uploads/blog/' . $fileName;
        }

        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($data['title']);

        try {
            BlogPost::create($data);
        } catch (\Throwable $e) {
            \Log::error('Admin Blog create failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to create post.');
        }

        return redirect()->route('admin.blog.index')->with('success', 'Post created.');
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
            $file = $request->file('cover_image');

            try {
                // 1. Delete old image from public folder if it exists
                if ($post->cover_image) {
                    $oldPath = public_path($post->cover_image);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }

                // 2. Generate unique name and move new file
                $fileName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/blog'), $fileName);

                // 3. Update the data array with the new path
                $data['cover_image'] = 'uploads/blog/' . $fileName;

            } catch (\Throwable $e) {
                \Log::error('Admin Blog update: cover_image move failed', [
                    'error' => $e->getMessage(),
                    'id' => $id
                ]);
            }
        }

        $data['slug'] = Str::slug($data['title']);

        try {
            $post->update($data);
        } catch (\Throwable $e) {
            \Log::error('Admin Blog update failed', [
                'error' => $e->getMessage(),
                'payload' => $data
            ]);
            return redirect()->back()->with('error', 'Failed to update post.');
        }

        return redirect()->route('admin.blog.index')->with('success', 'Post updated.');
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
