<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
defineOptions({ layout: PublicLayout });
import { computed } from 'vue';
const props = defineProps({
  title: String,
  markdown: { type: [String, null], default: null },
});

// Lightweight markdown -> HTML renderer (simple, safe-ish)
const formatMarkdown = (text) => {
  if (!text) return '';
  let html = text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/```([\s\S]*?)```/g, '<pre class="bg-gray-100 p-3 rounded my-2 overflow-x-auto">$1</pre>')
    .replace(/`([^`]+)`/g, '<code class="bg-gray-100 rounded px-1 py-0.5 text-xs">$1</code>')
    .replace(/### (.+)$/gm, '<h3 class="text-lg font-semibold mt-4">$1</h3>')
    .replace(/## (.+)$/gm, '<h2 class="text-xl font-bold mt-4">$1</h2>')
    .replace(/# (.+)$/gm, '<h1 class="text-3xl font-bold mb-4">$1</h1>')
    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
    .replace(/\*(.+?)\*/g, '<em>$1</em>')
    .replace(/^-\s+(.+)$/gm, '<li>$1</li>')
    .replace(/\n\n+/g, '</p><p class="mt-2">')
    .replace(/\n/g, '<br>');

  // wrap list items
  html = html.replace(/((?:<li>.*<\/li>\s*)+)/g, '<ul class="ml-6 list-disc mt-2">$1</ul>');
  return '<div class="prose prose-lg prose-gray max-w-none">' + '<p>' + html + '</p>' + '</div>';
};

const rendered = computed(() => formatMarkdown(props.markdown));
</script>

<template>
    <section class="pt-24 pb-12 lg:pt-32 lg:pb-20">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl mb-6">{{ props.title }}</h1>
            <div v-if="props.markdown" v-html="rendered"></div>
            <div v-else class="text-gray-600">Content not available.</div>
        </div>
    </section>
</template>
