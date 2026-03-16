<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogPost;
use App\Models\User;
use Carbon\Carbon;

class BlogPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // find an existing user or create a dummy admin
        $user = User::first();
        if (! $user) {
            $user = User::factory()->create(['name' => 'Blog Admin', 'email' => 'blogadmin@example.test']);
        }

        $posts = [
            [
                'title' => 'Understanding Small Business Tax Filing in Nigeria',
                'excerpt' => 'A practical guide for small business owners on tax filing requirements and common pitfalls.',
                'body' => '<p>Filing taxes as a small business can be daunting. This article walks through registration, record keeping, and deadlines that matter.</p>\n<p>Keep proper records and consult a qualified accountant when in doubt.</p>',
            ],
            [
                'title' => 'How Accountants Can Manage Multiple Client Businesses',
                'excerpt' => 'Best practices for accountants using TaxMaster to manage several client businesses.',
                'body' => '<p>Accountants often juggle many clients. We discuss workflows, security, and tips to keep client data separated and organised.</p>',
            ],
            [
                'title' => 'Optimising VAT Returns: Tips for Quick Compliance',
                'excerpt' => 'Speed up your VAT return preparation with these practical tips and checklist.',
                'body' => '<p>VAT returns are time-sensitive. Follow this checklist to ensure accuracy and avoid penalties.</p>',
            ],
            [
                'title' => 'Preparing for Year-End: A Small Business Checklist',
                'excerpt' => 'End-of-year tasks every small business should complete to stay tax-ready.',
                'body' => '<p>From reconciling accounts to reviewing payroll, this checklist helps you close the year with confidence.</p>',
            ],
        ];

        foreach ($posts as $p) {
            BlogPost::create([
                'title' => $p['title'],
                'excerpt' => $p['excerpt'],
                'body' => $p['body'],
                'user_id' => $user->id,
                'published_at' => Carbon::now(),
            ]);
        }
    }
}
