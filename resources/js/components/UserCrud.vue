<script setup lang="ts">
import { defineProps } from 'vue';
import { router } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
}

const props = defineProps<{ users: User[] }>();

function editUser(userId: number) {
    router.visit(`/admin/users/${userId}/edit`);
}

function deleteUser(userId: number) {
    if (confirm('Are you sure you want to delete this user?')) {
        router.delete(`/admin/users/${userId}`, {
            onSuccess: () => {
                // Remove user from local list immediately
                props.users.splice(props.users.findIndex(u => u.id === userId), 1);
            }
        });
    }
}
</script>

<template>
    <div>
        <h2 class="text-2xl mb-4">User Management</h2>
        <table v-if="props.users && props.users.length" class="w-full text-left bg-[#4b824b] text-[#FFFAE9]">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in props.users" :key="user.id">
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.is_admin ? 'Yes' : 'No' }}</td>
                    <td>
                        <button @click="editUser(user.id)" class="mr-2">Edit</button>
                        <button @click="deleteUser(user.id)">Delete</button>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-else>No users found.</div>
    </div>
</template>

<style scoped>
table {
    border-collapse: collapse;
}
th, td {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #FFFAE9;
}
button {
    background: #FFFAE9;
    color: #4b824b;
    border: none;
    padding: 0.25rem 0.75rem;
    border-radius: 4px;
    cursor: pointer;
}
button:hover {
    background: #e6e6e6;
}
</style>
