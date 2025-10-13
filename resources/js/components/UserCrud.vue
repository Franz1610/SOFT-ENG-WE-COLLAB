<script setup lang="ts">
import { defineProps, ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
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
    is_blocked?: boolean;
    role?: string;
}

const props = defineProps<{ users: User[] }>();

// Get current user data from Inertia shared data
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

// Modal state
const showBlockModal = ref(false);
const userToBlock = ref<User | null>(null);

// Function to check if current user can block the target user
function canBlockUser(targetUser: User): boolean {
    if (!currentUser.value) return false;
    
    // Admin Officers can block anyone (including Admins)
    if (currentUser.value.role === 'admin_officer') {
        return true;
    }
    
    // Base Admins can only block regular users, NOT Admin Officers
    if (currentUser.value.role === 'admin') {
        return targetUser.role !== 'admin_officer';
    }
    
    // Regular users cannot block anyone
    return false;
}

function editUser(userId: number) {
    router.visit(`/admin/users/${userId}/edit`);
}

function openBlockModal(user: User) {
    userToBlock.value = user;
    showBlockModal.value = true;
}

function closeBlockModal() {
    showBlockModal.value = false;
    userToBlock.value = null;
}

function confirmToggleBlock() {
    if (userToBlock.value) {
        router.post(`/admin/users/${userToBlock.value.id}/toggle-block`, {}, {
            preserveScroll: false,
            preserveState: false,
            onSuccess: () => {
                closeBlockModal();
            },
            onError: (error) => {
                console.error('Block/Unblock error:', error);
                closeBlockModal();
                alert('An error occurred while updating the user status. Please try again.');
            },
            onFinish: () => {
                closeBlockModal();
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
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in props.users" :key="user.id">
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                        <span v-if="user.role === 'admin_officer'" class="text-purple-300 font-bold">Admin Officer</span>
                        <span v-else-if="user.is_admin" class="text-blue-300 font-bold">Admin</span>
                        <span v-else class="text-gray-300">User</span>
                    </td>
                    <td>
                        <span :class="user.is_blocked ? 'text-red-300 font-bold' : 'text-green-300 font-bold'">
                            {{ user.is_blocked ? 'Blocked' : 'Active' }}
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-3">
                            <button 
                                @click="editUser(user.id)" 
                                class="px-3 py-2 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors font-medium"
                            >
                                Edit
                            </button>
                            <button 
                                v-if="canBlockUser(user)"
                                @click="openBlockModal(user)"
                                :class="user.is_blocked 
                                    ? 'px-3 py-2 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors font-medium'
                                    : 'px-3 py-2 bg-red-500 text-white text-sm rounded hover:bg-red-600 transition-colors font-medium'"
                            >
                                {{ user.is_blocked ? 'Unblock' : 'Block' }}
                            </button>
                            <span 
                                v-else-if="!canBlockUser(user) && (user.role === 'admin_officer' || user.is_admin)"
                                class="px-3 py-2 bg-gray-400 text-white text-sm rounded cursor-not-allowed opacity-60 font-medium"
                                title="You don't have permission to block this user"
                            >
                                {{ user.is_blocked ? 'Unblock' : 'Block' }}
                            </span>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div v-else>No users found.</div>

        <!-- Block/Unblock Confirmation Modal -->
        <Dialog :open="showBlockModal" @update:open="closeBlockModal">
            <DialogContent class="sm:max-w-md bg-white">
                <DialogHeader>
                    <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
                        {{ userToBlock?.is_blocked ? 'Unblock User' : 'Block User' }}
                    </DialogTitle>
                    <DialogDescription class="text-center text-gray-600 mt-2">
                        {{ userToBlock?.is_blocked 
                            ? 'Are you sure you want to unblock this user? They will be able to log in again.' 
                            : 'Are you sure you want to block this user? They will not be able to log in until unblocked.' 
                        }}
                        <div v-if="userToBlock" class="mt-3 p-3 rounded-lg text-sm" 
                             :class="userToBlock.is_blocked ? 'bg-green-50' : 'bg-red-50'">
                            <div><strong>Name:</strong> {{ userToBlock.name }}</div>
                            <div><strong>Email:</strong> {{ userToBlock.email }}</div>
                            <div><strong>Role:</strong> 
                                <span v-if="userToBlock.role === 'admin_officer'" class="text-purple-600 font-bold">Admin Officer</span>
                                <span v-else-if="userToBlock.is_admin" class="text-blue-600 font-bold">Admin</span>
                                <span v-else class="text-gray-600">User</span>
                            </div>
                            <div><strong>Current Status:</strong> 
                                <span :class="userToBlock.is_blocked ? 'text-red-600 font-bold' : 'text-green-600 font-bold'">
                                    {{ userToBlock.is_blocked ? 'Blocked' : 'Active' }}
                                </span>
                            </div>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                
                <DialogFooter class="flex gap-3 sm:justify-center">
                    <Button 
                        variant="outline" 
                        @click="closeBlockModal"
                        class="flex-1"
                        style="border-color: #495846; color: #495846;"
                    >
                        Cancel
                    </Button>
                    <Button 
                        @click="confirmToggleBlock"
                        class="flex-1 text-white border-none"
                        :style="userToBlock?.is_blocked 
                            ? 'background-color: #16a34a; border-color: #16a34a;' 
                            : 'background-color: #dc2626; border-color: #dc2626;'"
                    >
                        {{ userToBlock?.is_blocked ? 'Unblock User' : 'Block User' }}
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
