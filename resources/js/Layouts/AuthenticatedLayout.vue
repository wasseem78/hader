<script setup>
// =============================================================================
// Authenticated Layout - Main Application Shell with Sidebar
// =============================================================================

import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    HomeIcon,
    DevicePhoneMobileIcon,
    UsersIcon,
    ClockIcon,
    ChartBarIcon,
    CalendarDaysIcon,
    Cog6ToothIcon,
    ArrowRightOnRectangleIcon,
    Bars3Icon,
    XMarkIcon,
    SunIcon,
    MoonIcon,
    LanguageIcon,
} from '@heroicons/vue/24/outline';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const isRtl = computed(() => page.props.direction === 'rtl');

const sidebarOpen = ref(false);
const darkMode = ref(localStorage.getItem('darkMode') === 'true');

const navigation = [
    { name: 'Dashboard', href: '/dashboard', icon: HomeIcon },
    { name: 'Devices', href: '/devices', icon: DevicePhoneMobileIcon },
    { name: 'Employees', href: '/employees', icon: UsersIcon },
    { name: 'Attendance', href: '/attendance', icon: ClockIcon },
    { name: 'Reports', href: '/reports', icon: ChartBarIcon },
    { name: 'Time Off', href: '/admin/time-off', icon: CalendarDaysIcon },
    { name: 'Settings', href: '/admin/settings', icon: Cog6ToothIcon },
];

function toggleDarkMode() {
    darkMode.value = !darkMode.value;
    localStorage.setItem('darkMode', darkMode.value);
    document.documentElement.classList.toggle('dark', darkMode.value);
}

function toggleLocale() {
    const newLocale = page.props.currentLocale === 'en' ? 'ar' : 'en';
    window.location.href = `/locale/${newLocale}`;
}

function isActive(href) {
    return page.url.startsWith(href);
}
</script>

<template>
    <div :dir="isRtl ? 'rtl' : 'ltr'" class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Mobile sidebar backdrop -->
        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-40 bg-black/50 lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <!-- Sidebar -->
        <aside
            :class="[
                'fixed z-50 inset-y-0 flex w-72 flex-col bg-white dark:bg-gray-800 border-e border-gray-200 dark:border-gray-700 transition-transform duration-300 lg:translate-x-0',
                sidebarOpen ? 'translate-x-0' : '-translate-x-full rtl:translate-x-full',
                'ltr:left-0 rtl:right-0'
            ]"
        >
            <!-- Logo -->
            <div class="flex h-16 items-center justify-between px-6 border-b border-gray-200 dark:border-gray-700">
                <Link href="/dashboard" class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                        <ClockIcon class="w-5 h-5 text-white" />
                    </div>
                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                        Attendance
                    </span>
                </Link>
                <button
                    @click="sidebarOpen = false"
                    class="lg:hidden text-gray-500 hover:text-gray-700"
                >
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <Link
                    v-for="item in navigation"
                    :key="item.name"
                    :href="item.href"
                    :class="[
                        'nav-link',
                        isActive(item.href) && 'active'
                    ]"
                >
                    <component :is="item.icon" class="w-5 h-5" />
                    <span>{{ item.name }}</span>
                </Link>
            </nav>

            <!-- User Section -->
            <div class="border-t border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center gap-3 mb-4">
                    <img
                        :src="user?.avatar || '/images/default-avatar.png'"
                        :alt="user?.name"
                        class="w-10 h-10 rounded-full object-cover"
                    />
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 dark:text-white truncate">
                            {{ user?.name }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400 truncate">
                            {{ user?.email }}
                        </p>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    class="nav-link w-full text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                >
                    <ArrowRightOnRectangleIcon class="w-5 h-5" />
                    <span>Logout</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="lg:ps-72">
            <!-- Top Header -->
            <header class="sticky top-0 z-30 h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 glass">
                <div class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 -ms-2 text-gray-500 hover:text-gray-700"
                    >
                        <Bars3Icon class="w-6 h-6" />
                    </button>

                    <!-- Page header slot -->
                    <div class="hidden lg:block">
                        <slot name="header" />
                    </div>

                    <!-- Right actions -->
                    <div class="flex items-center gap-2">
                        <!-- Locale toggle -->
                        <button
                            @click="toggleLocale"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                            :title="isRtl ? 'Switch to English' : 'التبديل إلى العربية'"
                        >
                            <LanguageIcon class="w-5 h-5" />
                        </button>

                        <!-- Dark mode toggle -->
                        <button
                            @click="toggleDarkMode"
                            class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <MoonIcon v-if="!darkMode" class="w-5 h-5" />
                            <SunIcon v-else class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- Mobile page header -->
                <div class="lg:hidden px-4 pb-4">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
