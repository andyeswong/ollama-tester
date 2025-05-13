<script setup lang="ts">
import SettingsLayout from '@/layouts/SettingsLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head } from '@inertiajs/vue3';

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const updatePassword = () => {
    form.put(route('password.update'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Update Password" />
    
    <SettingsLayout>
        <Card>
            <CardHeader>
                <CardTitle>Update Password</CardTitle>
                <CardDescription>
                    Ensure your account is using a strong password to stay secure.
                </CardDescription>
            </CardHeader>
            <form @submit.prevent="updatePassword">
                <CardContent class="space-y-6">
                    <div class="space-y-2">
                        <Label for="current_password">Current Password</Label>
                        <Input id="current_password" v-model="form.current_password" type="password" autocomplete="current-password" required />
                        <p v-if="form.errors.current_password" class="text-sm text-red-600">{{ form.errors.current_password }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password">New Password</Label>
                        <Input id="password" v-model="form.password" type="password" autocomplete="new-password" required />
                        <p v-if="form.errors.password" class="text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation">Confirm Password</Label>
                        <Input id="password_confirmation" v-model="form.password_confirmation" type="password" autocomplete="new-password" required />
                        <p v-if="form.errors.password_confirmation" class="text-sm text-red-600">{{ form.errors.password_confirmation }}</p>
                    </div>
                </CardContent>
                <CardFooter>
                    <Button type="submit" :disabled="form.processing">Save</Button>
                </CardFooter>
            </form>
        </Card>
    </SettingsLayout>
</template>
