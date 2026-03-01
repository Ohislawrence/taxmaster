<template>
    <div class="bg-white rounded-lg shadow-sm p-6 hover:shadow-md transition-shadow"
        :class="{ 'border-l-4 border-green-500 bg-green-50': step.is_completed }"
    >
        <div class="flex gap-4">
            <!-- Checkbox -->
            <div class="flex-shrink-0 mt-1">
                <button
                    @click="toggleStep"
                    :class="[
                        'flex items-center justify-center w-6 h-6 rounded-full transition-all',
                        step.is_completed 
                            ? 'bg-green-500 text-white' 
                            : 'border-2 border-gray-300 hover:border-blue-500'
                    ]"
                    :title="step.is_completed ? 'Mark as incomplete' : 'Mark as complete'"
                >
                    <svg v-if="step.is_completed" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4 mb-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ step.order }}. {{ step.title }}
                        </h3>
                        <p class="text-gray-600 text-sm mt-1">{{ step.description }}</p>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span v-if="!step.is_completed" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                            :class="getPriorityBadgeClass(step.priority)"
                        >
                            {{ step.priority }}
                        </span>
                        <span v-if="step.estimated_time" class="text-xs text-gray-500">
                            {{ step.estimated_time }}
                        </span>
                    </div>
                </div>

                <!-- Benefits list -->
                <div class="mb-4 ml-0">
                    <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <li v-for="(benefit, index) in step.benefits" :key="index" class="flex items-start gap-2 text-sm text-gray-700">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <span>{{ benefit }}</span>
                        </li>
                    </ul>
                </div>

                <!-- Progress indicators -->
                <div v-if="step.progress_indicators && step.progress_indicators.length > 0" class="mb-4">
                    <div class="flex flex-wrap gap-2">
                        <span v-for="(indicator, index) in step.progress_indicators" :key="index"
                            class="inline-block px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded"
                        >
                            {{ indicator }}
                        </span>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <Link
                        :href="step.action_url"
                        class="inline-flex items-center px-4 py-2 rounded-lg font-medium transition-colors"
                        :class="[
                            step.is_completed
                                ? 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                                : 'bg-blue-600 text-white hover:bg-blue-700'
                        ]"
                    >
                        <svg v-if="!step.is_completed" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                        <svg v-else class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        {{ step.is_completed ? 'Completed' : step.action_label }}
                    </Link>
                    
                    <!-- Requires step badge -->
                    <div v-if="step.requires_step" class="text-xs text-gray-500">
                        Requires: {{ formatStepName(step.requires_step) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { useGetStarted } from '@/composables/useGetStarted';

const props = defineProps({
    step: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['toggle']);

const { completeStep, incompleteStep } = useGetStarted();

const toggleStep = async () => {
    if (props.step.is_completed) {
        await incompleteStep(props.step.id);
    } else {
        await completeStep(props.step.id);
    }
    emit('toggle', props.step.id, props.step.is_completed);
};

const getPriorityBadgeClass = (priority) => {
    const classes = {
        high: 'bg-red-100 text-red-700',
        medium: 'bg-yellow-100 text-yellow-700',
        low: 'bg-blue-100 text-blue-700',
    };
    return classes[priority] || classes.medium;
};

const formatStepName = (stepId) => {
    return stepId.split('_').map(word => 
        word.charAt(0).toUpperCase() + word.slice(1)
    ).join(' ');
};
</script>
