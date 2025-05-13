<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { type BreadcrumbItem } from '@/types';
import { onMounted, ref } from 'vue';

// Simple theme management with localStorage instead of cookies
const theme = ref('light');

onMounted(() => {
    // Get theme from localStorage or default to light
    const savedTheme = localStorage.getItem('theme') || 'light';
    theme.value = savedTheme;
    applyTheme(savedTheme);
});

const applyTheme = (value: string) => {
    // Save to localStorage
    localStorage.setItem('theme', value);
    
    // Apply theme to document
    if (value === 'dark') {
        document.documentElement.classList.add('dark');
    } else if (value === 'light') {
        document.documentElement.classList.remove('dark');
    } else {
        // System preference
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Appearance settings',
        href: '/settings/appearance',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Appearance settings" />

        <SettingsLayout>
            <Card>
                <CardHeader>
                    <CardTitle>Appearance</CardTitle>
                    <CardDescription>
                        Customize the appearance of your dashboard
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-6">
                    <div class="space-y-4">
                        <Label>Theme</Label>
                        <div class="grid grid-cols-3 gap-4">
                            <!-- Light Theme Option -->
                            <div 
                                @click="applyTheme('light')"
                                class="flex flex-col items-center justify-between rounded-md border-2 p-4 hover:bg-gray-50 cursor-pointer"
                                :class="{'border-blue-500': theme === 'light', 'border-muted': theme !== 'light'}"
                            >
                                <div class="rounded-md border border-gray-200 bg-white p-2">
                                    <div class="space-y-2">
                                        <div class="h-2 w-8 rounded-lg bg-gray-900"></div>
                                        <div class="h-2 w-[80px] rounded-lg bg-gray-300"></div>
                                        <div class="h-2 w-[120px] rounded-lg bg-gray-300"></div>
                                    </div>
                                </div>
                                <span class="mt-2 font-medium">Light</span>
                            </div>
                            
                            <!-- Dark Theme Option -->
                            <div 
                                @click="applyTheme('dark')"
                                class="flex flex-col items-center justify-between rounded-md border-2 p-4 hover:bg-gray-800 cursor-pointer bg-gray-900"
                                :class="{'border-blue-500': theme === 'dark', 'border-muted': theme !== 'dark'}"
                            >
                                <div class="rounded-md border border-gray-700 bg-gray-900 p-2">
                                    <div class="space-y-2">
                                        <div class="h-2 w-8 rounded-lg bg-gray-100"></div>
                                        <div class="h-2 w-[80px] rounded-lg bg-gray-700"></div>
                                        <div class="h-2 w-[120px] rounded-lg bg-gray-700"></div>
                                    </div>
                                </div>
                                <span class="mt-2 font-medium text-white">Dark</span>
                            </div>
                            
                            <!-- System Theme Option -->
                            <div 
                                @click="applyTheme('system')"
                                class="flex flex-col items-center justify-between rounded-md border-2 p-4 hover:bg-gray-50 cursor-pointer"
                                :class="{'border-blue-500': theme === 'system', 'border-muted': theme !== 'system'}"
                            >
                                <div class="rounded-md border border-gray-200 bg-white p-2">
                                    <div class="flex space-x-2">
                                        <div class="space-y-2 w-1/2">
                                            <div class="h-2 w-6 rounded-lg bg-gray-300"></div>
                                            <div class="h-2 w-10 rounded-lg bg-gray-300"></div>
                                        </div>
                                        <div class="space-y-2 w-1/2 bg-gray-900 rounded-md p-1">
                                            <div class="h-2 w-6 rounded-lg bg-gray-500"></div>
                                            <div class="h-2 w-10 rounded-lg bg-gray-500"></div>
                                        </div>
                                    </div>
                                </div>
                                <span class="mt-2 font-medium">System</span>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </SettingsLayout>
    </AppLayout>
</template>
