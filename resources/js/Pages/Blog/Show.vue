<script setup>
import { ref, onMounted, nextTick, computed } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import SeoMeta from '@/Components/SeoMeta.vue';
import axios from 'axios';

defineOptions({ layout: PublicLayout });

const page = usePage();
const post = ref(null);

// Computed property to safely check if post has related posts
const hasRelatedPosts = computed(() => {
    return post.value?.related_posts?.length > 0;
});

function assetPath(path) {
    if (!path) return null;
    if (path.startsWith('http') || path.startsWith('/')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
}

function formatDateFor(post, longMonth = false) {
    const d = post?.published_at ?? post?.posted_at;
    if (!d) return '';
    return new Date(d).toLocaleDateString('en-US', longMonth ? { month: 'long', day: 'numeric', year: 'numeric' } : { month: 'short', day: 'numeric', year: 'numeric' });
}

function authorInitials(name) {
    if (!name) return 'TM';
    return name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
}

onMounted(async () => {
    try {
        const res = await axios.get(`/api/blog-posts/${page.props.slug}`);
        post.value = res.data;

        await nextTick();
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.reveal').forEach((el) => observer.observe(el));
    } catch (error) {
        console.error('Failed to load post:', error);
    }
});
</script>

<template>
    <!-- Dynamic SEO: use post data when available so title isn't the slug and cover image is used -->
    <SeoMeta
        v-if="post"
        :title="post.title"
        :description="post.excerpt"
        :og-image="assetPath(post.cover_image)"
        :canonicalUrl="`https://taxmaster.ng/blog/${page.props.slug}`"
    />
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-36 lg:pb-24">
        <!-- Subtle grid background - Mono.co style -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:3rem_3rem] sm:bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

        <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <!-- Back link - Refined -->
            <div class="reveal mb-6 sm:mb-8">
                <Link
                    href="/blog"
                    class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-900 transition-colors group"
                >
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to blog
                </Link>
            </div>

            <!-- Loading state -->
            <div v-if="!post" class="reveal text-slate-400 text-center py-16 sm:py-20">
                <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-slate-200 border-t-slate-900"></div>
                <p class="mt-4 text-sm">Loading article...</p>
            </div>

            <!-- Article Content -->
            <article v-else class="bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 lg:p-12 shadow-sm">
                <!-- Article Header -->
                <header class="reveal">
                    <!-- Meta info - Responsive layout -->
                    <div class="flex flex-wrap items-center gap-3 text-xs sm:text-sm text-slate-500 mb-6">
                        <!-- Category/Pill -->
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                            Article
                        </span>

                        <!-- Author with avatar on desktop -->
                        <div class="flex items-center gap-2">
                            <div class="hidden sm:flex h-6 w-6 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 items-center justify-center text-xs font-medium text-slate-600">
                                {{ authorInitials(post.user?.name) }}
                            </div>
                            <span class="font-medium text-slate-700">{{ post.user?.name }}</span>
                        </div>

                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>

                        <!-- Date -->
                        <time :datetime="post.published_at">{{ formatDateFor(post, true) }}</time>

                        <!-- Read time - hidden on mobile, shown on tablet+ -->
                        <span v-if="post.read_time" class="hidden sm:inline-flex items-center gap-2">
                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                            <span>{{ post.read_time }} min read</span>
                        </span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-slate-900 tracking-tight leading-tight mb-4">
                        {{ post.title }}
                    </h1>

                    <!-- Excerpt -->
                    <p class="text-base sm:text-lg text-slate-500 leading-relaxed max-w-3xl">
                        {{ post.excerpt }}
                    </p>

                    <!-- Mobile read time indicator -->
                    <div v-if="post.read_time" class="sm:hidden flex items-center gap-2 text-xs text-slate-400 mt-4 pt-4 border-t border-slate-100">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5" fill="none" />
                        </svg>
                        <span>{{ post.read_time }} minute read</span>
                    </div>
                </header>

                <!-- Cover Image - Enhanced presentation -->
                <figure v-if="post.cover_image" class="reveal mt-8 sm:mt-10">
                    <div class="relative aspect-[16/9] sm:aspect-[21/9] rounded-xl sm:rounded-2xl overflow-hidden bg-slate-50">
                        <img
                            :src="'/' + post.cover_image"
                            class="absolute inset-0 w-full h-full object-cover"
                            :alt="post.title"
                            loading="eager"
                        />
                    </div>
                    <figcaption v-if="post.cover_caption" class="text-xs text-slate-400 mt-3 text-center">
                        {{ post.cover_caption }}
                    </figcaption>
                </figure>

                <!-- Article Body - Enhanced prose styling -->
                <div class="reveal prose prose-slate prose-lg max-w-none mt-8 sm:mt-10">
                    <!-- Table of Contents (optional - if post has sections) -->
                    <div v-if="post.toc" class="bg-slate-50 rounded-xl p-6 mb-8 not-prose">
                        <h4 class="text-sm font-semibold text-slate-900 mb-3">In this article</h4>
                        <nav class="space-y-1">
                            <a
                                v-for="item in post.toc"
                                :key="item.id"
                                :href="`#${item.id}`"
                                class="block text-sm text-slate-600 hover:text-blue-600 transition-colors"
                            >
                                {{ item.title }}
                            </a>
                        </nav>
                    </div>

                    <!-- Main content -->
                    <div v-html="post.body" class="blog-content"></div>

                    <!-- Tags -->
                    <div v-if="post.tags?.length" class="mt-8 pt-6 border-t border-slate-100 not-prose">
                        <div class="flex flex-wrap gap-2">
                            <span
                                v-for="tag in post.tags"
                                :key="tag"
                                class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700 hover:bg-slate-200 transition-colors cursor-default"
                            >
                                #{{ tag }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Author Box - Enhanced with more details -->
                <footer class="reveal mt-12 sm:mt-16 pt-8 sm:pt-10 border-t border-slate-100">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
                        <!-- Author avatar - larger -->
                        <div class="flex-shrink-0">
                            <div class="h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 flex items-center justify-center text-xl sm:text-2xl font-bold text-slate-600 shadow-inner">
                                {{ authorInitials(post.user?.name) }}
                            </div>
                        </div>

                        <!-- Author info -->
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 mb-2">
                                <h3 class="text-lg sm:text-xl font-semibold text-slate-900">
                                    {{ post.user?.name }}
                                </h3>
                                <span class="text-sm text-slate-400">·</span>
                                <span class="text-sm text-slate-500">{{ post.user?.role || 'Contributor' }}</span>
                            </div>

                            <p class="text-sm sm:text-base text-slate-500 leading-relaxed max-w-2xl">
                                {{ post.user?.bio || 'Sharing insights on tax technology and business growth in Nigeria.' }}
                            </p>

                            <!-- Author social links (if available) -->
                            <div v-if="post.user?.social" class="flex gap-3 mt-4">
                                <a
                                    v-for="(url, platform) in post.user.social"
                                    :key="platform"
                                    :href="url"
                                    target="_blank"
                                    rel="noopener"
                                    class="text-slate-400 hover:text-slate-900 transition-colors"
                                    :aria-label="`Follow on ${platform}`"
                                >
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path v-if="platform === 'twitter'" d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                        <path v-if="platform === 'linkedin'" d="M20.5 2h-17A1.5 1.5 0 002 3.5v17A1.5 1.5 0 003.5 22h17a1.5 1.5 0 001.5-1.5v-17A1.5 1.5 0 0020.5 2zM8 19H5v-9h3zM6.5 8.25A1.75 1.75 0 118.3 6.5a1.78 1.78 0 01-1.8 1.75zM19 19h-3v-4.74c0-1.42-.6-1.93-1.38-1.93A1.74 1.74 0 0013 14.19a.66.66 0 000 .14V19h-3v-9h2.9v1.3a3.11 3.11 0 012.7-1.4c1.55 0 3.36.86 3.36 3.66z" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </footer>
            </article>

            <!-- Related Posts (optional - if you have related posts) -->
            <div v-if="hasRelatedPosts" class="reveal mt-16 sm:mt-20">
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-6 sm:mb-8">Related articles</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div
                        v-for="related in post.related_posts"
                        :key="related.id"
                        class="group bg-white rounded-xl sm:rounded-2xl border border-slate-100 p-5 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)]"
                    >
                        <Link :href="`/blog/${related.slug}`" class="block">
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 group-hover:text-blue-600 transition-colors line-clamp-2 mb-2">
                                {{ related.title }}
                            </h3>
                            <p class="text-xs sm:text-sm text-slate-500 line-clamp-2 mb-3">
                                {{ related.excerpt }}
                            </p>
                            <div class="flex items-center gap-2 text-xs text-slate-400">
                                <span>{{ formatDateFor(related) }}</span>
                                <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                <span>{{ related.read_time }} min</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- CTA - Refined pill style -->
            <div class="reveal mt-16 sm:mt-20 text-center">
                <div class="bg-slate-50 rounded-2xl sm:rounded-3xl p-8 sm:p-12">
                    <h2 class="text-xl sm:text-2xl font-bold text-slate-900 mb-2">Ready to simplify your tax compliance?</h2>
                    <p class="text-sm sm:text-base text-slate-500 mb-6">Join thousands of Nigerian businesses using TaxMaster</p>

                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <Link
                            :href="route('register')"
                            class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 sm:px-8 py-3 sm:py-4 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                        >
                            Get started for free
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </Link>

                        <Link
                            href="/pricing"
                            class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-6 sm:px-8 py-3 sm:py-4 text-sm font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50"
                        >
                            View pricing
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.reveal {
    opacity: 0;
    transform: translateY(20px);
    transition: opacity 0.6s cubic-bezier(0.22, 1, 0.36, 1), transform 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.reveal.is-visible {
    opacity: 1;
    transform: translateY(0);
}

/* Blog content prose styling */
.blog-content :deep(h2) {
    @apply text-2xl sm:text-3xl font-bold text-slate-900 mt-10 mb-4 scroll-mt-24;
}
.blog-content :deep(h3) {
    @apply text-xl sm:text-2xl font-semibold text-slate-900 mt-8 mb-3;
}
.blog-content :deep(p) {
    @apply text-slate-600 leading-relaxed mb-6 text-base sm:text-lg;
}
.blog-content :deep(ul),
.blog-content :deep(ol) {
    @apply mb-6 pl-5 text-slate-600 space-y-2;
}
.blog-content :deep(li) {
    @apply text-base sm:text-lg;
}
.blog-content :deep(a) {
    @apply text-blue-600 hover:text-blue-700 underline decoration-2 decoration-blue-200 hover:decoration-blue-600 transition-colors;
}
.blog-content :deep(blockquote) {
    @apply border-l-4 border-blue-200 bg-slate-50 pl-6 py-4 pr-4 italic text-slate-600 my-8 rounded-r-xl;
}
.blog-content :deep(code) {
    @apply bg-slate-100 rounded-lg px-2 py-1 text-sm font-mono text-slate-800;
}
.blog-content :deep(pre) {
    @apply bg-slate-900 rounded-xl p-4 sm:p-6 overflow-x-auto my-8;
}
.blog-content :deep(pre code) {
    @apply bg-transparent text-slate-100 p-0 text-sm;
}
.blog-content :deep(img) {
    @apply rounded-xl sm:rounded-2xl my-8;
}
.blog-content :deep(hr) {
    @apply my-10 border-slate-200;
}
.blog-content :deep(table) {
    @apply min-w-full divide-y divide-slate-200 my-8;
}
.blog-content :deep(th) {
    @apply bg-slate-50 px-4 py-3 text-left text-sm font-semibold text-slate-900;
}
.blog-content :deep(td) {
    @apply px-4 py-3 text-sm text-slate-600 border-t border-slate-100;
}

/* Line clamp utilities */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Animation for spinner */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
.animate-spin {
    animation: spin 1s linear infinite;
}
</style>
