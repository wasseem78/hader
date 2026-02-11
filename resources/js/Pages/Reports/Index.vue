<script setup>
// =============================================================================
// Reports Index Page - Attendance Reports with Filters
// =============================================================================

import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';
import {
    ArrowDownTrayIcon,
    CalendarDaysIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    summary: {
        type: Object,
        default: () => ({}),
    },
    dailyData: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const dateFrom = ref(props.filters.from || new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]);
const dateTo = ref(props.filters.to || new Date().toISOString().split('T')[0]);
const reportType = ref(props.filters.type || 'daily');

const chartData = computed(() => ({
    labels: props.dailyData.map(d => d.date),
    datasets: [
        {
            label: 'Present',
            data: props.dailyData.map(d => d.present),
            backgroundColor: 'rgba(34, 197, 94, 0.7)',
            borderRadius: 4,
        },
        {
            label: 'Late',
            data: props.dailyData.map(d => d.late),
            backgroundColor: 'rgba(245, 158, 11, 0.7)',
            borderRadius: 4,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1,
            },
        },
    },
};

function applyFilters() {
    router.get(route('reports.index'), {
        from: dateFrom.value,
        to: dateTo.value,
        type: reportType.value,
    }, {
        preserveState: true,
    });
}

function exportReport(format) {
    window.location.href = `/reports/export?from=${dateFrom.value}&to=${dateTo.value}&format=${format}`;
}
</script>

<template>
    <Head title="Reports" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Reports
                </h2>
                <div class="flex gap-2">
                    <button @click="exportReport('csv')" class="btn btn-outline">
                        <ArrowDownTrayIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                        Export CSV
                    </button>
                    <button @click="exportReport('pdf')" class="btn btn-primary">
                        <ArrowDownTrayIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                        Export PDF
                    </button>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="card mb-6">
                    <div class="p-4 flex flex-wrap gap-4 items-end">
                        <div>
                            <label class="form-label">From Date</label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="form-input"
                            />
                        </div>
                        <div>
                            <label class="form-label">To Date</label>
                            <input
                                v-model="dateTo"
                                type="date"
                                class="form-input"
                            />
                        </div>
                        <div>
                            <label class="form-label">Report Type</label>
                            <select v-model="reportType" class="form-input">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <button @click="applyFilters" class="btn btn-primary">
                            Generate Report
                        </button>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Days</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ summary.period?.total_days || 0 }}
                        </p>
                    </div>
                    <div class="card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total Records</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ summary.attendance?.total_records || 0 }}
                        </p>
                    </div>
                    <div class="card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Late Arrivals</p>
                        <p class="text-3xl font-bold text-amber-600">
                            {{ summary.punctuality?.late_count || 0 }}
                        </p>
                        <p class="text-sm text-gray-500 mt-1">
                            Avg: {{ summary.punctuality?.average_late_minutes || 0 }} min
                        </p>
                    </div>
                    <div class="card p-6">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Overtime Hours</p>
                        <p class="text-3xl font-bold text-blue-600">
                            {{ summary.overtime?.total_hours || 0 }}
                        </p>
                    </div>
                </div>

                <!-- Chart -->
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <ChartBarIcon class="w-5 h-5" />
                            Attendance Overview
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="h-80">
                            <Bar
                                v-if="dailyData.length > 0"
                                :data="chartData"
                                :options="chartOptions"
                            />
                            <div
                                v-else
                                class="h-full flex items-center justify-center text-gray-500"
                            >
                                No data available for selected period.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Daily Breakdown Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <CalendarDaysIcon class="w-5 h-5" />
                            Daily Breakdown
                        </h3>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-center">Present</th>
                                    <th class="text-center">Late</th>
                                    <th class="text-center">On Time</th>
                                    <th class="text-center">Attendance Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="day in dailyData" :key="day.date">
                                    <td class="font-medium">{{ day.date }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-success">{{ day.present }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-warning">{{ day.late }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ day.on_time }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                                <div
                                                    class="bg-green-500 h-2 rounded-full"
                                                    :style="{ width: `${(day.present / (summary.attendance?.total_records || 1)) * 100}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
