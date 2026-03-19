<script setup>
import { ref, computed } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

defineOptions({ layout: AdminLayout });

const form = ref({
    title: '',
    excerpt: '',
    body: '',
    cover_image: null,
    published_at: '',
});

const handleImage = (e) => {
    form.value.cover_image = e.target.files[0];
};

const isSubmitting = ref(false);
const page = usePage();
const errors = computed(() => (page.props && page.props.errors) ? page.props.errors : {});
const localErrors = ref({});

const normalizePublishedAt = (val) => {
    if (!val) return '';
    let v = val.toString();
    // datetime-local -> 'YYYY-MM-DDTHH:MM' or with seconds
    if (v.includes('T')) {
        v = v.replace('T', ' ');
    }
    // ensure seconds are present
    const parts = v.split(' ');
    if (parts.length >= 2) {
        let date = parts[0];
        let time = parts[1];
        if (time.length === 5) time = time + ':00';
        return `${date} ${time}`;
    }
    return v;
};

const submit = async () => {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    console.log('Submitting blog create', form.value);

    // Client-side validation: ensure title and body are present
    localErrors.value = {};
    const title = (form.value.title || '').toString().trim();
    // Prefer the v-model value; if empty, fall back to Quill editor DOM content
    let bodyHtml = (form.value.body || '').toString();
    if (!bodyHtml) {
        const el = document.querySelector('.ql-editor');
        bodyHtml = el ? el.innerHTML : '';
    }
    // Normalize non-breaking spaces and collapse whitespace
    const normalized = bodyHtml.replace(/\u00A0|&nbsp;/g, ' ').trim();
    // Strip HTML tags and collapse spaces to get plain text
    const bodyText = normalized.replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
    // Common empty-editor HTML fragments (Quill and others)
    const isEmptyHtml = /^(?:<p>(?:&nbsp;|\s|<br\s*\/?>)*<\/p>|<div>(?:&nbsp;|\s|<br\s*\/?>)*<\/div>)$/i.test(normalized.trim());

    if (!title) {
        localErrors.value.title = ['The title field is required.'];
    }
    if (!bodyText || isEmptyHtml) {
        localErrors.value.body = ['The body field is required.'];
    }
    if (Object.keys(localErrors.value).length > 0) {
        console.error('Client validation failed', localErrors.value);
        isSubmitting.value = false;
        return;
    }

    // Ensure the bodyHtml is sent to the server
    form.value.body = bodyHtml;

    const data = new FormData();
    data.append('title', form.value.title || '');
    data.append('excerpt', form.value.excerpt || '');
    data.append('body', form.value.body || '');
    data.append('published_at', normalizePublishedAt(form.value.published_at) || '');
    if (form.value.cover_image && form.value.cover_image instanceof File) {
        data.append('cover_image', form.value.cover_image);
    }

    router.post(route('admin.blog.store'), data, {
        preserveState: false,
        onError(errors) {
            try {
                const e = JSON.parse(JSON.stringify(errors));
                console.error('Validation errors:', e);
            } catch (ex) {
                console.error('Validation errors (raw):', errors);
            }
            alert('Failed to create post. Check console for details.');
        },
        onFinish() {
            isSubmitting.value = false;
        }
    });
};

const firstError = (field) => {
    const src = (localErrors.value && localErrors.value[field]) || (errors.value && errors.value[field]);
    if (!src) return '';
    return Array.isArray(src) ? src[0] : src;
};
</script>

<template>
    <section class="py-12">
        <div class="mx-auto max-w-3xl px-4">
            <div class="bg-white/90 backdrop-blur-md rounded-2xl shadow-2xl border border-blue-50 p-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900">New Blog Post</h1>
                <form @submit.prevent="submit" class="space-y-6">
            <div>
                <label class="block font-semibold mb-1">Title</label>
                <input v-model="form.title" type="text" class="w-full rounded border-gray-300" required />
                <div v-if="localErrors.title || errors.title" class="text-red-600 text-sm mt-1">{{ firstError('title') }}</div>
            </div>
            <div>
                <label class="block font-semibold mb-1">Excerpt</label>
                <textarea v-model="form.excerpt" class="w-full rounded border-gray-300" rows="2"></textarea>
            </div>
            <div>
                <label class="block font-semibold mb-1">Body</label>
                <QuillEditor v-model="form.body" class="bg-white" theme="snow" />
                <div v-if="localErrors.body || errors.body" class="text-red-600 text-sm mt-1">{{ firstError('body') }}</div>
            </div>
            <div>
                <label class="block font-semibold mb-1">Cover Image</label>
                <input type="file" @change="handleImage" accept="image/*" />
                <div v-if="localErrors.cover_image || errors.cover_image" class="text-red-600 text-sm mt-1">{{ firstError('cover_image') }}</div>
            </div>
            <div>
                <label class="block font-semibold mb-1">Publish Date</label>
                <input v-model="form.published_at" type="datetime-local" class="w-full rounded border-gray-300" />
            </div>
                    <div class="flex gap-4">
                        <button :disabled="isSubmitting" type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold hover:bg-blue-700 transition disabled:opacity-60" >
                            <span v-if="isSubmitting">Publishing…</span>
                            <span v-else>Publish</span>
                        </button>
                        <Link :href="route('admin.blog.index')" class="text-gray-500 hover:underline">Cancel</Link>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
