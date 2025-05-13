<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItemType } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { CreditCard, KeyRound, PaintBucket, User } from 'lucide-vue-next';

// For route active state checking
const page = usePage();

// Define settings navigation items
const settingsNavItems = [
    {
        name: 'Profile',
        href: route('profile.edit'),
        icon: User,
        description: 'Manage your account information and preferences',
    },
    {
        name: 'Password',
        href: route('password.edit'),
        icon: KeyRound,
        description: 'Update your password and security settings',
    },
    {
        name: 'Appearance',
        href: route('appearance'),
        icon: PaintBucket,
        description: 'Customize the appearance of your dashboard',
    },
    {
        name: 'Integration',
        href: route('settings.broadcast'),
        icon: CreditCard,
        description: 'Configure real-time updates with Pusher',
    },
];

// Set up breadcrumbs
const breadcrumbs: BreadcrumbItemType[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
    },
    {
        title: 'Settings',
        href: route('profile.edit'),
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row gap-6">
                <!-- Settings Navigation Sidebar -->
                <div class="w-full md:w-64 flex-shrink-0">
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50 border-b">
                            <h2 class="text-lg font-medium text-gray-800">Settings</h2>
                        </div>
                        <div class="divide-y">
                            <Link
                                v-for="item in settingsNavItems"
                                :key="item.name"
                                :href="item.href"
                                class="flex items-center px-6 py-4 hover:bg-gray-50 transition-colors duration-150"
                                :class="{ 'bg-gray-50': page.url.startsWith(item.href) }"
                            >
                                <component :is="item.icon" class="h-5 w-5 text-gray-500 mr-3" />
                                <span class="text-sm font-medium text-gray-700">{{ item.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Content Area -->
                <div class="flex-1">
                    <slot></slot>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 