<script setup lang="ts">
import { defineProps, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface User {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
}

const props = defineProps<{ users: User[] }>();

// Modal state
const showDeleteModal = ref(false);
const userToDelete = ref<User | null>(null);

function editUser(userId: number) {
    router.visit(`/admin/users/${userId}/edit`);
}

function openDeleteModal(user: User) {
    userToDelete.value = user;
    showDeleteModal.value = true;
}

function closeDeleteModal() {
    showDeleteModal.value = false;
    userToDelete.value = null;
}

function confirmDelete() {
    if (userToDelete.value) {
        router.delete(`/admin/users/${userToDelete.value.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteModal();
                // Refresh the dashboard to get updated user list
                router.visit('/dashboard', {
                    preserveState: false,
                    replace: true
                });
            },
            onError: (error) => {
                console.error('Delete error:', error);
                closeDeleteModal();
            },
            onFinish: () => {
                closeDeleteModal();
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
                        <div class="flex gap-3">
                            <button 
                                @click="editUser(user.id)" 
                                class="px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors font-medium"
                            >
                                Edit
                            </button>
                            <button 
                                @click="openDeleteModal(user)"
                                class="px-3 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition-colors font-medium"
                            >
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-else>No users found.</div>

        <!-- Delete Confirmation Modal -->
        <Dialog :open="showDeleteModal" @update:open="closeDeleteModal">
            <DialogContent class="sm:max-w-md bg-white">
                <DialogHeader>
                    <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
                        Delete User
                    </DialogTitle>
                    <DialogDescription class="text-center text-gray-600 mt-2">
                        Are you sure you want to delete this user? This action cannot be undone.
                        <div v-if="userToDelete" class="mt-3 p-3 bg-red-50 rounded-lg text-sm">
                            <div><strong>Name:</strong> {{ userToDelete.name }}</div>
                            <div><strong>Email:</strong> {{ userToDelete.email }}</div>
                            <div><strong>Admin:</strong> {{ userToDelete.is_admin ? 'Yes' : 'No' }}</div>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                
                <DialogFooter class="flex gap-3 sm:justify-center">
                    <Button 
                        variant="outline" 
                        @click="closeDeleteModal"
                        class="flex-1"
                        style="border-color: #495846; color: #495846;"
                    >
                        Cancel
                    </Button>
                    <Button 
                        @click="confirmDelete"
                        class="flex-1 text-white border-none"
                        style="background-color: #dc2626; border-color: #dc2626;"
                    >
                        Delete User
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
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

/* Remove old button styles since we're using Tailwind classes */
</style>
