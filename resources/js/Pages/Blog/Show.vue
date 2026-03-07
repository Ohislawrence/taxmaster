<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import axios from 'axios';

defineOptions({ layout: PublicLayout });

const page = usePage();
const post = ref(null);

onMounted(async () => {
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
});
</script>

<template>
    <section class="py-16">
        <div class="mx-auto max-w-5xl px-4">
            <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-50 p-8">
                <div v-if="!post" class="text-gray-400">Loading...</div>
                <div v-else>
                    <h1 class="reveal text-4xl font-bold mb-4 text-gray-900">{{ post.title }}</h1>
                    <div class="reveal mb-6 text-gray-500">{{ post.excerpt }}</div>
                    <img v-if="post.cover_image" :src="post.cover_image" class="reveal rounded-2xl mb-6 max-h-96 object-cover w-full" />
                    <div class="prose prose-lg max-w-none" v-html="post.body"></div>
                    <div class="mt-8 text-xs text-gray-400 flex items-center gap-2">
                        <span>{{ post.user?.name }}</span>
                        <span>·</span>
                        <span>{{ new Date(post.published_at).toLocaleDateString() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
