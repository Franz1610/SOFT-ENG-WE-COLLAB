<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Building, MapPin, Users, Wifi, Monitor, Coffee, Car } from 'lucide-vue-next';

// Mock data for demonstration - this would come from the backend in a real implementation
const rooms = [
    {
        id: 1,
        name: 'Conference Room A',
        location: 'Floor 1, East Wing',
        capacity: 12,
        status: 'Available',
        amenities: ['Projector', 'WiFi', 'Coffee Station', 'Whiteboard'],
        description: 'Large conference room perfect for team meetings and presentations.'
    },
    {
        id: 2,
        name: 'Meeting Room B',
        location: 'Floor 2, West Wing',
        capacity: 8,
        status: 'Occupied',
        amenities: ['Smart TV', 'WiFi', 'Video Conferencing'],
        description: 'Medium-sized room ideal for smaller team discussions.'
    },
    {
        id: 3,
        name: 'Boardroom',
        location: 'Floor 3, Executive Suite',
        capacity: 20,
        status: 'Available',
        amenities: ['4K Display', 'WiFi', 'Coffee Station', 'Parking'],
        description: 'Executive boardroom for important meetings and presentations.'
    }
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'Available':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'Occupied':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'Maintenance':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
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
                    <Button class="bg-[#4b824b] hover:bg-[#3d6b3d] text-[#FFFAE9] border-[#4b824b]">
                        <Building class="w-4 h-4 mr-2" />
                        Add New Room
                    </Button>
                </div>

                <!-- Statistics Cards -->
                <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Total Rooms</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold text-[#FFFAE9]">12</div>
                            <p class="text-xs text-[#FFFAE9]/80">All spaces</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Available</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #86efac !important;">8</div>
                            <p class="text-xs text-[#FFFAE9]/80">Ready to book</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Occupied</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #fca5a5 !important;">3</div>
                            <p class="text-xs text-[#FFFAE9]/80">Currently in use</p>
                        </CardContent>
                    </Card>
                    
                    <Card class="stats-card">
                        <CardHeader class="pb-2">
                            <CardTitle class="text-sm font-medium" style="color: #FFFAE9 !important;">Maintenance</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <div class="text-2xl font-bold" style="color: #fde68a !important;">1</div>
                            <p class="text-xs text-[#FFFAE9]/80">Under maintenance</p>
                        </CardContent>
                    </Card>
                </div>

                <!-- Rooms Grid -->
                <div class="main-content-area">
                    <div class="p-6">
                        <h2 class="text-xl font-semibold text-[#4b824b] mb-4">All Rooms</h2>
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            <Card v-for="room in rooms" :key="room.id" class="room-card hover:shadow-lg transition-shadow">
                                <CardHeader>
                                    <div class="flex items-center justify-between">
                                        <CardTitle class="text-lg text-[#4b824b]">{{ room.name }}</CardTitle>
                                        <span :class="getStatusColor(room.status)" class="text-xs px-2 py-1 rounded-full border">
                                            {{ room.status }}
                                        </span>
                                    </div>
                                    <CardDescription class="text-[#344C34]">
                                        {{ room.description }}
                                    </CardDescription>
                                </CardHeader>
                                <CardContent class="space-y-4">
                                    <div class="flex items-center text-sm text-[#344C34]">
                                        <MapPin class="w-4 h-4 mr-2 text-[#4b824b]" />
                                        {{ room.location }}
                                    </div>
                                    <div class="flex items-center text-sm text-[#344C34]">
                                        <Users class="w-4 h-4 mr-2 text-[#4b824b]" />
                                        Capacity: {{ room.capacity }} people
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-[#4b824b] mb-2">Amenities:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <div v-for="amenity in room.amenities" :key="amenity" 
                                                 class="flex items-center text-xs bg-[#4b824b]/10 text-[#4b824b] px-2 py-1 rounded-md border border-[#4b824b]/20">
                                                <component :is="getAmenityIcon(amenity)" class="w-3 h-3 mr-1" />
                                                {{ amenity }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 pt-2">
                                        <Button variant="outline" size="sm" class="flex-1 border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]">
                                            Edit
                                        </Button>
                                        <Button variant="outline" size="sm" class="flex-1 border-[#4b824b] text-[#4b824b] hover:bg-[#4b824b] hover:text-[#FFFAE9]">
                                            View Schedule
                                        </Button>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
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