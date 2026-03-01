<template>
    <BusinessLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Compliance Calendar
                        <span
                            class="ml-2 inline-flex h-4 w-4 items-center justify-center rounded-full bg-blue-100 text-[10px] font-bold text-blue-700"
                            title="Deadlines are generated from your business settings and filings. Mark items complete and upload proof to keep records audit-ready."
                        >
                            i
                        </span>
                    </h1>
                    <p class="text-gray-600 mt-1">Track your tax deadlines and compliance requirements</p>
                </div>
                <div class="flex items-center space-x-2">
                    <button
                        @click="previousMonth"
                        class="p-2 hover:bg-gray-100 rounded-lg transition"
                    >
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <span class="text-lg font-semibold text-gray-900 min-w-48 text-center">
                        {{ currentMonth.toLocaleDateString('en-NG', { month: 'long', year: 'numeric' }) }}
                    </span>
                    <button
                        @click="nextMonth"
                        class="p-2 hover:bg-gray-100 rounded-lg transition"
                    >
                        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid md:grid-cols-4 gap-4">
                <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                    <p class="text-sm text-red-600 font-medium">Overdue</p>
                    <p class="text-3xl font-bold text-red-900">{{ overdueCount }}</p>
                </div>
                <div class="bg-orange-50 rounded-lg p-4 border border-orange-200">
                    <p class="text-sm text-orange-600 font-medium">Due This Week</p>
                    <p class="text-3xl font-bold text-orange-900">{{ thisWeekCount }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <p class="text-sm text-yellow-600 font-medium">Due Soon</p>
                    <p class="text-3xl font-bold text-yellow-900">{{ soonCount }}</p>
                </div>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <p class="text-sm text-blue-600 font-medium">Total This Month</p>
                    <p class="text-3xl font-bold text-blue-900">{{ monthCount }}</p>
                </div>
            </div>

            <!-- Deadlines List -->
            <div class="space-y-4">
                <div
                    v-if="deadlines.length > 0"
                    class="space-y-3"
                >
                    <div
                        v-for="deadline in deadlines"
                        :key="deadline.id"
                        class="bg-white rounded-lg shadow p-5 hover:shadow-lg transition"
                        :class="getDeadlineClass(deadline)"
                    >
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <!-- Date and Badge -->
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-sm font-bold text-gray-600">
                                        {{ formatDate(deadline.due_date) }}
                                    </span>
                                    <span
                                        class="px-2 py-1 rounded text-xs font-bold"
                                        :class="getUrgencyBadge(deadline)"
                                    >
                                        {{ getUrgencyLabel(deadline) }}
                                    </span>
                                    <span
                                        v-if="deadline.is_completed"
                                        class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-800"
                                    >
                                        ✓ Completed
                                    </span>
                                </div>

                                <!-- Deadline Type and Description -->
                                <h3 class="text-lg font-bold text-gray-900">{{ deadline.deadline_type }}</h3>
                                <p class="text-gray-600 text-sm mt-1">{{ deadline.description }}</p>

                                <!-- Details -->
                                <div class="grid md:grid-cols-2 gap-4 mt-3 pt-3 border-t border-gray-100">
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Period</p>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ formatDate(deadline.period_start) }} - {{ formatDate(deadline.period_end) }}
                                        </p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 uppercase tracking-wide">Days Until</p>
                                        <p class="text-sm font-medium" :class="getDaysClass(deadline)">
                                            {{ getDaysUntil(deadline.due_date) }} days
                                        </p>
                                    </div>
                                </div>

                                <!-- Files -->
                                <div v-if="deadline.attachments && deadline.attachments.length > 0" class="mt-3 flex flex-wrap gap-2">
                                    <a
                                        v-for="attachment in deadline.attachments"
                                        :key="attachment.id"
                                        :href="`/storage/${attachment.file_path}`"
                                        target="_blank"
                                        class="text-xs bg-blue-50 text-blue-600 px-2 py-1 rounded hover:bg-blue-100 transition flex items-center gap-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8m0 0l-6 6m6-6l6 6" />
                                        </svg>
                                        {{ attachment.file_name }}
                                    </a>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-2 ml-4">
                                <button
                                    v-if="!deadline.is_completed"
                                    @click="markComplete(deadline)"
                                    :disabled="completingId === deadline.id"
                                    class="px-3 py-2 bg-green-50 text-green-600 hover:bg-green-100 rounded text-sm font-medium transition disabled:opacity-50"
                                >
                                    {{ completingId === deadline.id ? 'Saving...' : 'Mark Done' }}
                                </button>
                                <button
                                    @click="showUploadModal(deadline)"
                                    class="px-3 py-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded text-sm font-medium transition"
                                >
                                    📎
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No deadlines this month</h3>
                    <p class="text-gray-600">Your compliance calendar will update based on your business settings</p>
                </div>
            </div>

            <!-- Upload Modal -->
            <Teleport to="#app">
                <div v-if="uploadingDeadline" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
                    <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold text-gray-900">Upload Proof</h2>
                            <button
                                @click="uploadingDeadline = null"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <p class="text-sm text-gray-600 mb-4">
                            Upload proof of payment or completion for {{ uploadingDeadline?.deadline_type }}
                        </p>

                        <div
                            @drop="handleFileDrop"
                            @dragover.prevent
                            @dragenter.prevent
                            class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center cursor-pointer hover:border-blue-500 transition"
                        >
                            <input
                                ref="fileInput"
                                type="file"
                                @change="handleFileSelect"
                                class="hidden"
                                accept=".pdf,.jpg,.jpeg,.png"
                            />
                            <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm font-medium text-gray-900">Click to upload or drag and drop</p>
                            <p class="text-xs text-gray-600">PDF, JPG or PNG (max 5MB)</p>
                        </div>

                        <button
                            @click="$refs.fileInput.click()"
                            class="w-full mt-4 px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition"
                        >
                            Choose File
                        </button>

                        <div v-if="selectedFile" class="mt-4 p-3 bg-blue-50 rounded">
                            <p class="text-sm text-blue-900">{{ selectedFile.name }}</p>
                        </div>

                        <div class="flex gap-3 mt-6">
                            <button
                                @click="uploadingDeadline = null"
                                class="flex-1 px-4 py-2 border border-gray-300 hover:bg-gray-50 rounded-lg font-medium transition"
                            >
                                Cancel
                            </button>
                            <button
                                @click="uploadFile"
                                :disabled="!selectedFile || uploading"
                                class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition disabled:opacity-50"
                            >
                                {{ uploading ? 'Uploading...' : 'Upload' }}
                            </button>
                        </div>
                    </div>
                </div>
            </Teleport>

            <!-- Success Message -->
            <Teleport to="#app">
                <div
                    v-if="successMessage"
                    class="fixed top-4 right-4 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg shadow-lg"
                >
                    {{ successMessage }}
                </div>
            </Teleport>
        </div>
    </BusinessLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import BusinessLayout from '@/Layouts/BusinessLayout.vue'

const props = defineProps({
    deadlines: Array,
})

const currentMonth = ref(new Date())
const uploadingDeadline = ref(null)
const selectedFile = ref(null)
const uploading = ref(false)
const completingId = ref(null)
const successMessage = ref('')
const fileInput = ref(null)

const deadlines = computed(() => {
    return (props.deadlines || []).filter(d => {
        const dueDate = new Date(d.due_date)
        return dueDate.getMonth() === currentMonth.value.getMonth() &&
               dueDate.getFullYear() === currentMonth.value.getFullYear()
    }).sort((a, b) => new Date(a.due_date) - new Date(b.due_date))
})

const overdueCount = computed(() => {
    return (props.deadlines || []).filter(d => {
        const now = new Date()
        const dueDate = new Date(d.due_date)
        return dueDate < now && !d.is_completed
    }).length
})

const thisWeekCount = computed(() => {
    const now = new Date()
    const weekFromNow = new Date()
    weekFromNow.setDate(weekFromNow.getDate() + 7)

    return (props.deadlines || []).filter(d => {
        const dueDate = new Date(d.due_date)
        return dueDate >= now && dueDate <= weekFromNow && !d.is_completed
    }).length
})

const soonCount = computed(() => {
    const now = new Date()
    const monthFromNow = new Date()
    monthFromNow.setDate(monthFromNow.getDate() + 30)

    return (props.deadlines || []).filter(d => {
        const dueDate = new Date(d.due_date)
        return dueDate > new Date(new Date().getTime() + 7 * 24 * 60 * 60 * 1000) &&
               dueDate <= monthFromNow &&
               !d.is_completed
    }).length
})

const monthCount = computed(() => deadlines.value.filter(d => !d.is_completed).length)

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-NG', {
        year: 'numeric',
        month: 'short',
        day: '2-digit',
    })
}

const getDaysUntil = (dueDate) => {
    const now = new Date()
    const due = new Date(dueDate)
    const diff = Math.ceil((due - now) / (1000 * 60 * 60 * 24))
    return diff
}

const getDaysClass = (deadline) => {
    const days = getDaysUntil(deadline.due_date)
    if (days < 0) return 'text-red-600 font-bold'
    if (days < 7) return 'text-orange-600 font-bold'
    if (days < 14) return 'text-yellow-600'
    return 'text-green-600'
}

const getUrgencyLabel = (deadline) => {
    const days = getDaysUntil(deadline.due_date)
    if (days < 0) return 'OVERDUE'
    if (days === 0) return 'TODAY'
    if (days < 3) return 'URGENT'
    if (days < 7) return 'THIS WEEK'
    if (days < 14) return 'COMING UP'
    return 'UPCOMING'
}

const getUrgencyBadge = (deadline) => {
    const days = getDaysUntil(deadline.due_date)
    if (days < 0) return 'bg-red-100 text-red-800'
    if (days === 0) return 'bg-red-100 text-red-800'
    if (days < 3) return 'bg-orange-100 text-orange-800'
    if (days < 7) return 'bg-yellow-100 text-yellow-800'
    return 'bg-blue-100 text-blue-800'
}

const getDeadlineClass = (deadline) => {
    if (deadline.is_completed) return 'opacity-60'
    return ''
}

const previousMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1)
}

const nextMonth = () => {
    currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1)
}

const showUploadModal = (deadline) => {
    uploadingDeadline.value = deadline
    selectedFile.value = null
}

const handleFileSelect = (event) => {
    selectedFile.value = event.target.files[0]
}

const handleFileDrop = (event) => {
    selectedFile.value = event.dataTransfer.files[0]
}

const markComplete = (deadline) => {
    completingId.value = deadline.id

    fetch(`/business/compliance/${deadline.id}/complete`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
    })
    .then(() => {
        successMessage.value = 'Deadline marked as complete'
        setTimeout(() => {
            window.location.reload()
        }, 1500)
    })
    .finally(() => {
        completingId.value = null
    })
}

const uploadFile = () => {
    if (!selectedFile.value || !uploadingDeadline.value) return

    const formData = new FormData()
    formData.append('file', selectedFile.value)

    uploading.value = true

    fetch(`/business/compliance/${uploadingDeadline.value.id}/upload`, {
        method: 'POST',
        headers: {
            'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]')?.content,
        },
        body: formData,
    })
    .then(() => {
        successMessage.value = 'File uploaded successfully'
        uploadingDeadline.value = null
        selectedFile.value = null
        setTimeout(() => {
            window.location.reload()
        }, 1500)
    })
    .catch(() => {
        successMessage.value = 'Upload failed. Please try again.'
    })
    .finally(() => {
        uploading.value = false
    })
}
</script>
