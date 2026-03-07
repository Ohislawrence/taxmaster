<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import axios from 'axios';

defineOptions({ layout: PublicLayout });

const posts = ref([]);

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
    <section class="relative overflow-hidden bg-white py-12">
        <div class="mx-auto max-w-5xl px-4">
            <div class="pt-12 pb-8">
                <h1 class="reveal text-4xl font-bold mb-3 text-gray-900">Blog</h1>
                <p class="reveal text-gray-500 mb-6">News, product updates, and tax guidance for businesses.</p>
                <div class="reveal mb-8">
                    <Link :href="route('register')" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white hover:bg-black">Get started — it's free</Link>
                </div>
            </div>

            <div v-if="posts.length === 0" class="text-gray-400">No posts yet.</div>
            <div v-else class="grid grid-cols-1 gap-8">
                <div v-for="post in posts" :key="post.id" class="reveal">
                    <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-50 p-8 transition group">
                        <Link :href="`/blog/${post.slug}`" class="block">
                            <h2 class="text-2xl font-semibold text-gray-900 group-hover:text-blue-600 transition">{{ post.title }}</h2>
                            <p class="mt-3 text-gray-500">{{ post.excerpt }}</p>
                            <div class="mt-4 text-xs text-gray-400 flex items-center gap-2">
                                <span>{{ post.user?.name }}</span>
                                <span>·</span>
                                <span>{{ new Date(post.published_at).toLocaleDateString() }}</span>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
