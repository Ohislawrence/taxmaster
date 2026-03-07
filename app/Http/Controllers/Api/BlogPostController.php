<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BlogPostController extends Controller
{
    // Public list; admin can pass ?admin=1 to get drafts
    public function index(Request $request)
    {
        $isAdmin = false;
        if (Auth::check()) {
            $user = Auth::user();
            if (method_exists($user, 'hasRole')) {
                $isAdmin = $user->hasRole('admin');
            } elseif (isset($user->is_admin)) {
                $isAdmin = (bool) $user->is_admin;
            }
        }

        if ($request->query('admin') && $isAdmin) {
            $posts = BlogPost::with('user')->orderByDesc('published_at')->get();
        } else {
            $posts = BlogPost::with('user')->whereNotNull('published_at')->orderByDesc('published_at')->get();
        }

        return response()->json($posts);
    }

    // Show by id or slug
    public function show($idOrSlug)
    {
        $post = BlogPost::with('user')
            ->where('id', $idOrSlug)
            ->orWhere('slug', $idOrSlug)
            ->firstOrFail();

        return response()->json($post);
    }

    // Create (admin only)
    public function store(Request $request)
    {
        $this->authorize('admin');

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:4096',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('blog', 'public');
        }

        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($data['title']);

        $post = BlogPost::create($data);

        return response()->json($post, 201);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin');

        $post = BlogPost::findOrFail($id);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|max:4096',
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

        return response()->json($post);
    }

    public function destroy($id)
    {
        $this->authorize('admin');

        $post = BlogPost::findOrFail($id);
        if ($post->cover_image) {
            Storage::disk('public')->delete($post->cover_image);
        }
        $post->delete();

        return response()->json(['success' => true]);
    }
}
