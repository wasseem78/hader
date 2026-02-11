<script setup>
// =============================================================================
// Dashboard Page - Tenant Overview with Stats and Recent Activity
// =============================================================================

import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    UsersIcon,
    DevicePhoneMobileIcon,
    ClockIcon,
    CalendarDaysIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            totalEmployees: 0,
            totalDevices: 0,
            onlineDevices: 0,
            todayPresent: 0,
            todayLate: 0,
            todayAbsent: 0,
            pendingRequests: 0,
        }),
    },
    recentActivity: {
        type: Array,
        default: () => [],
    },
    todayTimeline: {
        type: Array,
        default: () => [],
    },
});

const attendanceRate = computed(() => {
    if (props.stats.totalEmployees === 0) return 0;
    return Math.round((props.stats.todayPresent / props.stats.totalEmployees) * 100);
});

const statCards = computed(() => [
    {
        title: 'Total Employees',
        value: props.stats.totalEmployees,
        icon: UsersIcon,
        color: 'blue',
        link: '/employees',
    },
    {
        title: 'Devices Online',
        value: `${props.stats.onlineDevices}/${props.stats.totalDevices}`,
        icon: DevicePhoneMobileIcon,
        color: 'green',
        link: '/devices',
    },
    {
        title: 'Present Today',
        value: props.stats.todayPresent,
        icon: ClockIcon,
        color: 'emerald',
        sublabel: `${attendanceRate.value}% attendance`,
        link: '/attendance',
    },
    {
        title: 'Late Today',
        value: props.stats.todayLate,
        icon: ArrowTrendingDownIcon,
        color: 'amber',
        link: '/attendance?filter=late',
    },
]);

const colorClasses = {
    blue: 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
    green: 'bg-green-50 text-green-600 dark:bg-green-900/30 dark:text-green-400',
    emerald: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400',
    amber: 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
    red: 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
};

function formatTime(timestamp) {
    return new Date(timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ $t('Dashboard') }}
            </h2>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <Link
                        v-for="stat in statCards"
                        :key="stat.title"
                        :href="stat.link"
                        class="card p-6 hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                    {{ stat.title }}
                                </p>
                                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-white">
                                    {{ stat.value }}
                                </p>
                                <p v-if="stat.sublabel" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    {{ stat.sublabel }}
                                </p>
                            </div>
                            <div :class="['p-3 rounded-xl', colorClasses[stat.color]]">
                                <component :is="stat.icon" class="w-6 h-6" />
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Today's Timeline -->
                    <div class="lg:col-span-2 card">
                        <div class="card-header flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Today's Timeline
                            </h3>
                            <Link href="/attendance/timeline" class="text-sm text-blue-600 hover:text-blue-700">
                                View All →
                            </Link>
                        </div>
                        <div class="card-body">
                            <div v-if="todayTimeline.length === 0" class="text-center py-8 text-gray-500">
                                No attendance records for today yet.
                            </div>
                            <div v-else class="space-y-4 max-h-96 overflow-y-auto scrollbar-thin">
                                <div
                                    v-for="entry in todayTimeline"
                                    :key="entry.id"
                                    class="flex items-center gap-4 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50"
                                >
                                    <img
                                        :src="entry.user.avatar || '/images/default-avatar.png'"
                                        :alt="entry.user.name"
                                        class="w-10 h-10 rounded-full object-cover"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ entry.user.name }}
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ entry.type_label }} • {{ entry.device }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            {{ formatTime(entry.time) }}
                                        </p>
                                        <span
                                            v-if="entry.is_late"
                                            class="badge-warning"
                                        >
                                            {{ entry.late_minutes }} min late
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Recent Activity -->
                    <div class="space-y-6">
                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Quick Actions
                                </h3>
                            </div>
                            <div class="card-body space-y-3">
                                <Link href="/devices" class="btn btn-outline w-full justify-between">
                                    <span>Sync Devices</span>
                                    <DevicePhoneMobileIcon class="w-5 h-5" />
                                </Link>
                                <Link href="/reports" class="btn btn-outline w-full justify-between">
                                    <span>Generate Report</span>
                                    <CalendarDaysIcon class="w-5 h-5" />
                                </Link>
                                <Link href="/employees/create" class="btn btn-primary w-full justify-between">
                                    <span>Add Employee</span>
                                    <UsersIcon class="w-5 h-5" />
                                </Link>
                            </div>
                        </div>

                        <!-- Pending Time-Off Requests -->
                        <div v-if="stats.pendingRequests > 0" class="card">
                            <div class="card-header flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                    Pending Requests
                                </h3>
                                <span class="badge-warning">{{ stats.pendingRequests }}</span>
                            </div>
                            <div class="card-body">
                                <Link href="/admin/time-off?status=pending" class="btn btn-outline w-full">
                                    Review Requests
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
