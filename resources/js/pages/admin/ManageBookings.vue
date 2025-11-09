<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

interface Booking {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    company_name: string;
    room: string;
    booking_date: string;
    start_time: string;
    end_time: string;
    formatted_time: string;
    room_name: string;
    status: string;
    created_at: string;
    additional_info?: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Manage Bookings',
        href: '/admin/bookings',
    },
];

const page = usePage();
const bookings = computed(() => page.props.bookings as Booking[]);

// Pagination state
const currentPage = ref(1);
const pageSize = 10;
const totalPages = computed(() => Math.ceil(bookings.value.length / pageSize));
const paginatedBookings = computed(() =>
    bookings.value.slice((currentPage.value - 1) * pageSize, currentPage.value * pageSize)
);

// Modal state
const showCancelModal = ref(false);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const selectedBooking = ref<Booking | null>(null);
const actionType = ref<'cancel' | 'approve' | 'reject'>('cancel');

function openActionModal(booking: Booking, action: 'cancel' | 'approve' | 'reject') {
    selectedBooking.value = booking;
    actionType.value = action;
    
    if (action === 'cancel') {
        showCancelModal.value = true;
    } else if (action === 'approve') {
        showApproveModal.value = true;
    } else if (action === 'reject') {
        showRejectModal.value = true;
    }
}

function closeAllModals() {
    showCancelModal.value = false;
    showApproveModal.value = false;
    showRejectModal.value = false;
    selectedBooking.value = null;
}

function confirmAction() {
    if (selectedBooking.value && actionType.value) {
        const endpoint = `/admin/bookings/${selectedBooking.value.id}/${actionType.value}`;
        
        router.post(endpoint, {}, {
            onSuccess: () => {
                closeAllModals();
                // Refresh the page to get updated data
                router.reload();
            },
            onError: (errors) => {
                console.error('Error performing action:', errors);
            }
        });
    }
}

function getStatusBadgeClass(status: string) {
    switch (status) {
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'completed':
            return 'bg-blue-100 text-blue-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

function formatDate(dateString: string) {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function getBookingType(booking: Booking) {
    // Check if it's a walk-in booking based on additional_info field
    if (booking.additional_info && booking.additional_info.includes('Walk-in booking')) {
        return 'Walk-in';
    }
    return 'Online';
}

function getBookingTypeBadgeClass(booking: Booking) {
    const type = getBookingType(booking);
    if (type === 'Walk-in') {
        return 'bg-orange-100 text-orange-800 border border-orange-200';
    }
    return 'bg-blue-100 text-blue-800 border border-blue-200';
}
</script>

<template>
    <div style="background: #FFFAE9; min-height: 100vh;">
        <Head title="Manage Bookings" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-[#4b824b]">Manage Bookings</h1>
                <div class="text-sm text-gray-600">
                    Total bookings: {{ bookings.length }}
                </div>
            </div>

            <div class="overflow-hidden rounded-lg border border-[#4b824b]">
                <div v-if="paginatedBookings.length > 0" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#4b824b]">
                        <thead class="bg-[#4b824b]">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Booking ID</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Room</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Time</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Type</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-[#FFFAE9] uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-[#FFFAE9] divide-y divide-[#4b824b]">
                            <tr v-for="booking in paginatedBookings" :key="booking.id" class="hover:bg-[#f5f1e3]">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-[#4b824b] text-left">#{{ booking.id }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-left">
                                    <div class="text-sm font-medium text-[#4b824b]">{{ booking.user.name }}</div>
                                    <div class="text-sm text-gray-600">{{ booking.user.email }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-[#4b824b] text-left">{{ booking.company_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-[#4b824b] text-left">{{ booking.room_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-[#4b824b] text-left">{{ formatDate(booking.booking_date) }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-[#4b824b] text-left">{{ booking.formatted_time }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span 
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getBookingTypeBadgeClass(booking)"
                                    >
                                        {{ getBookingType(booking) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <span 
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusBadgeClass(booking.status)"
                                    >
                                        {{ booking.status.charAt(0).toUpperCase() + booking.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex justify-center gap-2">
                                        <template v-if="booking.status === 'pending'">
                                            <Button
                                                @click="openActionModal(booking, 'approve')"
                                                class="text-xs bg-green-600 hover:bg-green-700 text-white"
                                                size="sm"
                                            >
                                                Accept
                                            </Button>
                                            <Button
                                                @click="openActionModal(booking, 'reject')"
                                                variant="destructive"
                                                size="sm"
                                                class="text-xs"
                                            >
                                                Reject
                                            </Button>
                                        </template>
                                        <template v-else-if="booking.status === 'confirmed'">
                                            <Button
                                                @click="openActionModal(booking, 'cancel')"
                                                variant="destructive"
                                                size="sm"
                                                class="text-xs"
                                            >
                                                Cancel
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <span class="text-gray-400 text-xs">
                                                No actions available
                                            </span>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="text-center py-12">
                    <div class="text-gray-500 text-lg">No bookings found</div>
                    <div class="text-gray-400 text-sm mt-2">Bookings will appear here once users start making reservations</div>
                </div>
            </div>

            <!-- Pagination Controls -->
            <div v-if="totalPages > 1" class="pagination-controls">
                <button 
                    :disabled="currentPage === 1"
                    @click="currentPage--"
                    class="page-btn"
                >Previous</button>
                <span class="page-info">Page {{ currentPage }} of {{ totalPages }}</span>
                <button 
                    :disabled="currentPage === totalPages"
                    @click="currentPage++"
                    class="page-btn"
                >Next</button>
            </div>
        </div>

        <!-- Cancel Confirmation Modal -->
        <Dialog :open="showCancelModal" @update:open="closeAllModals">
            <DialogContent class="sm:max-w-md bg-white">
                <DialogHeader>
                    <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
                        Cancel Booking
                    </DialogTitle>
                    <DialogDescription class="text-center text-gray-600 mt-2">
                        Are you sure you want to cancel this booking?
                        <div v-if="selectedBooking" class="mt-3 p-3 bg-gray-50 rounded-lg text-sm">
                            <div><strong>User:</strong> {{ selectedBooking.user.name }}</div>
                            <div><strong>Room:</strong> {{ selectedBooking.room_name }}</div>
                            <div><strong>Date:</strong> {{ formatDate(selectedBooking.booking_date) }}</div>
                            <div><strong>Time:</strong> {{ selectedBooking.formatted_time }}</div>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                
                <DialogFooter class="flex gap-3 sm:justify-center">
                    <Button 
                        variant="outline" 
                        @click="closeAllModals"
                        class="flex-1"
                        style="border-color: #495846; color: #495846;"
                    >
                        Keep Booking
                    </Button>
                    <Button 
                        @click="confirmAction"
                        class="flex-1 text-white border-none"
                        style="background-color: #dc2626; border-color: #dc2626;"
                    >
                        Cancel Booking
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Approve Confirmation Modal -->
        <Dialog :open="showApproveModal" @update:open="closeAllModals">
            <DialogContent class="sm:max-w-md bg-white">
                <DialogHeader>
                    <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
                        Approve Booking
                    </DialogTitle>
                    <DialogDescription class="text-center text-gray-600 mt-2">
                        Are you sure you want to approve this booking?
                        <div v-if="selectedBooking" class="mt-3 p-3 bg-green-50 rounded-lg text-sm">
                            <div><strong>User:</strong> {{ selectedBooking.user.name }}</div>
                            <div><strong>Room:</strong> {{ selectedBooking.room_name }}</div>
                            <div><strong>Date:</strong> {{ formatDate(selectedBooking.booking_date) }}</div>
                            <div><strong>Time:</strong> {{ selectedBooking.formatted_time }}</div>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                
                <DialogFooter class="flex gap-3 sm:justify-center">
                    <Button 
                        variant="outline" 
                        @click="closeAllModals"
                        class="flex-1"
                        style="border-color: #495846; color: #495846;"
                    >
                        Cancel
                    </Button>
                    <Button 
                        @click="confirmAction"
                        class="flex-1 text-white border-none"
                        style="background-color: #16a34a; border-color: #16a34a;"
                    >
                        Approve Booking
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Reject Confirmation Modal -->
        <Dialog :open="showRejectModal" @update:open="closeAllModals">
            <DialogContent class="sm:max-w-md bg-white">
                <DialogHeader>
                    <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
                        Reject Booking
                    </DialogTitle>
                    <DialogDescription class="text-center text-gray-600 mt-2">
                        Are you sure you want to reject this booking?
                        <div v-if="selectedBooking" class="mt-3 p-3 bg-red-50 rounded-lg text-sm">
                            <div><strong>User:</strong> {{ selectedBooking.user.name }}</div>
                            <div><strong>Room:</strong> {{ selectedBooking.room_name }}</div>
                            <div><strong>Date:</strong> {{ formatDate(selectedBooking.booking_date) }}</div>
                            <div><strong>Time:</strong> {{ selectedBooking.formatted_time }}</div>
                        </div>
                    </DialogDescription>
                </DialogHeader>
                
                <DialogFooter class="flex gap-3 sm:justify-center">
                    <Button 
                        variant="outline" 
                        @click="closeAllModals"
                        class="flex-1"
                        style="border-color: #495846; color: #495846;"
                    >
                        Cancel
                    </Button>
                    <Button 
                        @click="confirmAction"
                        class="flex-1 text-white border-none"
                        style="background-color: #dc2626; border-color: #dc2626;"
                    >
                        Reject Booking
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
    </div>
</template>

<style scoped>
/* Main page background */
.flex {
    background-color: #FFFAE9;
    color: #4b824b;
}

/* Custom styling for the manage bookings page */
.hover\:bg-\[#f5f1e3\]:hover {
    background-color: #f5f1e3;
}

/* Ensure proper color inheritance */
.text-\[#4b824b\] {
    color: #4b824b;
}

.text-\[#FFFAE9\] {
    color: #FFFAE9;
}

.bg-\[#4b824b\] {
    background-color: #4b824b;
}

.bg-\[#FFFAE9\] {
    background-color: #FFFAE9;
}

.border-\[#4b824b\] {
    border-color: #4b824b;
}

.divide-\[#4b824b\] > :not([hidden]) ~ :not([hidden]) {
    border-color: #4b824b;
}

.pagination-controls {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1.2em;
    margin: 2em 0 0 0;
}
.page-btn {
    background: #fff;
    color: #495846;
    border: 1px solid #495846;
    border-radius: 6px;
    padding: 0.3em 1.2em;
    font-size: 1em;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.page-btn:disabled {
    background: #e0e0e0;
    color: #888;
    cursor: not-allowed;
}
.page-info {
    font-size: 1em;
    color: #495846;
    font-weight: 500;
}
th, td {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
    padding-top: 0.75rem !important;
    padding-bottom: 0.75rem !important;
}
</style>