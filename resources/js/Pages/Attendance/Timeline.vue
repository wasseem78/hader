<script setup>
// =============================================================================
// Attendance Timeline Page - Visual timeline of punches
// =============================================================================

import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    ChevronLeftIcon,
    ChevronRightIcon,
    ArrowDownTrayIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    records: {
        type: Array,
        default: () => [],
    },
    date: {
        type: String,
        default: () => new Date().toISOString().split('T')[0],
    },
});

const selectedDate = ref(props.date);

const groupedByHour = computed(() => {
    const grouped = {};
    props.records.forEach(record => {
        const hour = record.time.split(':')[0];
        if (!grouped[hour]) {
            grouped[hour] = [];
        }
        grouped[hour].push(record);
    });
    return grouped;
});

const hours = computed(() => {
    return Object.keys(groupedByHour.value).sort();
});

function changeDate(days) {
    const date = new Date(selectedDate.value);
    date.setDate(date.getDate() + days);
    selectedDate.value = date.toISOString().split('T')[0];
    router.get(route('attendance.timeline'), { date: selectedDate.value }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function formatHour(hour) {
    const h = parseInt(hour);
    if (h === 0) return '12 AM';
    if (h < 12) return `${h} AM`;
    if (h === 12) return '12 PM';
    return `${h - 12} PM`;
}

function getTypeColor(type) {
    const colors = {
        in: 'bg-green-500',
        out: 'bg-red-500',
        break_start: 'bg-amber-500',
        break_end: 'bg-blue-500',
    };
    return colors[type] || 'bg-gray-500';
}

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString(undefined, {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <Head title="Attendance Timeline" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Attendance Timeline
                </h2>
                <a
                    :href="`/attendance/export?date=${selectedDate}`"
                    class="btn btn-outline"
                >
                    <ArrowDownTrayIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    Export
                </a>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <!-- Date Navigation -->
                <div class="card mb-6">
                    <div class="p-4 flex items-center justify-between">
                        <button
                            @click="changeDate(-1)"
                            class="btn btn-outline p-2"
                        >
                            <ChevronLeftIcon class="w-5 h-5 rtl-flip" />
                        </button>
                        <div class="text-center">
                            <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ formatDate(selectedDate) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ records.length }} records
                            </p>
                        </div>
                        <button
                            @click="changeDate(1)"
                            :disabled="selectedDate >= new Date().toISOString().split('T')[0]"
                            class="btn btn-outline p-2 disabled:opacity-50"
                        >
                            <ChevronRightIcon class="w-5 h-5 rtl-flip" />
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="records.length === 0"
                    class="card text-center py-12"
                >
                    <p class="text-gray-500 dark:text-gray-400">
                        No attendance records for this date.
                    </p>
                </div>

                <!-- Timeline -->
                <div v-else class="space-y-6">
                    <div
                        v-for="hour in hours"
                        :key="hour"
                        class="card"
                    >
                        <div class="card-header bg-gray-50 dark:bg-gray-800/50">
                            <h3 class="font-medium text-gray-700 dark:text-gray-300">
                                {{ formatHour(hour) }}
                            </h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <div
                                v-for="record in groupedByHour[hour]"
                                :key="record.id"
                                class="p-4 flex items-center gap-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors"
                            >
                                <!-- Time indicator -->
                                <div class="flex items-center gap-2">
                                    <div :class="['w-2 h-2 rounded-full', getTypeColor(record.type)]"></div>
                                    <span class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                        {{ record.time }}
                                    </span>
                                </div>

                                <!-- User info -->
                                <div class="flex items-center gap-3 flex-1 min-w-0">
                                    <img
                                        :src="record.user.avatar || '/images/default-avatar.png'"
                                        :alt="record.user.name"
                                        class="w-8 h-8 rounded-full object-cover"
                                    />
                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ record.user.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ record.device }} • {{ record.verification }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Type and status -->
                                <div class="text-end">
                                    <span
                                        :class="[
                                            'badge',
                                            record.type === 'in' ? 'badge-success' : 'badge-danger'
                                        ]"
                                    >
                                        {{ record.type_label }}
                                    </span>
                                    <span
                                        v-if="record.is_late"
                                        class="badge badge-warning ms-2"
                                    >
                                        {{ record.late_minutes }}m late
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
