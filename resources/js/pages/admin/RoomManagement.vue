<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Building, Users, Wifi, Monitor, Coffee, Car, User, Clock, RefreshCw } from 'lucide-vue-next';
import { ref, computed, watch, onUnmounted } from 'vue';
import axios from 'axios';

// Type definitions
interface RoomCategory {
    id: number;
    name: string;
    category: string;
    totalRooms: number;
    availableRooms: number;
    occupiedRooms: number;
    reservedRooms: number;
    maintenanceRooms: number;
    capacity: string;
    amenities: string[];
    description: string;
}

interface IndividualRoom {
    number: string;
    status: string;
    capacity: number;
    guest: string | null;
    timeRange: string | null;
    booking_id?: number | null;
}

// Get props from backend
interface Props {
    roomCategories: RoomCategory[];
}

const props = defineProps<Props>();

// Reactive variables for modal
const showIndividualRooms = ref(false);
const showEditRooms = ref(false);
const showAddRoomModal = ref(false);
const showDeleteConfirmModal = ref(false);
const showDeleteSuccessModal = ref(false);
const showAddSuccessModal = ref(false);
const showOccupyModal = ref(false);
const showExtendModal = ref(false);
const showStopConfirmModal = ref(false);
const showOccupySuccessModal = ref(false);
const showExtendSuccessModal = ref(false);
const showStopSuccessModal = ref(false);
const showPastTimeErrorModal = ref(false);
const selectedCategory = ref<RoomCategory | null>(null);
const roomToDelete = ref<IndividualRoom | null>(null);
const roomToOccupy = ref<IndividualRoom | null>(null);
const roomToExtend = ref<IndividualRoom | null>(null);
const roomToStop = ref<IndividualRoom | null>(null);
const deletedRoomInfo = ref<{number: string, category: string} | null>(null);
const addedRoomInfo = ref<{numberOfRooms: number, category: string} | null>(null);
const occupySuccessInfo = ref<{roomNumber: string, guestName: string, timeRange: string} | null>(null);
const extendSuccessInfo = ref<{roomNumber: string, newEndTime: string} | null>(null);
const stopSuccessInfo = ref<{roomNumber: string, guestName: string} | null>(null);
const pastTimeErrorMessage = ref('');
const individualRooms = ref<IndividualRoom[]>([]);
const editableRooms = ref<IndividualRoom[]>([]);
const loading = ref(false);
const editLoading = ref(false);
const addRoomLoading = ref(false);
const deleteLoading = ref(false);
const occupyLoading = ref(false);
const extendLoading = ref(false);
const stopLoading = ref(false);

// Add room form data
const addRoomForm = ref({
    category: '',
    numberOfRooms: 1
});

// Occupy room form data
const occupyForm = ref({
    guestName: '',
    startTime: '',
    endTime: ''
});

// Extend room form data
const extendForm = ref({
    newEndTime: ''
});

// Use the room categories from props instead of mock data
const roomCategories = computed(() => props.roomCategories);

// Calculate total statistics
const totalStats = computed(() => {
    const total = roomCategories.value.reduce((acc, category) => {
        acc.totalRooms += category.totalRooms;
        acc.availableRooms += category.availableRooms;
        acc.occupiedRooms += category.occupiedRooms;
        acc.reservedRooms += category.reservedRooms;
        acc.maintenanceRooms += category.maintenanceRooms;
        return acc;
    }, { totalRooms: 0, availableRooms: 0, occupiedRooms: 0, reservedRooms: 0, maintenanceRooms: 0 });
    
    return total;
});

// Fetch individual rooms for a category from the backend
const previewDate = ref<string>('');
const previewStart = ref<string>('');
const previewEnd = ref<string>('');

const fetchIndividualRooms = async (category: RoomCategory) => {
    loading.value = true;
    try {
        const params: Record<string, string> = {};
        if (previewDate.value && previewStart.value && previewEnd.value) {
            params.date = previewDate.value;
            params.start_time = previewStart.value;
            params.end_time = previewEnd.value;
        }
        const response = await axios.get(`/admin/rooms/${category.category}`, { params });
        return response.data;
    } catch (error) {
        console.error('Error fetching room data:', error);
        return [];
    } finally {
        loading.value = false;
    }
};

// Refresh room categories data
const refreshRoomData = async () => {
    try {
        const response = await axios.get('/admin/rooms');
        // When modal is open, also update the selectedCategory stats from the latest payload
        if (selectedCategory.value && Array.isArray(response.data)) {
            const updated = response.data.find((c: any) => c.category === selectedCategory.value?.category);
            if (updated) {
                selectedCategory.value = updated;
            }
            individualRooms.value = await fetchIndividualRooms(selectedCategory.value);
        }
    } catch (error) {
        console.error('Error refreshing room data:', error);
    }
};

// ----- Auto-refresh while the Individual Rooms modal is open -----
let minuteAlignTimeout: number | null = null;
let autoRefreshInterval: number | null = null;

function clearAutoRefreshTimers() {
    if (minuteAlignTimeout) { clearTimeout(minuteAlignTimeout); minuteAlignTimeout = null; }
    if (autoRefreshInterval) { clearInterval(autoRefreshInterval); autoRefreshInterval = null; }
}

async function doTimedRefresh() {
    // Refresh category stats and individual rooms
    await refreshRoomData();
}

function startAutoRefresh() {
    clearAutoRefreshTimers();
    // Immediate refresh to catch up
    void doTimedRefresh();
    // Align the next refresh to the top of the next minute for precise status flips (e.g., 9:30)
    const now = new Date();
    const msToNextMinute = (60 - now.getSeconds()) * 1000 - now.getMilliseconds();
    minuteAlignTimeout = setTimeout(() => {
        void doTimedRefresh();
        autoRefreshInterval = setInterval(() => void doTimedRefresh(), 60_000);
    }, Math.max(0, msToNextMinute));
}

// Start/stop auto-refresh with modal visibility
watch(showIndividualRooms, (open) => {
    if (open) startAutoRefresh();
    else clearAutoRefreshTimers();
});

onUnmounted(() => clearAutoRefreshTimers());

// Removed unused getStatusColor helper

const getStatusBadgeClass = (status: string) => {
    switch (status) {
        case 'Available':
            return 'bg-green-100 text-green-800 border border-green-200';
        case 'Occupied':
            return 'bg-red-100 text-red-800 border border-red-200';
        case 'Reserved':
            return 'bg-blue-100 text-blue-800 border border-blue-200';
        case 'Maintenance':
            return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
        default:
            return 'bg-gray-100 text-gray-800 border border-gray-200';
    }
};

const getAmenityIcon = (amenity: string) => {
    switch (amenity.toLowerCase()) {
        case 'wifi':
            return Wifi;
        case 'projector':
        case 'smart tv':
        case '4k display':
            return Monitor;
        case 'coffee station':
            return Coffee;
        case 'parking':
            return Car;
        default:
            return Building;
    }
};

const viewRooms = async (category: RoomCategory) => {
    selectedCategory.value = category;
    showIndividualRooms.value = true;
    
    // Fetch real room data from backend
    individualRooms.value = await fetchIndividualRooms(category);
};

// Removed preview window apply helper as preview controls were removed

const editRooms = async (category: RoomCategory) => {
    selectedCategory.value = category;
    showEditRooms.value = true;
    editLoading.value = true;
    
    try {
        // Force fresh data fetch from backend
        console.log('Fetching fresh room data for category:', category.category);
        editableRooms.value = await fetchIndividualRooms(category);
        console.log('Fetched rooms:', editableRooms.value);
    } catch (error) {
        console.error('Error fetching rooms for edit:', error);
    } finally {
        editLoading.value = false;
    }
};

const toggleMaintenanceStatus = async (room: IndividualRoom) => {
    const newStatus = room.status === 'Maintenance' ? 'Available' : 'Maintenance';
    
    try {
        // Update locally first for immediate feedback
        room.status = newStatus;
        
        // Send update to backend
        await axios.post(`/admin/rooms/maintenance`, {
            category: selectedCategory.value?.category,
            room_number: room.number,
            status: newStatus
        });
        
        // Refresh the room data in the edit modal
        editableRooms.value = await fetchIndividualRooms(selectedCategory.value!);
        
        // Refresh the page data to update category stats
        router.reload({ only: ['roomCategories'] });
        
    } catch (error) {
        console.error('Error updating room status:', error);
        // Revert the change if the request failed
        room.status = room.status === 'Maintenance' ? 'Available' : 'Maintenance';
        alert('Failed to update room status. Please try again.');
    }
};

// Add new room functions
const openAddRoomModal = () => {
    addRoomForm.value = {
        category: roomCategories.value.length > 0 ? roomCategories.value[0].category : '',
        numberOfRooms: 1
    };
    showAddRoomModal.value = true;
};

const submitAddRoom = async () => {
    if (!addRoomForm.value.category || addRoomForm.value.numberOfRooms < 1) {
        alert('Please fill in all fields correctly.');
        return;
    }

    addRoomLoading.value = true;
    
    try {
        await axios.post('/admin/rooms/add', {
            category: addRoomForm.value.category,
            numberOfRooms: addRoomForm.value.numberOfRooms
        });
        
        // Store added room info for success modal
        const categoryName = roomCategories.value.find(cat => cat.category === addRoomForm.value.category)?.name || addRoomForm.value.category;
        addedRoomInfo.value = {
            numberOfRooms: addRoomForm.value.numberOfRooms,
            category: categoryName
        };
        
        // Close modal and refresh data
        showAddRoomModal.value = false;
        router.reload({ only: ['roomCategories'] });
        
        // Show success modal
        showAddSuccessModal.value = true;
    } catch (error) {
        console.error('Error adding rooms:', error);
        alert('Failed to add rooms. Please try again.');
    } finally {
        addRoomLoading.value = false;
    }
};

// Remove room functions
const openDeleteConfirmModal = (room: IndividualRoom) => {
    roomToDelete.value = room;
    showDeleteConfirmModal.value = true;
};

const confirmDeleteRoom = async () => {
    if (!roomToDelete.value) return;
    
    deleteLoading.value = true;
    console.log('Attempting to delete room:', roomToDelete.value.number, 'in category:', selectedCategory.value?.category);
    
    try {
        await axios.delete(`/admin/rooms/delete`, {
            data: {
                category: selectedCategory.value?.category,
                room_number: roomToDelete.value.number
            }
        });
        
        // Store deleted room info for success modal
        deletedRoomInfo.value = {
            number: roomToDelete.value.number,
            category: selectedCategory.value?.name || ''
        };
        
        // Close the delete modal
        showDeleteConfirmModal.value = false;
        
        // Refresh the room data in the edit modal
        editableRooms.value = await fetchIndividualRooms(selectedCategory.value!);
        
        // Refresh the page data to update category stats
        router.reload({ only: ['roomCategories'] });
        
        // Show success modal
        showDeleteSuccessModal.value = true;
    } catch (error: any) {
        console.error('Error deleting room:', error);
        
        // Show more specific error message
        let errorMessage = 'Failed to delete room. Please try again.';
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.status === 404) {
            errorMessage = 'Room not found.';
        } else if (error.response?.status === 400) {
            errorMessage = 'Cannot delete room with active bookings.';
        }
        
        alert(errorMessage);
    } finally {
        deleteLoading.value = false;
        roomToDelete.value = null;
    }
};

// Occupy room function for walk-in functionality
const occupyRoom = async (room: IndividualRoom) => {
    roomToOccupy.value = room;
    
    // Prefill with NOW and +1 hour to ensure walk-ins show as Occupied immediately
    const now = new Date();
    const pad = (n: number) => String(n).padStart(2, '0');
    const hhmm = (d: Date) => `${pad(d.getHours())}:${pad(d.getMinutes())}`;
    const plusMinutes = (d: Date, m: number) => new Date(d.getTime() + m * 60000);
    const start = now;
    const end = plusMinutes(now, 60);
    occupyForm.value = {
        guestName: '',
        startTime: hhmm(start),
        endTime: hhmm(end)
    };
    
    showOccupyModal.value = true;
};

// Submit occupy room form
const submitOccupyRoom = async () => {
    if (!occupyForm.value.guestName.trim() || !occupyForm.value.startTime || !occupyForm.value.endTime) {
        pastTimeErrorMessage.value = 'Please fill in all fields.';
        showPastTimeErrorModal.value = true;
        return;
    }
    
    if (occupyForm.value.startTime >= occupyForm.value.endTime) {
        pastTimeErrorMessage.value = 'End time must be after start time.';
        showPastTimeErrorModal.value = true;
        return;
    }
    
    // Simplified time validation - just compare time strings with current time
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();
    const currentTimeInMinutes = currentHour * 60 + currentMinute;
    
    // Convert start time to minutes
    const [startHour, startMinute] = occupyForm.value.startTime.split(':').map(Number);
    const startTimeInMinutes = startHour * 60 + startMinute;
    
    // Convert end time to minutes  
    const [endHour, endMinute] = occupyForm.value.endTime.split(':').map(Number);
    const endTimeInMinutes = endHour * 60 + endMinute;
    
    // Allow some tolerance (5 minutes) for walk-in bookings
    const toleranceMinutes = 5;
    
    if (startTimeInMinutes < (currentTimeInMinutes - toleranceMinutes)) {
        pastTimeErrorMessage.value = 'Start time cannot be in the past. Please select a current or future time.';
        showPastTimeErrorModal.value = true;
        return;
    }
    
    if (endTimeInMinutes < currentTimeInMinutes) {
        pastTimeErrorMessage.value = 'End time cannot be in the past. Please select a current or future time.';
        showPastTimeErrorModal.value = true;
        return;
    }
    
    occupyLoading.value = true;
    
    try {
        // Call the actual backend API to create walk-in booking
        const response = await axios.post('/admin/rooms/occupy', {
            category: selectedCategory.value?.category,
            room_number: roomToOccupy.value?.number,
            guest_name: occupyForm.value.guestName,
            start_time: occupyForm.value.startTime,
            end_time: occupyForm.value.endTime
        });
        
        if (response.data.success) {
            // Store success info for modal
            occupySuccessInfo.value = {
                roomNumber: roomToOccupy.value?.number || '',
                guestName: occupyForm.value.guestName,
                timeRange: `${occupyForm.value.startTime} - ${occupyForm.value.endTime}`
            };
            
            // Close modal and refresh data
            showOccupyModal.value = false;
            
            // Refresh room data after occupying
            individualRooms.value = await fetchIndividualRooms(selectedCategory.value!);
            router.reload({ only: ['roomCategories'] });
            
            // Show success modal
            showOccupySuccessModal.value = true;
        } else {
            pastTimeErrorMessage.value = response.data.message || 'Failed to occupy room.';
            showPastTimeErrorModal.value = true;
        }
        
    } catch (error: any) {
        console.error('Error occupying room:', error);
        let errorMessage = 'Failed to occupy room. Please try again.';
        
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.status === 400) {
            errorMessage = error.response.data.message || 'Invalid booking data or time conflict.';
        }
        
        pastTimeErrorMessage.value = errorMessage;
        showPastTimeErrorModal.value = true;
    } finally {
        occupyLoading.value = false;
    }
};

// Extend room function
const extendRoom = async (room: IndividualRoom) => {
    roomToExtend.value = room;
    
    // Reset form - we'll pre-fill with current end time for convenience
    extendForm.value = {
        newEndTime: ''
    };
    
    showExtendModal.value = true;
};

// Submit extend room form
const submitExtendRoom = async () => {
    if (!extendForm.value.newEndTime) {
        pastTimeErrorMessage.value = 'Please select a new end time.';
        showPastTimeErrorModal.value = true;
        return;
    }
    
    // Basic validation - ensure booking_id exists
    if (!roomToExtend.value?.booking_id) {
        pastTimeErrorMessage.value = 'Unable to find booking information for this room.';
        showPastTimeErrorModal.value = true;
        return;
    }
    
    extendLoading.value = true;
    
    try {
        // Call the backend API to extend the booking
        const response = await axios.post('/admin/rooms/extend', {
            category: selectedCategory.value?.category,
            room_number: roomToExtend.value?.number,
            booking_id: roomToExtend.value?.booking_id,
            new_end_time: extendForm.value.newEndTime
        });
        
        if (response.data.success) {
            // Store success info for modal
            extendSuccessInfo.value = {
                roomNumber: roomToExtend.value?.number || '',
                newEndTime: extendForm.value.newEndTime
            };
            
            // Close modal and refresh data
            showExtendModal.value = false;
            
            // Refresh room data after extending
            individualRooms.value = await fetchIndividualRooms(selectedCategory.value!);
            router.reload({ only: ['roomCategories'] });
            
            // Show success modal
            showExtendSuccessModal.value = true;
        } else {
            pastTimeErrorMessage.value = response.data.message || 'Failed to extend booking.';
            showPastTimeErrorModal.value = true;
        }
        
    } catch (error: any) {
        console.error('Error extending booking:', error);
        let errorMessage = 'Failed to extend booking. Please try again.';
        
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.status === 400) {
            errorMessage = error.response.data.message || 'Invalid time or booking conflict.';
        } else if (error.response?.status === 404) {
            errorMessage = 'Booking not found. Please refresh and try again.';
        }
        
        pastTimeErrorMessage.value = errorMessage;
        showPastTimeErrorModal.value = true;
    } finally {
        extendLoading.value = false;
    }
};

// Stop room function
const stopRoom = async (room: IndividualRoom) => {
    // Check if booking_id exists
    if (!room.booking_id) {
        alert('Unable to find booking information for this room.');
        return;
    }
    
    roomToStop.value = room;
    showStopConfirmModal.value = true;
};

// Confirm stop room
const confirmStopRoom = async () => {
    if (!roomToStop.value) return;
    
    stopLoading.value = true;
    
    try {
        // Call the backend API to stop/cancel the booking
        const response = await axios.post('/admin/rooms/stop', {
            category: selectedCategory.value?.category,
            room_number: roomToStop.value.number,
            booking_id: roomToStop.value.booking_id
        });
        
        if (response.data.success) {
            // Store success info for modal
            stopSuccessInfo.value = {
                roomNumber: roomToStop.value.number,
                guestName: roomToStop.value.guest || 'Unknown Guest'
            };
            
            // Close confirmation modal
            showStopConfirmModal.value = false;
            
            // Refresh room data after stopping
            individualRooms.value = await fetchIndividualRooms(selectedCategory.value!);
            router.reload({ only: ['roomCategories'] });
            
            // Show success modal
            showStopSuccessModal.value = true;
        } else {
            alert(response.data.message || 'Failed to stop booking.');
        }
        
    } catch (error: any) {
        console.error('Error stopping booking:', error);
        let errorMessage = 'Failed to stop booking. Please try again.';
        
        if (error.response?.data?.message) {
            errorMessage = error.response.data.message;
        } else if (error.response?.status === 404) {
            errorMessage = 'Booking not found. Please refresh and try again.';
        }
        
        alert(errorMessage);
    } finally {
        stopLoading.value = false;
        roomToStop.value = null;
    }
};
</script>

<template>
    <div style="background: #FFFAE9; min-height: 100vh;">
        <Head title="Room Management" />

        <AppLayout :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Room Management', href: '/admin/rooms' }
        ]">
            <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <!-- Header Section -->
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-[#4b824b] mb-2">Room Management</h1>
                        <p class="text-[#344C34]">Manage and monitor all conference rooms and meeting spaces</p>
                    </div>
                    <div class="flex gap-2">
                        <Button variant="outline" class="border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]" @click="refreshRoomData">
                            <RefreshCw class="w-4 h-4 mr-2" />
                            Refresh
                        </Button>
                        <Button class="bg-[#4b824b] hover:bg-[#3d6b3d] text-[#FFFAE9] border-[#4b824b]" @click="openAddRoomModal">
                            <Building class="w-4 h-4 mr-2" />
                            Add New Room
                        </Button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="grid auto-rows-min gap-4 md:grid-cols-5">
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Total Rooms</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-[#FFFAE9]">{{ totalStats.totalRooms }}</div>
                            <p class="text-xs text-[#FFFAE9]/80">All spaces</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Available</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #86efac !important;">{{ totalStats.availableRooms }}</div>
                            <p class="text-xs text-[#FFFAE9]/80">Ready to book</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Occupied</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #fca5a5 !important;">{{ totalStats.occupiedRooms }}</div>
                            <p class="text-xs text-[#FFFAE9]/80">Currently in use</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Reserved</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #93c5fd !important;">{{ totalStats.reservedRooms }}</div>
                            <p class="text-xs text-[#FFFAE9]/80">Upcoming bookings</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Maintenance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #fde68a !important;">{{ totalStats.maintenanceRooms }}</div>
                            <p class="text-xs text-[#FFFAE9]/80">Under maintenance</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Rooms Grid -->
                <div class="main-content-area">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-[#4b824b] mb-4">Room Categories</h2>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card v-for="category in roomCategories" :key="category.id" class="room-card hover:shadow-lg transition-shadow">
                                <CardHeader>
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="text-lg text-[#4b824b]">{{ category.name }}</CardTitle>
                                        <span class="text-xs px-2 py-1 rounded-full border bg-blue-100 text-blue-800 border-blue-200">
                                            {{ category.totalRooms }} Rooms
                                        </span>
                                    </div>
                                    <CardDescription class="text-[#344C34]">
                                        {{ category.description }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="flex items-center text-sm text-[#344C34]">
                                        <Users class="w-4 h-4 mr-2 text-[#4b824b]" />
                                        Capacity: {{ category.capacity }}
                                    </div>
                                    <div class="grid grid-cols-4 gap-2 text-xs">
                                        <div class="text-center p-2 bg-green-50 rounded">
                                            <div class="font-bold text-green-600">{{ category.availableRooms }}</div>
                                            <div class="text-green-600">Available</div>
                                        </div>
                                        <div class="text-center p-2 bg-red-50 rounded">
                                            <div class="font-bold text-red-600">{{ category.occupiedRooms }}</div>
                                            <div class="text-red-600">Occupied</div>
                                        </div>
                                        <div class="text-center p-2 bg-blue-50 rounded">
                                            <div class="font-bold text-blue-600">{{ category.reservedRooms }}</div>
                                            <div class="text-blue-600">Reserved</div>
                                        </div>
                                        <div class="text-center p-2 bg-yellow-50 rounded">
                                            <div class="font-bold text-yellow-600">{{ category.maintenanceRooms }}</div>
                                            <div class="text-yellow-600">Maintenance</div>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-[#4b824b] mb-2">Amenities:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <div v-for="amenity in category.amenities" :key="amenity" 
                                                 class="flex items-center text-xs bg-[#4b824b]/10 text-[#4b824b] px-2 py-1 rounded-md border border-[#4b824b]/20">
                                                <component :is="getAmenityIcon(amenity)" class="w-3 h-3 mr-1" />
                                                {{ amenity }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 pt-2">
                                        <Button variant="outline" size="sm" class="flex-1 border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]" @click="editRooms(category)">
                                            Edit
                                        </Button>
                                        <Button variant="outline" size="sm" class="flex-1 border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]" @click="viewRooms(category)">
                                            View Rooms
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>

        <!-- Individual Rooms Modal -->
        <Dialog v-model:open="showIndividualRooms">
            <DialogContent class="max-w-[98vw] w-[98vw] h-[95vh] max-h-[95vh] p-10 bg-[#FFFAE9] border-2 border-[#4b824b] sm:max-w-[98vw]">
                <DialogHeader class="mb-6">
                    <DialogTitle class="text-3xl text-[#4b824b] font-bold">{{ selectedCategory?.name }}</DialogTitle>
                    <DialogDescription class="text-lg text-[#344C34] mt-2">
                        {{ selectedCategory?.description }}
                    </DialogDescription>
                </DialogHeader>
        
                <!-- Removed date/time preview controls per request -->
                
                <!-- Category Stats (Large) -->
                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div class="text-center p-6 bg-green-50 rounded-lg border border-green-200">
                        <div class="text-4xl font-bold text-green-600">{{ selectedCategory?.availableRooms }}</div>
                        <div class="text-xl text-green-600 mt-2">Available</div>
                    </div>
                    <div class="text-center p-6 bg-red-50 rounded-lg border border-red-200">
                        <div class="text-4xl font-bold text-red-600">{{ selectedCategory?.occupiedRooms }}</div>
                        <div class="text-xl text-red-600 mt-2">Occupied</div>
                    </div>
                    <div class="text-center p-6 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div class="text-4xl font-bold text-yellow-600">{{ selectedCategory?.maintenanceRooms }}</div>
                        <div class="text-xl text-yellow-600 mt-2">Maintenance</div>
                    </div>
                </div>

                <!-- Individual Rooms Grid (Scrollable) -->
                <div class="overflow-y-auto max-h-[50vh]">
                    <div v-if="loading" class="flex justify-center items-center h-32">
                        <div class="text-lg text-[#4b824b]">Loading rooms...</div>
                    </div>
                    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4">
                        <Card v-for="room in individualRooms" :key="room.number" 
                              class="room-card hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                            <CardHeader class="pb-3">
                                <div class="flex items-center justify-between">
                                    <CardTitle class="text-lg text-[#4b824b] font-semibold">
                                        {{ room.number }}
                                    </CardTitle>
                                    <span :class="getStatusBadgeClass(room.status)" 
                                          class="text-xs px-3 py-1 rounded-full font-medium">
                                        {{ room.status }}
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex items-center text-sm text-[#344C34]">
                                    <Users class="w-4 h-4 mr-2 text-[#4b824b]" />
                                    Capacity: {{ room.capacity }}
                                </div>
                                <div v-if="room.guest" class="flex items-center text-sm text-[#344C34]">
                                    <User class="w-4 h-4 mr-2 text-[#4b824b]" />
                                    {{ room.guest }}
                                </div>
                                <div v-if="room.timeRange" class="flex items-center text-sm text-[#344C34]">
                                    <Clock class="w-4 h-4 mr-2 text-[#4b824b]" />
                                    {{ room.timeRange }}
                                </div>
                                
                                <!-- Occupy Button for Available Rooms -->
                                <div v-if="room.status === 'Available'" class="pt-2">
                                    <Button 
                                        @click="occupyRoom(room)"
                                        variant="outline"
                                        class="w-full border-orange-500 text-orange-600 hover:bg-orange-500 hover:text-white"
                                        size="sm"
                                    >
                                        Occupy
                                    </Button>
                                </div>
                                
                                <!-- Extend and Stop Buttons for Occupied Rooms -->
                                <div v-if="room.status === 'Occupied'" class="pt-2 space-y-2">
                                    <Button 
                                        @click="extendRoom(room)"
                                        variant="outline"
                                        class="w-full border-blue-500 text-blue-600 hover:bg-blue-500 hover:text-white transition-all duration-200"
                                        size="sm"
                                        :disabled="extendLoading"
                                    >
                                        <div v-if="extendLoading" class="w-4 h-4 mr-2 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
                                        {{ extendLoading ? 'Extending...' : 'Extend' }}
                                    </Button>
                                    <Button 
                                        @click="stopRoom(room)"
                                        variant="outline"
                                        class="w-full border-red-500 text-red-600 hover:bg-red-500 hover:text-white transition-all duration-200"
                                        size="sm"
                                        :disabled="stopLoading"
                                    >
                                        <div v-if="stopLoading" class="w-4 h-4 mr-2 border-2 border-red-600 border-t-transparent rounded-full animate-spin"></div>
                                        {{ stopLoading ? 'Stopping...' : 'Stop' }}
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Edit Rooms Modal -->
        <Dialog v-model:open="showEditRooms">
            <DialogContent class="max-w-[98vw] w-[98vw] h-[95vh] max-h-[95vh] p-10 bg-[#FFFAE9] border-2 border-[#4b824b] sm:max-w-[98vw]">
                <DialogHeader class="mb-6">
                    <DialogTitle class="text-3xl text-[#4b824b] font-bold">Edit {{ selectedCategory?.name }} Rooms</DialogTitle>
                    <DialogDescription class="text-lg text-[#344C34] mt-2">
                        Set rooms to maintenance mode to prevent bookings
                    </DialogDescription>
                </DialogHeader>
                
                <!-- Category Stats (Large) -->
                <div class="grid grid-cols-3 gap-6 mb-8">
                    <div class="text-center p-6 bg-green-50 rounded-lg border border-green-200">
                        <div class="text-4xl font-bold text-green-600">{{ selectedCategory?.availableRooms }}</div>
                        <div class="text-xl text-green-600 mt-2">Available</div>
                    </div>
                    <div class="text-center p-6 bg-red-50 rounded-lg border border-red-200">
                        <div class="text-4xl font-bold text-red-600">{{ selectedCategory?.occupiedRooms }}</div>
                        <div class="text-xl text-red-600 mt-2">Occupied</div>
                    </div>
                    <div class="text-center p-6 bg-yellow-50 rounded-lg border border-yellow-200">
                        <div class="text-4xl font-bold text-yellow-600">{{ selectedCategory?.maintenanceRooms }}</div>
                        <div class="text-xl text-yellow-600 mt-2">Maintenance</div>
                    </div>
                </div>

                <!-- Individual Rooms Grid (Scrollable) -->
                <div class="overflow-y-auto max-h-[50vh]">
                    <div v-if="editLoading" class="flex justify-center items-center py-8">
                        <RefreshCw class="animate-spin h-8 w-8 text-[#4b824b]" />
                        <span class="ml-2 text-[#4b824b]">Loading rooms...</span>
                    </div>
                    
                    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        <Card v-for="room in editableRooms" :key="room.number" 
                              class="room-card hover:shadow-lg transition-all duration-200">
                            <CardHeader class="pb-3">
                                <div class="flex items-center justify-between">
                                    <CardTitle class="text-lg text-[#4b824b] font-semibold">
                                        {{ room.number }}
                                    </CardTitle>
                                    <span :class="getStatusBadgeClass(room.status)" 
                                          class="text-xs px-3 py-1 rounded-full font-medium">
                                        {{ room.status }}
                                    </span>
                                </div>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex items-center text-sm text-[#344C34]">
                                    <Users class="w-4 h-4 mr-2 text-[#4b824b]" />
                                    Capacity: {{ room.capacity }}
                                </div>
                                <div v-if="room.guest" class="flex items-center text-sm text-[#344C34]">
                                    <User class="w-4 h-4 mr-2 text-[#4b824b]" />
                                    {{ room.guest }}
                                </div>
                                <div v-if="room.timeRange" class="flex items-center text-sm text-[#344C34]">
                                    <Clock class="w-4 h-4 mr-2 text-[#4b824b]" />
                                    {{ room.timeRange }}
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="pt-2 space-y-2">
                                    <!-- Maintenance Toggle Button -->
                                    <Button 
                                        v-if="room.status !== 'Occupied' && room.status !== 'Reserved'"
                                        @click="toggleMaintenanceStatus(room)"
                                        :variant="room.status === 'Maintenance' ? 'default' : 'outline'"
                                        :class="room.status === 'Maintenance' 
                                            ? 'bg-yellow-500 hover:bg-yellow-600 text-white border-yellow-500' 
                                            : 'border-yellow-500 text-yellow-600 hover:bg-yellow-500 hover:text-white'"
                                        size="sm" 
                                        class="w-full"
                                    >
                                        {{ room.status === 'Maintenance' ? 'Remove from Maintenance' : 'Set to Maintenance' }}
                                    </Button>
                                    
                                    <!-- Remove Room Button -->
                                    <Button 
                                        v-if="room.status !== 'Occupied' && room.status !== 'Reserved'"
                                        @click="openDeleteConfirmModal(room)"
                                        variant="outline"
                                        class="w-full border-red-500 text-red-600 hover:bg-red-500 hover:text-white"
                                        size="sm"
                                    >
                                        Remove Room
                                    </Button>
                                    
                                    <div v-if="room.status === 'Occupied'" class="text-xs text-gray-500 text-center py-2">
                                        Cannot modify occupied room
                                    </div>
                                    <div v-if="room.status === 'Reserved'" class="text-xs text-blue-500 text-center py-2">
                                        Cannot modify reserved room
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Add New Room Modal -->
        <Dialog v-model:open="showAddRoomModal">
            <DialogContent class="max-w-md bg-[#FFFAE9] border-2 border-[#4b824b]">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-[#4b824b]">
                        <Building class="w-5 h-5 inline mr-2" />
                        Add New Room
                    </DialogTitle>
                    <DialogDescription class="text-[#344C34]">
                        Add new rooms to an existing category
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-4 mt-4">
                    <!-- Room Category Selection -->
                    <div>
                        <label class="block text-sm font-medium text-[#4b824b] mb-2">
                            Room Category
                        </label>
                        <select 
                            v-model="addRoomForm.category" 
                            class="w-full p-2 border border-[#4b824b] rounded-md bg-white text-[#4b824b] focus:ring-2 focus:ring-[#4b824b] focus:border-transparent"
                        >
                            <option value="" disabled>Select a category</option>
                            <option v-for="category in roomCategories" :key="category.id" :value="category.category">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                    
                    <!-- Number of Rooms -->
                    <div>
                        <label class="block text-sm font-medium text-[#4b824b] mb-2">
                            Number of Rooms to Add
                        </label>
                        <input 
                            v-model.number="addRoomForm.numberOfRooms" 
                            type="number" 
                            min="1" 
                            max="50"
                            class="w-full p-2 border border-[#4b824b] rounded-md bg-white text-[#4b824b] focus:ring-2 focus:ring-[#4b824b] focus:border-transparent"
                            placeholder="Enter number of rooms"
                        />
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2 justify-end mt-6">
                        <Button 
                            variant="outline" 
                            class="border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]"
                            @click="showAddRoomModal = false"
                            :disabled="addRoomLoading"
                        >
                            Cancel
                        </Button>
                        <Button 
                            class="bg-[#4b824b] hover:bg-[#3d6b3d] text-[#FFFAE9]"
                            @click="submitAddRoom"
                            :disabled="addRoomLoading || !addRoomForm.category || addRoomForm.numberOfRooms < 1"
                        >
                            <Building class="w-4 h-4 mr-2" v-if="!addRoomLoading" />
                            <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin" v-else></div>
                            {{ addRoomLoading ? 'Adding...' : 'Add Rooms' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Confirmation Modal -->
        <Dialog v-model:open="showDeleteConfirmModal">
            <DialogContent class="max-w-md bg-[#FFFAE9] border-2 border-red-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-red-600 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Room
                    </DialogTitle>
                    <DialogDescription class="text-[#344C34] mt-2">
                        This action cannot be undone. The room will be permanently removed from the system.
                    </DialogDescription>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <span class="text-red-700 font-medium">Warning</span>
                        </div>
                        <p class="text-red-600 mt-2">
                            You are about to permanently delete 
                            <span class="font-semibold">{{ roomToDelete?.number }}</span> 
                            from the {{ selectedCategory?.name }} category.
                        </p>
                    </div>
                    
                    <div v-if="roomToDelete" class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                        <h4 class="font-medium text-[#4b824b] mb-2">Room Details:</h4>
                        <div class="space-y-1 text-sm text-[#344C34]">
                            <div class="flex justify-between">
                                <span>Room Number:</span>
                                <span class="font-medium">{{ roomToDelete.number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Category:</span>
                                <span class="font-medium">{{ selectedCategory?.name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Status:</span>
                                <span :class="getStatusBadgeClass(roomToDelete.status)" 
                                      class="px-2 py-1 rounded-full text-xs font-medium">
                                    {{ roomToDelete.status }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Capacity:</span>
                                <span class="font-medium">{{ roomToDelete.capacity }} people</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3 justify-end mt-6">
                        <Button 
                            variant="outline" 
                            class="border-gray-300 text-gray-700 hover:bg-gray-50"
                            @click="showDeleteConfirmModal = false"
                            :disabled="deleteLoading"
                        >
                            Cancel
                        </Button>
                        <Button 
                            class="bg-red-600 hover:bg-red-700 text-white border-red-600"
                            @click="confirmDeleteRoom"
                            :disabled="deleteLoading"
                        >
                            <svg class="w-4 h-4 mr-2" v-if="!deleteLoading" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin" v-else></div>
                            {{ deleteLoading ? 'Deleting...' : 'Delete Room' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Delete Success Modal -->
        <Dialog v-model:open="showDeleteSuccessModal">
            <DialogContent class="max-w-sm bg-[#FFFAE9] border-2 border-green-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-green-600 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Room Successfully Deleted
                    </DialogTitle>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="text-green-700 font-medium">
                                {{ deletedRoomInfo?.number }} has been successfully deleted.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex justify-center mt-6">
                        <Button 
                            class="bg-green-600 hover:bg-green-700 text-white border-green-600 px-6"
                            @click="showDeleteSuccessModal = false"
                        >
                            OK
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Add Room Success Modal -->
        <Dialog v-model:open="showAddSuccessModal">
            <DialogContent class="max-w-sm bg-[#FFFAE9] border-2 border-green-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-green-600 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ addedRoomInfo?.numberOfRooms === 1 ? 'Room' : 'Rooms' }} Successfully Added
                    </DialogTitle>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="text-green-700 font-medium">
                                {{ addedRoomInfo?.numberOfRooms }} 
                                {{ addedRoomInfo?.numberOfRooms === 1 ? 'room has' : 'rooms have' }} 
                                been successfully added to {{ addedRoomInfo?.category }}.
                            </p>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex justify-center mt-6">
                        <Button 
                            class="bg-green-600 hover:bg-green-700 text-white border-green-600 px-6"
                            @click="showAddSuccessModal = false"
                        >
                            OK
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Occupy Room Modal -->
        <Dialog v-model:open="showOccupyModal">
            <DialogContent class="max-w-md bg-[#FFFAE9] border-2 border-orange-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-orange-600">
                        <User class="w-5 h-5 inline mr-2" />
                        Walk-in Booking
                    </DialogTitle>
                    <DialogDescription class="text-[#344C34]">
                        Register a walk-in guest for room {{ roomToOccupy?.number }}
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-4 mt-4">
                    <!-- Room Info -->
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-orange-800">Room:</span>
                            <span class="text-sm font-bold text-orange-900">{{ roomToOccupy?.number }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium text-orange-800">Category:</span>
                            <span class="text-sm text-orange-900">{{ selectedCategory?.name }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium text-orange-800">Capacity:</span>
                            <span class="text-sm text-orange-900">{{ roomToOccupy?.capacity }} people</span>
                        </div>
                    </div>
                    
                    <!-- Guest Name -->
                    <div>
                        <label class="block text-sm font-medium text-[#4b824b] mb-2">
                            Guest Name *
                        </label>
                        <input 
                            v-model="occupyForm.guestName" 
                            type="text" 
                            class="w-full p-2 border border-[#4b824b] rounded-md bg-white text-[#4b824b] focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            placeholder="Enter guest name"
                        />
                    </div>
                    
                    <!-- Time Selection -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-[#4b824b] mb-2">
                                Start Time *
                            </label>
                            <input 
                                v-model="occupyForm.startTime" 
                                type="time" 
                                placeholder="00:00"
                                class="w-full p-2 border border-[#4b824b] rounded-md bg-white text-[#4b824b] focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-[#4b824b] mb-2">
                                End Time *
                            </label>
                            <input 
                                v-model="occupyForm.endTime" 
                                type="time" 
                                placeholder="00:00"
                                class="w-full p-2 border border-[#4b824b] rounded-md bg-white text-[#4b824b] focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                            />
                        </div>
                    </div>
                    
                    <!-- Walk-in Note -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-blue-700 font-medium text-sm">Walk-in Booking</p>
                                <p class="text-blue-600 text-xs mt-1">
                                    This booking has no advance time restrictions and can start immediately.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2 justify-end mt-6">
                        <Button 
                            variant="outline" 
                            class="border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]"
                            @click="showOccupyModal = false"
                            :disabled="occupyLoading"
                        >
                            Cancel
                        </Button>
                        <Button 
                            class="bg-orange-500 hover:bg-orange-600 text-white border-orange-500"
                            @click="submitOccupyRoom"
                            :disabled="occupyLoading || !occupyForm.guestName.trim() || !occupyForm.startTime || !occupyForm.endTime"
                        >
                            <User class="w-4 h-4 mr-2" v-if="!occupyLoading" />
                            <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin" v-else></div>
                            {{ occupyLoading ? 'Booking...' : 'Occupy Room' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Extend Room Modal -->
        <Dialog v-model:open="showExtendModal">
            <DialogContent class="max-w-md bg-[#FFFAE9] border-2 border-blue-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-blue-600">
                        <Clock class="w-5 h-5 inline mr-2" />
                        Extend Booking
                    </DialogTitle>
                    <DialogDescription class="text-[#344C34]">
                        Extend the end time for room {{ roomToExtend?.number }}
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-4 mt-4">
                    <!-- Room Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-blue-800">Room:</span>
                            <span class="text-sm font-bold text-blue-900">{{ roomToExtend?.number }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium text-blue-800">Guest:</span>
                            <span class="text-sm text-blue-900">{{ roomToExtend?.guest }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium text-blue-800">Current Time:</span>
                            <span class="text-sm text-blue-900">{{ roomToExtend?.timeRange }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium text-blue-800">Booking ID:</span>
                            <span class="text-sm text-blue-900">{{ roomToExtend?.booking_id || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between items-center mt-1">
                            <span class="text-sm font-medium text-blue-800">Capacity:</span>
                            <span class="text-sm text-blue-900">{{ roomToExtend?.capacity }} people</span>
                        </div>
                    </div>
                    
                    <!-- New End Time -->
                    <div>
                        <label class="block text-sm font-medium text-[#4b824b] mb-2">
                            New End Time *
                        </label>
                        <input 
                            v-model="extendForm.newEndTime" 
                            type="time" 
                            class="w-full p-2 border border-[#4b824b] rounded-md bg-white text-[#4b824b] focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Select new end time"
                        />
                    </div>
                    
                    <!-- Extend Note -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-green-700 font-medium text-sm">Extend Booking</p>
                                <p class="text-green-600 text-xs mt-1">
                                    The new end time must be later than the current end time. The booking will be updated in the system.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2 justify-end mt-6">
                        <Button 
                            variant="outline" 
                            class="border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]"
                            @click="showExtendModal = false"
                            :disabled="extendLoading"
                        >
                            Cancel
                        </Button>
                        <Button 
                            class="bg-blue-500 hover:bg-blue-600 text-white border-blue-500"
                            @click="submitExtendRoom"
                            :disabled="extendLoading || !extendForm.newEndTime"
                        >
                            <Clock class="w-4 h-4 mr-2" v-if="!extendLoading" />
                            <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin" v-else></div>
                            {{ extendLoading ? 'Extending...' : 'Extend Booking' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Stop Confirmation Modal -->
        <Dialog v-model:open="showStopConfirmModal">
            <DialogContent class="max-w-md bg-[#FFFAE9] border-2 border-red-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-red-600 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        Stop Booking
                    </DialogTitle>
                    <DialogDescription class="text-[#344C34] mt-2">
                        This action will cancel the current booking and make the room available.
                    </DialogDescription>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <span class="text-red-700 font-medium">Warning</span>
                        </div>
                        <p class="text-red-600 mt-2">
                            You are about to stop the current booking for 
                            <span class="font-semibold">{{ roomToStop?.number }}</span>.
                        </p>
                    </div>
                    
                    <div v-if="roomToStop" class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
                        <h4 class="font-medium text-[#4b824b] mb-2">Booking Details:</h4>
                        <div class="space-y-1 text-sm text-[#344C34]">
                            <div class="flex justify-between">
                                <span>Room:</span>
                                <span class="font-medium">{{ roomToStop.number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Guest:</span>
                                <span class="font-medium">{{ roomToStop.guest }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Time:</span>
                                <span class="font-medium">{{ roomToStop.timeRange }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Status:</span>
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs font-medium">
                                    Will be Cancelled
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3 justify-end mt-6">
                        <Button 
                            variant="outline" 
                            class="border-gray-300 text-gray-700 hover:bg-gray-50"
                            @click="showStopConfirmModal = false"
                            :disabled="stopLoading"
                        >
                            Cancel
                        </Button>
                        <Button 
                            class="bg-red-600 hover:bg-red-700 text-white border-red-600"
                            @click="confirmStopRoom"
                            :disabled="stopLoading"
                        >
                            <svg class="w-4 h-4 mr-2" v-if="!stopLoading" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10l2 2 4-4"></path>
                            </svg>
                            <div class="w-4 h-4 mr-2 border-2 border-white border-t-transparent rounded-full animate-spin" v-else></div>
                            {{ stopLoading ? 'Stopping...' : 'Stop Booking' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Occupy Success Modal -->
        <Dialog v-model:open="showOccupySuccessModal">
            <DialogContent class="max-w-sm bg-[#FFFAE9] border-2 border-green-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-green-600 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Walk-in Booking Successful
                    </DialogTitle>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <p class="text-green-700 font-medium">
                                {{ occupySuccessInfo?.roomNumber }} has been successfully occupied!
                            </p>
                            <div class="mt-3 text-sm text-green-600">
                                <div><strong>Guest:</strong> {{ occupySuccessInfo?.guestName }}</div>
                                <div><strong>Time:</strong> {{ occupySuccessInfo?.timeRange }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex justify-center mt-6">
                        <Button 
                            class="bg-green-600 hover:bg-green-700 text-white border-green-600 px-6"
                            @click="showOccupySuccessModal = false"
                        >
                            OK
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Extend Success Modal -->
        <Dialog v-model:open="showExtendSuccessModal">
            <DialogContent class="max-w-sm bg-[#FFFAE9] border-2 border-blue-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-blue-600 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Booking Extended Successfully
                    </DialogTitle>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-blue-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-blue-700 font-medium">
                                {{ extendSuccessInfo?.roomNumber }} booking has been extended!
                            </p>
                            <div class="mt-3 text-sm text-blue-600">
                                <div><strong>New End Time:</strong> {{ extendSuccessInfo?.newEndTime }}</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex justify-center mt-6">
                        <Button 
                            class="bg-blue-600 hover:bg-blue-700 text-white border-blue-600 px-6"
                            @click="showExtendSuccessModal = false"
                        >
                            OK
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Stop Success Modal -->
        <Dialog v-model:open="showStopSuccessModal">
            <DialogContent class="max-w-sm bg-[#FFFAE9] border-2 border-green-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-green-600 flex items-center justify-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Booking Stopped Successfully
                    </DialogTitle>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="text-center">
                            <svg class="w-12 h-12 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10l2 2 4-4"></path>
                            </svg>
                            <p class="text-green-700 font-medium">
                                {{ stopSuccessInfo?.roomNumber }} booking has been stopped and cancelled!
                            </p>
                            <div class="mt-3 text-sm text-green-600">
                                <div><strong>Guest:</strong> {{ stopSuccessInfo?.guestName }}</div>
                                <div><strong>Status:</strong> Cancelled</div>
                                <div><strong>Room Status:</strong> Now Available</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex justify-center mt-6">
                        <Button 
                            class="bg-green-600 hover:bg-green-700 text-white border-green-600 px-6"
                            @click="showStopSuccessModal = false"
                        >
                            OK
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Past Time Error Modal -->
        <Dialog v-model:open="showPastTimeErrorModal">
            <DialogContent class="max-w-md bg-[#FFFAE9] border-2 border-red-500">
                <DialogHeader>
                    <DialogTitle class="text-xl font-bold text-red-600 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Invalid Time Selection
                    </DialogTitle>
                    <DialogDescription class="text-[#344C34] mt-2">
                        Please correct the time selection to proceed with the walk-in booking.
                    </DialogDescription>
                </DialogHeader>
                
                <div class="mt-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                            <div>
                                <p class="text-red-700 font-medium text-sm">Time Validation Error</p>
                                <p class="text-red-600 text-sm mt-1">
                                    {{ pastTimeErrorMessage }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Time Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="text-blue-700 font-medium text-sm">Current Time</p>
                                <p class="text-blue-600 text-sm">
                                    {{ new Date().toLocaleTimeString('en-US', { hour12: false, hour: '2-digit', minute: '2-digit' }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Button -->
                    <div class="flex justify-center mt-6">
                        <Button 
                            class="bg-red-600 hover:bg-red-700 text-white border-red-600 px-6"
                            @click="showPastTimeErrorModal = false"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Fix Time Selection
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style scoped>
/* Main background and layout */
.flex {
    background-color: #FFFAE9;
    color: #4b824b;
}

/* Statistics cards */
.stats-card {
    background-color: #4b824b !important;
    border-color: #4b824b !important;
}

/* Main content area */
.main-content-area {
    background-color: #FFFAE9;
    color: #4b824b;
    border: 1px solid #4b824b;
    border-radius: 0.75rem;
    min-height: 400px;
}

/* Individual room cards */
.room-card {
    background-color: #FFFAE9;
    border: 1px solid #4b824b;
    color: #4b824b;
}

.room-card:hover {
    border-color: #3d6b3d;
}

/* Ensure card headers and content have proper colors */
:deep(.card-header) {
    border-bottom-color: #4b824b !important;
}

/* Button styling */
.bg-\[#4b824b\] {
    background-color: #4b824b !important;
}

.hover\:bg-\[#3d6b3d\]:hover {
    background-color: #3d6b3d !important;
}

.text-\[#FFFAE9\] {
    color: #FFFAE9 !important;
}

.border-\[#4b824b\] {
    border-color: #4b824b !important;
}

.text-\[#4b824b\] {
    color: #4b824b !important;
}

.text-\[#344C34\] {
    color: #344C34 !important;
}

/* Override any conflicting card styles */
:deep(.card) {
    background-color: #FFFAE9 !important;
    border-color: #4b824b !important;
}

:deep(.card-title) {
    color: #4b824b !important;
}

/* Stats cards should have cream text since they have green backgrounds */
:deep(.stats-card .card-title) {
    color: #FFFAE9 !important;
}

/* More specific override for stats card titles */
:deep(.stats-card) :deep(.card-title) {
    color: #FFFAE9 !important;
}

/* Override the broad rule - only apply cream color to titles, not numbers */
.stats-card :deep(.card-title) {
    color: #FFFAE9 !important;
}

:deep(.card-description) {
    color: #344C34 !important;
}
</style>