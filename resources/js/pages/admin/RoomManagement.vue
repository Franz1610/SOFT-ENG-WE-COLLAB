<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Building, MapPin, Users, Wifi, Monitor, Coffee, Car, User, Clock, DollarSign, RefreshCw } from 'lucide-vue-next';
import { ref, computed } from 'vue';
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
const selectedCategory = ref<RoomCategory | null>(null);
const roomToDelete = ref<IndividualRoom | null>(null);
const deletedRoomInfo = ref<{number: string, category: string} | null>(null);
const addedRoomInfo = ref<{numberOfRooms: number, category: string} | null>(null);
const individualRooms = ref<IndividualRoom[]>([]);
const editableRooms = ref<IndividualRoom[]>([]);
const loading = ref(false);
const editLoading = ref(false);
const addRoomLoading = ref(false);
const deleteLoading = ref(false);

// Add room form data
const addRoomForm = ref({
    category: '',
    numberOfRooms: 1
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
const fetchIndividualRooms = async (category: RoomCategory) => {
    loading.value = true;
    try {
        const response = await axios.get(`/admin/rooms/${category.category}`);
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
        // If you need to refresh the entire page data, you can use Inertia's visit method
        // For now, we'll just refresh the current modal if it's open
        if (selectedCategory.value) {
            individualRooms.value = await fetchIndividualRooms(selectedCategory.value);
        }
    } catch (error) {
        console.error('Error refreshing room data:', error);
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'Available':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'Occupied':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'Reserved':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'Maintenance':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
};

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
        const response = await axios.delete(`/admin/rooms/delete`, {
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
</script>

<template>
    <div style="background: #232323; min-height: 100vh;">
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
                    <DialogTitle class="text-3xl text-[#4b824b] font-bold">{{ selectedCategory?.name }} Rooms</DialogTitle>
                    <DialogDescription class="text-lg text-[#344C34] mt-2">
                        {{ selectedCategory?.description }}
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