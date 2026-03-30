<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    /**
     * Display a listing of blog posts.
     */
    public function index()
    {
        return Inertia::render('Blog/Index', [
            'title' => 'Blog - Tax News & Updates',
            'description' => 'Tax compliance news, product updates, and guidance for Nigerian businesses. Stay informed on VAT, PAYE, WHT, CIT, and regulatory changes.',
            'ogImage' => asset('company-Income-Tax.jpg'),
            'meta' => [
                'description' => 'Tax compliance news, product updates, and guidance for Nigerian businesses. Stay informed on VAT, PAYE, WHT, CIT, and regulatory changes.',
                'keywords' => 'Nigerian tax blog, tax compliance news, VAT updates, PAYE news, WHT guidance, tax regulations Nigeria',
                'author' => 'TaxMaster NG',
                'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
                'og:site_name' => config('app.name', 'TaxMaster NG'),
                'og:locale' => 'en_NG',
                'og:title' => 'TaxMaster Blog - Tax News & Updates for Nigerian Businesses',
                'og:description' => 'Expert tax compliance news, product updates, and guidance for Nigerian businesses.',
                'og:type' => 'website',
                'og:image' => asset('company-Income-Tax.jpg'),
                'og:image:width' => '1200',
                'og:image:height' => '630',
                'og:url' => url('/blog'),
                'twitter:card' => 'summary_large_image',
                'twitter:title' => 'TaxMaster Blog - Tax News & Updates',
                'twitter:description' => 'Tax compliance news and guidance for Nigerian businesses.',
                'twitter:image' => asset('company-Income-Tax.jpg'),
                'twitter:site' => '@TaxMasterNG',
                'twitter:creator' => '@TaxMasterNG',
            ],
        ]);
    }

    /**
     * Display the specified blog post.
     */
    public function show(string $slug)
    {
        // Fetch the blog post by slug
        $post = BlogPost::where('slug', $slug)
            ->where(function($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->firstOrFail();

        // Get related posts (same category or recent posts)
        $relatedPosts = BlogPost::where('id', '!=', $post->id)
            ->where(function($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->latest('published_at')
            ->take(3)
            ->get(['id', 'slug', 'title', 'excerpt', 'cover_image', 'published_at']);

        $metaDescription = $post->excerpt
            ? substr(strip_tags($post->excerpt), 0, 160)
            : substr(strip_tags($post->body), 0, 160);

        $ogImage = $post->cover_image ? asset('storage/' . $post->cover_image) : asset('company-Income-Tax.jpg');

        return Inertia::render('Blog/Show', [
            'slug' => $slug,
            'title' => $post->title . ' - TaxMaster Blog',
            'description' => $metaDescription,
            'ogImage' => $ogImage,
            'meta' => [
                'description' => $metaDescription,
                'keywords' => 'Nigerian tax, tax compliance, ' . $post->title,
                'author' => 'TaxMaster NG',
                'robots' => 'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1',
                'og:site_name' => config('app.name', 'TaxMaster NG'),
                'og:locale' => 'en_NG',
                'og:title' => $post->title,
                'og:description' => $metaDescription,
                'og:type' => 'article',
                'og:image' => $ogImage,
                'og:image:width' => '1200',
                'og:image:height' => '630',
                'og:url' => url('/blog/' . $slug),
                'article:published_time' => $post->published_at ? $post->published_at->toIso8601String() : null,
                'twitter:card' => 'summary_large_image',
                'twitter:title' => $post->title,
                'twitter:description' => $metaDescription,
                'twitter:image' => $ogImage,
                'twitter:site' => '@TaxMasterNG',
                'twitter:creator' => '@TaxMasterNG',
            ],
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'cover_image' => $post->cover_image,
                'published_at' => $post->published_at,
                'author' => $post->user ? [
                    'id' => $post->user->id,
                    'name' => $post->user->name,
                    'email' => $post->user->email,
                ] : null,
                'related_posts' => $relatedPosts,
            ],
        ]);
    }
}
