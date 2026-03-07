<script setup>
import { ref, onMounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import axios from 'axios';

defineOptions({ layout: AdminLayout });

const page = usePage();
const form = ref({
    title: '',
    excerpt: '',
    body: '',
    cover_image: null,
    published_at: '',
});

onMounted(async () => {
    const res = await axios.get(`/api/blog-posts/${page.props.id}`);
    Object.assign(form.value, res.data);
});

const handleImage = (e) => {
    form.value.cover_image = e.target.files[0];
};

const submit = async () => {
    const data = new FormData();
    Object.entries(form.value).forEach(([k, v]) => data.append(k, v));
    await router.post(`/api/blog-posts/${page.props.id}?_method=PUT`, data);
};
</script>

<template>
    <section class="py-12">
        <div class="mx-auto max-w-3xl px-4">
            <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-50 p-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900">Edit Blog Post</h1>
                <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block font-semibold mb-1">Title</label>
                <input v-model="form.title" type="text" class="w-full rounded border-gray-300" required />
            </div>
            <div>
                <label class="block font-semibold mb-1">Excerpt</label>
                <textarea v-model="form.excerpt" class="w-full rounded border-gray-300" rows="2"></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Body</label>
                <QuillEditor v-model="form.body" class="bg-white" theme="snow" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Cover Image</label>
                <input type="file" @change="handleImage" accept="image/*" />
            </div>
            <div>
                <label class="block font-semibold mb-1">Publish Date</label>
                <input v-model="form.published_at" type="datetime-local" class="w-full rounded border-gray-300" />
            </div>
                    <div class="flex gap-4">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">Update</button>
                        <Link :href="route('admin.blog.index')" class="text-gray-500 hover:underline">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
