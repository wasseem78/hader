<script setup>
// =============================================================================
// Devices Index Page - List and Manage ZKTeco Devices
// =============================================================================

import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    PlusIcon,
    ArrowPathIcon,
    SignalIcon,
    SignalSlashIcon,
    EllipsisVerticalIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    devices: {
        type: Array,
        default: () => [],
    },
    canAddDevice: {
        type: Boolean,
        default: true,
    },
});

const syncing = ref(null);

function getStatusColor(status) {
    const colors = {
        online: 'bg-green-500',
        offline: 'bg-gray-400',
        syncing: 'bg-blue-500 animate-pulse',
        error: 'bg-red-500',
    };
    return colors[status] || colors.offline;
}

function getStatusLabel(status) {
    const labels = {
        online: 'Online',
        offline: 'Offline',
        syncing: 'Syncing',
        error: 'Error',
    };
    return labels[status] || 'Unknown';
}

async function testConnection(device) {
    syncing.value = device.uuid;
    try {
        await router.post(route('devices.test', device.uuid), {}, {
            preserveScroll: true,
            onFinish: () => {
                syncing.value = null;
            },
        });
    } catch (error) {
        syncing.value = null;
    }
}

async function syncDevice(device) {
    syncing.value = device.uuid;
    await router.post(route('devices.sync', device.uuid), {}, {
        preserveScroll: true,
        onFinish: () => {
            syncing.value = null;
        },
    });
}

function formatLastSeen(date) {
    if (!date) return 'Never';
    return new Date(date).toLocaleString();
}
</script>

<template>
    <Head title="Devices" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Devices
                </h2>
                <Link
                    v-if="canAddDevice"
                    href="/devices/create"
                    class="btn btn-primary"
                >
                    <PlusIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    Add Device
                </Link>
                <button
                    v-else
                    disabled
                    class="btn btn-secondary opacity-50 cursor-not-allowed"
                    title="Device limit reached. Upgrade your plan."
                >
                    <PlusIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    Add Device
                </button>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Empty State -->
                <div
                    v-if="devices.length === 0"
                    class="card text-center py-12"
                >
                    <SignalSlashIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                        No devices yet
                    </h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        Get started by adding your first ZKTeco device.
                    </p>
                    <Link
                        href="/devices/create"
                        class="btn btn-primary mt-4"
                    >
                        <PlusIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                        Add Your First Device
                    </Link>
                </div>

                <!-- Devices Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="device in devices"
                        :key="device.uuid"
                        class="card hover:shadow-md transition-shadow duration-200"
                    >
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div
                                        :class="['w-3 h-3 rounded-full', getStatusColor(device.status)]"
                                    ></div>
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white">
                                            {{ device.name }}
                                        </h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ device.model || 'ZKTeco Device' }}
                                        </p>
                                    </div>
                                </div>
                                <Link
                                    :href="`/devices/${device.uuid}/edit`"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <EllipsisVerticalIcon class="w-5 h-5" />
                                </Link>
                            </div>

                            <!-- Details -->
                            <div class="mt-4 space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">IP Address</span>
                                    <span class="font-mono text-gray-900 dark:text-white">
                                        {{ device.ip_address }}:{{ device.port }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Location</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ device.location || '-' }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Last Seen</span>
                                    <span class="text-gray-900 dark:text-white">
                                        {{ formatLastSeen(device.last_seen) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500 dark:text-gray-400">Today's Logs</span>
                                    <span class="font-medium text-gray-900 dark:text-white">
                                        {{ device.today_logs || 0 }}
                                    </span>
                                </div>
                            </div>

                            <!-- Status Badge -->
                            <div class="mt-4">
                                <span
                                    :class="[
                                        'badge',
                                        device.status === 'online' ? 'badge-success' :
                                        device.status === 'error' ? 'badge-danger' :
                                        device.status === 'syncing' ? 'badge-info' : 'badge-gray'
                                    ]"
                                >
                                    {{ getStatusLabel(device.status) }}
                                </span>
                            </div>

                            <!-- Actions -->
                            <div class="mt-4 flex gap-2">
                                <button
                                    @click="testConnection(device)"
                                    :disabled="syncing === device.uuid"
                                    class="btn btn-outline flex-1"
                                >
                                    <SignalIcon class="w-4 h-4 ltr:mr-1 rtl:ml-1" />
                                    Test
                                </button>
                                <button
                                    @click="syncDevice(device)"
                                    :disabled="syncing === device.uuid"
                                    class="btn btn-primary flex-1"
                                >
                                    <ArrowPathIcon
                                        :class="['w-4 h-4 ltr:mr-1 rtl:ml-1', syncing === device.uuid && 'animate-spin']"
                                    />
                                    Sync
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
