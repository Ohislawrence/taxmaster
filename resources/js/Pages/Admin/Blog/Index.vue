<script setup>
import { ref, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const page = usePage();
const posts = ref(page.props.posts || []);

onMounted(async () => {
    // if server-provided posts are empty for some reason, fall back to API
    if (!posts.value || posts.value.length === 0) {
        // fallback to API only if server didn't provide posts
        try {
            const res = await fetch('/api/blog-posts?admin=1', { credentials: 'include' });
            if (res.ok) {
                posts.value = await res.json();
            }
        } catch (e) {
            // silent fallback
        }
    }
});

const deletePost = (id) => {
    if (!confirm('Delete this post?')) return;
    router.delete(route('admin.blog.destroy', id), {
        preserveState: false,
        onSuccess() {
            posts.value = posts.value.filter(p => p.id !== id);
        }
    });
};
</script>

<template>
    <section class="max-w-4xl mx-auto py-12 px-4">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Manage Blog Posts</h1>
            <Link :href="route('admin.blog.create')" class="bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">New Post</Link>
        </div>
        <div v-if="posts.length === 0" class="text-gray-400">No posts yet.</div>
        <table v-else class="w-full bg-white rounded-xl shadow border border-gray-100">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="p-4">Title</th>
                    <th class="p-4">Author</th>
                    <th class="p-4">Published</th>
                    <th class="p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="post in posts" :key="post.id" class="border-t">
                    <td class="p-4 font-medium">{{ post.title }}</td>
                    <td class="p-4">{{ post.user?.name }}</td>
                    <td class="p-4">{{ post.published_at ? new Date(post.published_at).toLocaleDateString() : 'Draft' }}</td>
                    <td class="p-4 flex gap-2">
                        <Link :href="route('admin.blog.edit', post.id)" class="text-blue-600 hover:underline">Edit</Link>
                        <button @click="deletePost(post.id)" class="text-red-500 hover:underline">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </section>
</template>
