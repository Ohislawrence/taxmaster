<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import axios from 'axios';

defineOptions({ layout: PublicLayout });

const posts = ref([]);

function assetPath(path) {
    if (!path) return null;
    if (path.startsWith('http') || path.startsWith('/')) return path;
    return `/storage/${path.replace(/^\/+/, '')}`;
}

function formatDateFor(post) {
    const d = post.posted_at ?? post.published_at;
    if (!d) return '';
    return new Date(d).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

onMounted(async () => {
    const res = await axios.get('/api/blog-posts');
    posts.value = res.data;

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
});
</script>

<template>
    <section class="relative overflow-hidden bg-white pt-24 pb-12 sm:pt-28 sm:pb-16 lg:pt-36 lg:pb-24">
        <!-- Subtle grid background - Mono.co style -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#f0f0f0_1px,transparent_1px),linear-gradient(to_bottom,#f0f0f0_1px,transparent_1px)] bg-[size:3rem_3rem] sm:bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_80%_50%_at_50%_0%,#000_70%,transparent_100%)]"></div>

        <div class="relative mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <!-- Hero - Refined typography -->
            <div class="pt-12 sm:pt-16 pb-8 sm:pb-12 text-center sm:text-left">
                <h1 class="reveal text-3xl sm:text-4xl lg:text-5xl font-bold mb-3 text-slate-900 tracking-tight">Blog</h1>
                <p class="reveal text-slate-500 mb-6 text-base sm:text-lg max-w-2xl mx-auto sm:mx-0">News, product updates, and tax guidance for Nigerian businesses.</p>
                
                <!-- CTA - Mono pill style -->
                <div class="reveal flex flex-col sm:flex-row items-center sm:items-start gap-4">
                    <Link 
                        :href="route('register')" 
                        class="group inline-flex items-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95"
                    >
                        Get started — it's free
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                    
                    <!-- Secondary CTA for desktop -->
                    <Link 
                        href="/features"
                        class="hidden sm:inline-flex items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900 transition-colors"
                    >
                        View features
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </Link>
                </div>
            </div>

            <!-- Posts Grid - Clean card design -->
            <div v-if="posts.length === 0" class="reveal text-slate-400 text-center py-16 sm:py-20">
                <p class="text-lg">No posts yet.</p>
                <p class="text-sm mt-2">Check back soon for updates.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 gap-4 sm:gap-6">
                <div 
                    v-for="(post, i) in posts" 
                    :key="post.id" 
                    class="reveal" 
                    :style="{ transitionDelay: `${i * 75}ms` }"
                >
                    <div class="group bg-white rounded-2xl sm:rounded-3xl border border-slate-100 p-6 sm:p-8 transition-all duration-300 hover:border-slate-200 hover:shadow-[0_8px_30px_rgba(0,0,0,0.06)]">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <!-- Mobile: Stacked layout -->
                            <div class="flex flex-col sm:flex-row sm:items-start gap-4 sm:gap-6">
                                <!-- Featured image (optional) -->
                                <div v-if="post.featured_image" class="sm:w-32 lg:w-40 flex-shrink-0">
                                    <div class="aspect-[16/9] sm:aspect-square rounded-xl sm:rounded-2xl bg-slate-50 overflow-hidden">
                                        <img 
                                            :src="assetPath(post.featured_image)" 
                                            :alt="post.title"
                                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                            loading="lazy"
                                        />
                                    </div>
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <!-- Category tag (if available) -->
                                    <div v-if="post.category" class="mb-3">
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            {{ post.category }}
                                        </span>
                                    </div>

                                    <!-- Title with arrow indicator on mobile -->
                                    <div class="flex items-start justify-between gap-3">
                                        <h2 class="text-lg sm:text-xl lg:text-2xl font-semibold text-slate-900 group-hover:text-blue-600 transition-colors">
                                            {{ post.title }}
                                        </h2>
                                        <!-- Desktop arrow -->
                                        <svg class="hidden sm:block w-5 h-5 text-slate-400 group-hover:text-blue-600 transition-colors flex-shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>

                                    <!-- Excerpt - shorter on mobile -->
                                    <p class="text-sm sm:text-base text-slate-500 leading-relaxed mt-2 line-clamp-2 sm:line-clamp-3">
                                        {{ post.excerpt }}
                                    </p>

                                    <!-- Meta info - Responsive layout -->
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-xs text-slate-400 mt-4">
                                        <span class="font-medium text-slate-600">{{ post.user?.name }}</span>
                                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                        <span>{{ formatDateFor(post) }}</span>
                                        <span v-if="post.read_time" class="inline-flex items-center gap-1">
                                            <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                                            <span>{{ post.read_time }} min read</span>
                                        </span>
                                    </div>

                                    <!-- Mobile arrow indicator -->
                                    <div class="sm:hidden flex items-center gap-1 text-blue-600 text-sm font-medium mt-4">
                                        Read more
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Bottom CTA - Refined with mono.co styling -->
            <div class="reveal mt-16 sm:mt-20 pt-8 sm:pt-10 border-t border-slate-100">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-6">
                    <div class="text-center sm:text-left">
                        <p class="text-slate-600 text-sm">Want to stay updated on new posts?</p>
                        <p class="text-xs text-slate-400 mt-1">Get the latest articles delivered to your inbox.</p>
                    </div>
                    
                    <div class="flex flex-col xs:flex-row gap-3 w-full sm:w-auto">
                        <Link 
                            :href="route('register')" 
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-slate-900 px-6 py-3 text-sm font-semibold text-white transition-all hover:shadow-lg hover:shadow-slate-900/20 active:scale-95 w-full xs:w-auto"
                        >
                            Create an account
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                        </Link>
                        
                        <!-- RSS feed link (optional) -->
                        <a 
                            href="/blog/feed"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-medium text-slate-600 transition-all hover:border-slate-300 hover:bg-slate-50 w-full xs:w-auto"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 19.5v-.75a7.5 7.5 0 00-7.5-7.5H4.5m0-6.75h.75c7.87 0 14.25 6.38 14.25 14.25v.75M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            RSS
                        </a>
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

/* Custom line clamp utilities */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Extra small screen breakpoint */
@media (min-width: 480px) {
    .xs\:w-auto {
        width: auto;
    }
    .xs\:flex-row {
        flex-direction: row;
    }
}
</style>