<template>
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Tax Filing Calendar</h3>

        <!-- Month Navigation -->
        <div class="flex items-center justify-between mb-6">
            <button
                @click="previousMonth"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded"
            >
                ← Prev
            </button>
            <h4 class="text-xl font-semibold">{{ currentMonthName }} {{ currentYear }}</h4>
            <button
                @click="nextMonth"
                class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 rounded"
            >
                Next →
            </button>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-2">
            <!-- Day Headers -->
            <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
                 :key="day"
                 class="text-center text-sm font-medium text-gray-600 py-2"
            >
                {{ day }}
            </div>

            <!-- Calendar Days -->
            <div
                v-for="(day, index) in calendarDays"
                :key="index"
                class="aspect-square p-2 border rounded relative"
                :class="{
                    'bg-gray-50 text-gray-400': !day.isCurrentMonth,
                    'bg-blue-50 border-blue-300': day.isToday,
                    'hover:bg-gray-100 cursor-pointer': day.isCurrentMonth
                }"
                @click="selectDay(day)"
            >
                <div class="text-sm">{{ day.date }}</div>

                <!-- Deadline Indicators -->
                <div v-if="day.deadlines && day.deadlines.length > 0" class="mt-1">
                    <div
                        v-for="deadline in day.deadlines.slice(0, 2)"
                        :key="deadline.id"
                        class="text-xs px-1 rounded mb-1"
                        :class="{
                            'bg-red-100 text-red-700': deadline.priority === 'high',
                            'bg-yellow-100 text-yellow-700': deadline.priority === 'medium',
                            'bg-green-100 text-green-700': deadline.priority === 'low'
                        }"
                    >
                        {{ deadline.tax_type?.code || 'Tax' }}
                    </div>
                    <div v-if="day.deadlines.length > 2" class="text-xs text-gray-500">
                        +{{ day.deadlines.length - 2 }} more
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Day Details -->
        <div v-if="selectedDay && selectedDay.deadlines && selectedDay.deadlines.length > 0"
             class="mt-6 p-4 bg-gray-50 rounded-lg"
        >
            <h4 class="font-semibold text-gray-900 mb-3">
                Deadlines for {{ selectedDay.fullDate }}
            </h4>
            <div class="space-y-2">
                <div
                    v-for="deadline in selectedDay.deadlines"
                    :key="deadline.id"
                    class="p-3 bg-white border rounded"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium text-gray-900">{{ deadline.tax_type?.name }}</p>
                            <p class="text-sm text-gray-600">{{ deadline.filing_type }} Filing</p>
                        </div>
                        <Link
                            :href="taxReturnRoute(deadline.tax_type?.code)"
                            class="text-sm text-blue-600 hover:underline"
                        >
                            File Now
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="mt-6 flex items-center justify-center gap-6 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-100 border border-red-300 rounded"></div>
                <span class="text-gray-600">High Priority</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded"></div>
                <span class="text-gray-600">Medium Priority</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-100 border border-green-300 rounded"></div>
                <span class="text-gray-600">Low Priority</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    deadlines: {
        type: Array,
        default: () => [],
    },
});

const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedDay = ref(null);

const currentMonthName = computed(() => {
    return new Date(currentYear.value, currentMonth.value).toLocaleDateString('en-NG', { month: 'long' });
});

const calendarDays = computed(() => {
    const firstDay = new Date(currentYear.value, currentMonth.value, 1);
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    const prevLastDay = new Date(currentYear.value, currentMonth.value, 0);

    const days = [];
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    // Previous month days
    const firstDayOfWeek = firstDay.getDay();
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const date = prevLastDay.getDate() - i;
        days.push({
            date,
            isCurrentMonth: false,
            isToday: false,
            fullDate: new Date(currentYear.value, currentMonth.value - 1, date),
            deadlines: [],
        });
    }

    // Current month days
    for (let i = 1; i <= lastDay.getDate(); i++) {
        const dayDate = new Date(currentYear.value, currentMonth.value, i);
        dayDate.setHours(0, 0, 0, 0);

        const deadlinesForDay = getDeadlinesForDate(dayDate);

        days.push({
            date: i,
            isCurrentMonth: true,
            isToday: dayDate.getTime() === today.getTime(),
            fullDate: dayDate,
            deadlines: deadlinesForDay,
        });
    }

    // Next month days
    const remainingDays = 42 - days.length; // 6 rows * 7 days
    for (let i = 1; i <= remainingDays; i++) {
        days.push({
            date: i,
            isCurrentMonth: false,
            isToday: false,
            fullDate: new Date(currentYear.value, currentMonth.value + 1, i),
            deadlines: [],
        });
    }

    return days;
});

const getDeadlinesForDate = (date) => {
    return props.deadlines.filter(deadline => {
        const dueDate = new Date(deadline.due_date);
        dueDate.setHours(0, 0, 0, 0);
        return dueDate.getTime() === date.getTime();
    }).map(deadline => {
        const daysUntil = Math.ceil((new Date(deadline.due_date) - new Date()) / (1000 * 60 * 60 * 24));
        return {
            ...deadline,
            priority: daysUntil < 7 ? 'high' : daysUntil < 14 ? 'medium' : 'low',
        };
    });
};

const previousMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
    selectedDay.value = null;
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
    selectedDay.value = null;
};

const selectDay = (day) => {
    if (day.isCurrentMonth && day.deadlines.length > 0) {
        selectedDay.value = {
            ...day,
            fullDate: day.fullDate.toLocaleDateString('en-NG', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }),
        };
    }
};

const TAX_RETURN_ROUTES = {
    paye: '/business/paye/create',
    vat:  '/business/vat/create',
    wht:  '/business/wht/create',
    cit:  '/business/cit/create',
};

function taxReturnRoute(code) {
    return TAX_RETURN_ROUTES[code?.toLowerCase()] ?? '/business/tax-returns';
}
</script>
