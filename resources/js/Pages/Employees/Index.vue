<script setup>
// =============================================================================
// Employees Index Page - Employee Roster
// =============================================================================

import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {
    PlusIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    UserCircleIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employees: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    canAddEmployee: {
        type: Boolean,
        default: true,
    },
});

const search = ref(props.filters.search || '');
const department = ref(props.filters.department || '');

function applyFilters() {
    router.get(route('employees.index'), {
        search: search.value,
        department: department.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearFilters() {
    search.value = '';
    department.value = '';
    router.get(route('employees.index'));
}
</script>

<template>
    <Head title="Employees" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Employees
                </h2>
                <Link
                    v-if="canAddEmployee"
                    href="/employees/create"
                    class="btn btn-primary"
                >
                    <PlusIcon class="w-5 h-5 ltr:mr-2 rtl:ml-2" />
                    Add Employee
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="card mb-6">
                    <div class="p-4 flex flex-col sm:flex-row gap-4">
                        <div class="flex-1 relative">
                            <MagnifyingGlassIcon
                                class="absolute ltr:left-3 rtl:right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
                            />
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search employees..."
                                class="form-input ltr:pl-10 rtl:pr-10"
                                @keyup.enter="applyFilters"
                            />
                        </div>
                        <select
                            v-model="department"
                            class="form-input sm:w-48"
                            @change="applyFilters"
                        >
                            <option value="">All Departments</option>
                            <option value="Operations">Operations</option>
                            <option value="HR">HR</option>
                            <option value="IT">IT</option>
                            <option value="Finance">Finance</option>
                        </select>
                        <button
                            v-if="search || department"
                            @click="clearFilters"
                            class="btn btn-outline"
                        >
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Empty State -->
                <div
                    v-if="employees.data.length === 0"
                    class="card text-center py-12"
                >
                    <UserCircleIcon class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                        No employees found
                    </h3>
                    <p class="mt-2 text-gray-500 dark:text-gray-400">
                        {{ search ? 'Try adjusting your search.' : 'Get started by adding your first employee.' }}
                    </p>
                </div>

                <!-- Employees Table -->
                <div v-else class="card">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Employee ID</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Status</th>
                                    <th>Device Enrollment</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="employee in employees.data" :key="employee.uuid">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <img
                                                :src="employee.avatar || '/images/default-avatar.png'"
                                                :alt="employee.name"
                                                class="w-10 h-10 rounded-full object-cover"
                                            />
                                            <div>
                                                <p class="font-medium">{{ employee.name }}</p>
                                                <p class="text-sm text-gray-500">{{ employee.email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="font-mono">{{ employee.employee_id || '-' }}</td>
                                    <td>{{ employee.department || '-' }}</td>
                                    <td>{{ employee.position || '-' }}</td>
                                    <td>
                                        <span
                                            :class="[
                                                'badge',
                                                employee.is_active ? 'badge-success' : 'badge-gray'
                                            ]"
                                        >
                                            {{ employee.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <span v-if="employee.fingerprint_count > 0" class="badge badge-info">
                                                {{ employee.fingerprint_count }} FP
                                            </span>
                                            <span v-if="employee.face_enrolled" class="badge badge-info">
                                                Face
                                            </span>
                                            <span v-if="employee.card_number" class="badge badge-info">
                                                Card
                                            </span>
                                            <span
                                                v-if="!employee.fingerprint_count && !employee.face_enrolled"
                                                class="text-gray-400"
                                            >
                                                Not enrolled
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <Link
                                            :href="`/employees/${employee.uuid}`"
                                            class="text-blue-600 hover:text-blue-700"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div
                        v-if="employees.meta?.last_page > 1"
                        class="border-t border-gray-200 dark:border-gray-700 px-4 py-3"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500">
                                Showing {{ employees.meta.from }} to {{ employees.meta.to }}
                                of {{ employees.meta.total }} employees
                            </p>
                            <div class="flex gap-2">
                                <Link
                                    v-for="link in employees.meta.links"
                                    :key="link.label"
                                    :href="link.url"
                                    :class="[
                                        'btn btn-outline px-3 py-1 text-sm',
                                        link.active && 'bg-blue-50 border-blue-500',
                                        !link.url && 'opacity-50 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
