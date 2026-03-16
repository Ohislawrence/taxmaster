<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use Carbon\Carbon;

class UpdateBlogCoversSeeder extends Seeder
{
    public function run(): void
    {
        $posts = BlogPost::orderBy('id')->take(4)->get();
        foreach ($posts as $i => $p) {
            $p->cover_image = 'blog/cover' . ($i + 1) . '.svg';
            $p->published_at = Carbon::now();
            $p->save();
        }
    }
}
