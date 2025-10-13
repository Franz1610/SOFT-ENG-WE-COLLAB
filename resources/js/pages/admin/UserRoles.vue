<template>
    <AppLayout>
        <Head title="User Role Management" />
        
        <div class="min-h-screen" style="background: #FFFAE9; padding: 2rem 1rem;">
            <div class="max-w-6xl mx-auto">
                <div class="mb-8">
                    <h1 class="text-3xl font-bold" style="color: #4b824b;">User Role Management</h1>
                    <p class="mt-2" style="color: #344C34;">Manage user roles and permissions</p>
                </div>

                <!-- Role Information Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <h3 class="font-semibold text-blue-900">User</h3>
                        <p class="text-sm text-blue-700 mt-1">Regular users who can make bookings</p>
                    </div>
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <h3 class="font-semibold text-green-900">Admin</h3>
                        <p class="text-sm text-green-700 mt-1">Access to Dashboard, Bookings, Rooms, and User Management</p>
                    </div>
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <h3 class="font-semibold text-purple-900">Admin Officer (Senior)</h3>
                        <p class="text-sm text-purple-700 mt-1">Highest level - Full access including Finance and Role Management</p>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="shadow rounded-lg overflow-hidden" style="background-color: #FFFAE9; border: 1px solid #4b824b;">
                    <div class="px-6 py-4" style="background-color: #4b824b; color: #FFFAE9; border-bottom: 1px solid #4b824b;">
                        <h2 class="text-lg font-medium">All Users</h2>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead style="background-color: #4b824b;">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #FFFAE9;">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #FFFAE9;">
                                        Current Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #FFFAE9;">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #FFFAE9;">
                                        Created
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider" style="color: #FFFAE9;">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody style="background-color: #FFFAE9;">
                                <tr v-for="user in users" :key="user.id" style="border-bottom: 1px solid #4b824b;">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium" style="color: #4b824b;">{{ user.name }}</div>
                                            <div class="text-sm" style="color: #344C34;">{{ user.email }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getRoleBadgeClass(user.role)" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                            {{ user.role_display }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="user.is_blocked ? 'text-red-600' : 'text-green-600'" class="text-sm font-medium">
                                            {{ user.is_blocked ? 'Blocked' : 'Active' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm" style="color: #344C34;">
                                        {{ new Date(user.created_at).toLocaleDateString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <select 
                                            v-model="user.role" 
                                            @change="updateUserRole(user)"
                                            class="mr-2 border rounded px-2 py-1 text-sm"
                                            style="border-color: #4b824b; color: #4b824b; background-color: #FFFAE9;"
                                        >
                                            <option v-for="(label, role) in available_roles" :key="role" :value="role">
                                                {{ label }}
                                            </option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="mt-8 border rounded-lg p-6" style="background-color: #4b824b; border-color: #4b824b; color: #FFFAE9;">
                    <h3 class="text-lg font-medium mb-4">Instructions for XAMPP/phpMyAdmin</h3>
                    <div class="space-y-2 text-sm opacity-90">
                        <p><strong>To manually set user roles in phpMyAdmin:</strong></p>
                        <ol class="list-decimal list-inside space-y-1 ml-4">
                            <li>Open phpMyAdmin and select your database (wecollab2)</li>
                            <li>Navigate to the 'users' table</li>
                            <li>Click 'Edit' on the user you want to modify</li>
                            <li>Set the 'role' field to one of: <code class="bg-black bg-opacity-20 px-1 rounded">user</code>, <code class="bg-black bg-opacity-20 px-1 rounded">admin</code>, or <code class="bg-black bg-opacity-20 px-1 rounded">admin_officer</code></li>
                            <li>Set 'is_admin' to 1 for admin and admin_officer roles, 0 for user role</li>
                            <li>Click 'Go' to save changes</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    role_display: string;
    is_blocked: boolean;
    created_at: string;
}

defineProps<{
    users: User[];
    available_roles: Record<string, string>;
}>();

const getRoleBadgeClass = (role: string) => {
    switch (role) {
        case 'admin':
            return 'bg-green-200 text-green-800 border border-green-300';
        case 'admin_officer':
            return 'bg-purple-200 text-purple-800 border border-purple-300';
        default:
            return 'bg-blue-200 text-blue-800 border border-blue-300';
    }
};

const updateUserRole = (user: User) => {
    router.post(`/admin/users/${user.id}/role`, {
        role: user.role
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        }
    });
};
</script>