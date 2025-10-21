<template>
  <div style="background: #232323; min-height: 100vh;">
    <!-- Header -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo">WECOLLAB</div>
        <nav class="nav">
          <a 
            href="#" 
            @click.prevent="handleAuthAction"
            :class="['nav-link', { 'logout-link': user }]"
          >
            {{ user ? 'Log out' : 'Log in' }}
          </a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="#" class="nav-link">What's NEW?</a>
          <a href="#" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content flex-1 flex flex-col items-center justify-start px-2 py-8 md:py-12">
      <div class="w-full max-w-6xl flex flex-col md:flex-row gap-12 md:gap-20 items-start justify-center">
        <!-- Left: Booking Title and History -->
        <section class="flex-1 min-w-[260px] max-w-xs w-full flex flex-col items-start">
          <h1 class="text-4xl font-bold mb-2 text-neutral-900">BOOKING</h1>
          <p class="mb-8 text-neutral-600">Wanna reserve for today or tommorow?</p>
          <Button 
            variant="default" 
            class="mb-8 w-full md:w-auto bg-[#495846] hover:bg-[#38613a] text-white border-none"
            @click="goToBookingHistory"
          >
            Booking History/modify
          </Button>
        </section>

        <!-- Right: Booking Form -->
        <section class="flex-1 min-w-[320px] max-w-lg w-full">
          <h2 class="text-xl font-semibold mb-6 text-neutral-900">Information</h2>
          <form class="space-y-4" @submit.prevent="submitBooking">
            <div class="flex gap-3">
              <div class="flex-1">
                <Label for="firstName">First name</Label>
                <Input id="firstName" v-model="form.firstName" autocomplete="given-name" required />
              </div>
              <div class="flex-1">
                <Label for="lastName">Last name</Label>
                <Input id="lastName" v-model="form.lastName" autocomplete="family-name" required />
              </div>
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <Label for="contact">Contact Number</Label>
                <Input 
                  id="contact" 
                  v-model="form.contact" 
                  type="tel" 
                  autocomplete="tel" 
                  pattern="[0-9]*"
                  @input="validateContactNumber"
                  required 
                />
              </div>
              <div class="flex-1">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" autocomplete="email" required />
              </div>
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <Label for="additional">Additional Info</Label>
                <Input id="additional" v-model="form.additional" />
              </div>
              <div class="flex-1">
                <Label for="pax">No. of Pax</Label>
                <Input 
                  id="pax" 
                  v-model="form.pax" 
                  type="number" 
                  min="1" 
                  max="10"
                  placeholder="1-10"
                  @input="validatePax"
                  required 
                />
              </div>
            </div>
            <Button type="submit" class="w-full mt-4 bg-[#495846] hover:bg-[#38613a] text-white border-none">Submit</Button>
          </form>
        </section>
      </div>

      <!-- Select a Room Section -->
      <div class="w-full max-w-6xl mt-16" id="room-selection">
        <h2 class="text-xl font-semibold mb-6 text-neutral-900">SELECT A ROOM</h2>
        
        <!-- Room Selection Alert -->
        <div 
          v-if="showRoomAlert"
          class="flex items-center gap-2 mb-4 p-3 bg-orange-50 border border-orange-200 rounded-lg text-orange-700"
        >
          <span class="text-orange-500 font-bold">!</span>
          <span>Please select a room.</span>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="room in rooms" :key="room.id" @click="canSelectRoom(room) && selectRoom(room.id)"
            :class="[
              'relative transition-all border-2 rounded-xl p-4 flex flex-col items-center',
              selectedRoom === room.id ? 'border-green-700 bg-green-50' : 'border-neutral-200 bg-white',
              canSelectRoom(room) ? 'cursor-pointer hover:border-green-600' : 'cursor-not-allowed opacity-60',
              roomsBlinking ? 'animate-pulse' : ''
            ]"
          >
            <!-- Disallow badge -->
            <div v-if="!canSelectRoom(room) && room.availableRooms > 0 && !isNaN(numericPax)"
                 class="absolute top-2 right-2 text-[10px] font-semibold px-2 py-1 rounded bg-red-100 text-red-700 border border-red-200">
              {{ paxDisallowText(room) }}
            </div>
            <img :src="room.image" :alt="room.name" class="w-48 h-36 object-cover rounded-lg mb-3" />
            <div class="text-lg font-bold text-neutral-900">{{ room.name }}</div>
            <div :class="['text-sm font-medium', getAvailabilityDisplay(room).color]">
              {{ room.availability }}
            </div>
            <div class="text-xs text-neutral-500 mt-1">{{ room.capacity }}</div>
            <!-- Show maintenance info if there are rooms under maintenance -->
            <div v-if="room.maintenanceRooms > 0" class="text-xs text-yellow-600 mt-1 font-medium">
              {{ room.maintenanceRooms }} room{{ room.maintenanceRooms > 1 ? 's' : '' }} under maintenance
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Logout Confirmation Modal -->
    <Dialog :open="showLogoutModal" @update:open="closeLogoutModal">
      <DialogContent class="sm:max-w-md bg-white">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
            Confirm Logout
          </DialogTitle>
          <DialogDescription class="text-center text-gray-600 mt-2">
            Are you sure you want to log out?
          </DialogDescription>
        </DialogHeader>
        
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button 
            variant="outline" 
            @click="closeLogoutModal"
            class="flex-1"
            style="border-color: #495846; color: #495846;"
          >
            Cancel
          </Button>
          <Button 
            @click="confirmLogout"
            class="flex-1 text-white border-none logout-btn"
          >
            Logout
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>


<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { ref, computed, onMounted } from 'vue';
import { router, Link, usePage } from '@inertiajs/vue3';

// Get props from backend
interface RoomCategory {
  id: number;
  name: string;
  category: string;
  totalRooms: number;
  availableRooms: number;
  occupiedRooms: number;
  maintenanceRooms: number;
  capacity: string;
  amenities: string[];
  description: string;
}

interface Props {
  roomCategories: RoomCategory[];
}

const props = defineProps<Props>();

// Get authentication data from Inertia
const page = usePage();
const user = computed(() => page.props.auth.user);

// Modal state
const showLogoutModal = ref(false);
const showRoomAlert = ref(false);
const roomsBlinking = ref(false);

const form = ref({
  firstName: '',
  lastName: '',
  contact: '',
  email: '',
  additional: '',
  pax: '',
});

const rooms = ref([
  {
    id: 1,
    name: 'INDIV ROOM',
    image: '/images/booking/indiv_room.png',
    availability: 'Loading...', // Will be updated from props
    capacity: '1-2 PAX ONLY',
    category: 'individual',
    totalRooms: 12,
    availableRooms: 0,
    occupiedRooms: 0,
    maintenanceRooms: 0
  },
  {
    id: 3,
    name: 'COMMON ROOM',
    image: '/images/booking/common_room.png',
    availability: 'Loading...', // Will be updated from props
    capacity: '3-4 PAX ONLY',
    category: 'common',
    totalRooms: 5,
    availableRooms: 0,
    occupiedRooms: 0,
    maintenanceRooms: 0
  },
  {
    id: 2,
    name: 'MASTER ROOM',
    image: '/images/booking/master_room.png',
    availability: 'Loading...', // Will be updated from props
    capacity: '5-10 PAX ONLY',
    category: 'master',
    totalRooms: 3,
    availableRooms: 0,
    occupiedRooms: 0,
    maintenanceRooms: 0
  },
]);

// Function to get availability status text and color
const getAvailabilityDisplay = (room: any) => {
  if (room.availableRooms === 0 && room.occupiedRooms > 0) {
    return { text: 'FULL', color: 'text-red-600 font-bold' };
  } else if (room.availableRooms === 0 && room.maintenanceRooms > 0) {
    return { text: 'MAINTENANCE', color: 'text-yellow-600 font-bold' };
  } else if (room.availableRooms === 1) {
    return { text: '1 ROOM VACANT', color: 'text-green-600 font-medium' };
  } else if (room.availableRooms > 1) {
    return { text: `${room.availableRooms} ROOMS VACANT`, color: 'text-green-600 font-medium' };
  } else {
    return { text: 'NO ROOMS AVAILABLE', color: 'text-red-600 font-bold' };
  }
};

// Function to update room availability from props
const updateRoomAvailability = () => {
  console.log('Room categories from props:', props.roomCategories);
  
  rooms.value.forEach(room => {
    const roomData = props.roomCategories.find(data => data.category === room.category);
    if (roomData) {
      console.log(`Updating ${room.category} room:`, roomData);
      room.totalRooms = roomData.totalRooms;
      room.availableRooms = roomData.availableRooms;
      room.occupiedRooms = roomData.occupiedRooms;
      room.maintenanceRooms = roomData.maintenanceRooms;
      
      // Update availability text
      const display = getAvailabilityDisplay(room);
      room.availability = display.text;
      console.log(`${room.category} availability: ${room.availability}`);
    } else {
      console.log(`No data found for ${room.category}`);
      room.availability = 'No data available';
    }
  });
};

// Update room availability on component mount
onMounted(() => {
  updateRoomAvailability();
});

const selectedRoom = ref<number|null>(null);

function selectRoom(id: number) {
  // Find the room data
  const room = rooms.value.find(r => r.id === id);
  
  // Check if room is available for booking
  if (room && room.availableRooms === 0) {
    // Show alert for unavailable rooms
    if (room.occupiedRooms > 0) {
      alert('This room type is currently full. Please select another room or try again later.');
    } else if (room.maintenanceRooms > 0) {
      alert('This room type is currently under maintenance. Please select another room.');
    } else {
      alert('This room type is currently unavailable. Please select another room.');
    }
    return;
  }
  if (room && !isRoomAllowedByPax(room.category, numericPax.value)) {
    // Show guidance alert based on pax
    alert(`This room is for ${room.capacity}. Please adjust the number of pax or select a different room.`);
    return;
  }
  selectedRoom.value = id;
  // Hide room alert when a room is selected
  showRoomAlert.value = false;
  roomsBlinking.value = false;
}

// Function to validate contact number input (numbers only)
function validateContactNumber(event: Event) {
  const target = event.target as HTMLInputElement;
  // Remove any non-numeric characters
  target.value = target.value.replace(/[^0-9]/g, '');
  // Update the form value
  form.value.contact = target.value;
}

// Clamp pax input between 1 and 10
function validatePax(event: Event) {
  const target = event.target as HTMLInputElement;
  let val = target.value.replace(/[^0-9]/g, '');
  if (val === '') {
    target.value = '';
    form.value.pax = '';
    return;
  }
  let n = parseInt(val, 10);
  if (isNaN(n)) {
    target.value = '';
    form.value.pax = '';
    return;
  }
  if (n < 1) n = 1;
  if (n > 10) n = 10;
  target.value = n.toString();
  form.value.pax = target.value;
}

function submitBooking() {
  // Validate form data first
  if (!form.value.firstName || !form.value.lastName || !form.value.contact || 
      !form.value.email || !form.value.pax) {
    alert('Please fill in all required fields');
    return;
  }

  // Check if room is selected
  if (!selectedRoom.value) {
    // Scroll to room selection section
    const roomSection = document.getElementById('room-selection');
    if (roomSection) {
      roomSection.scrollIntoView({ 
        behavior: 'smooth',
        block: 'center'
      });
    }

    // Show alert and start blinking animation
    showRoomAlert.value = true;
    roomsBlinking.value = true;

    // Stop blinking after 2 slow blinks (4 seconds total)
    setTimeout(() => {
      roomsBlinking.value = false;
    }, 4000);

    // Hide alert after 5 seconds
    setTimeout(() => {
      showRoomAlert.value = false;
    }, 5000);

    return;
  }

  // Get the category from the selected room
  const selectedRoomData = rooms.value.find(room => room.id === selectedRoom.value);
  const category = selectedRoomData?.category || 'individual';

  // Store booking data in session storage for the next page
  const bookingData = {
    ...form.value,
    category: category,
    room_id: selectedRoom.value
  };
  
  // Pass data to schedule page using Inertia
  router.visit('/booking/schedule', {
    method: 'post',
    data: bookingData
  });
}

function goHome() {
  window.location.href = '/';
}

function goToBookingHistory() {
  router.visit('/booking/history');
}

function closeLogoutModal() {
  showLogoutModal.value = false;
}

function confirmLogout() {
  showLogoutModal.value = false;
  router.post('/logout');
}

function handleLogout() {
  showLogoutModal.value = true;
}

function handleAuthAction() {
  if (user.value) {
    handleLogout();
  } else {
    router.visit('/login');
  }
}

// Numeric pax computed property
const numericPax = computed(() => {
  const n = parseInt((form.value.pax || '').toString(), 10);
  return isNaN(n) ? NaN : n;
});

// Determines if a room category is allowed by pax rules
function isRoomAllowedByPax(category: string, pax: number): boolean {
  if (isNaN(pax)) return true;
  if (pax >= 1 && pax <= 2) return category === 'individual';
  if (pax >= 5 && pax <= 10) return category === 'master';
  if (pax >= 3 && pax <= 4) return category === 'common';
  return false;
}

// Used to enable/disable room selection
function canSelectRoom(room: any): boolean {
  return room.availableRooms > 0 && isRoomAllowedByPax(room.category, numericPax.value);
}

// Shows the "Not allowed for X pax" badge
function paxDisallowText(room: any): string {
  const p = numericPax.value;
  if (isNaN(p)) return '';
  if (p >= 1 && p <= 2 && room.category !== 'individual') return `Not allowed for ${p} pax`;
  if (p >= 3 && p <= 4 && room.category !== 'common') return `Not allowed for ${p} pax`;
  if (p >= 5 && p <= 10 && room.category !== 'master') return `Not allowed for ${p} pax`;
  if (!(p === 1 || (p >= 3 && p <= 4) || (p >= 5 && p <= 10))) return `Not allowed for ${p} pax`;
  return '';
}
</script>

<style scoped>
.header {
  background: #495846;
  color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  z-index: 100;
  box-shadow: 0 1px 6px rgba(0,0,0,0.03);
}
.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.5rem 2rem;
  min-height: 54px;
  width: 100vw;
}
.logo {
  font-weight: bold;
  letter-spacing: 0.1em;
  font-size: 1.5rem;
  line-height: 1;
}
.nav {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}
.nav-link {
  color: #fff;
  text-decoration: none;
  font-size: 1rem;
  padding: 0.3rem 0.7rem;
  border-radius: 6px;
  transition: background 0.2s, color 0.2s;
  line-height: 1.2;
}
.nav-link:hover {
  background: #fff;
  color: #495846;
}
.logout-link:hover {
  background: #dc2626 !important;
  color: #fff !important;
}
.home-btn {
  background: #fff;
  color: #495846;
  border: none;
  border-radius: 6px;
  padding: 0.3rem 1rem;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  margin-left: 0.5rem;
  line-height: 1.2;
}
.home-btn:hover {
  background: #e0e0e0;
  color: #495846;
}
main.main-content {
  min-height: 100vh;
  background: #f5f5f5;
  width: 100vw;
  max-width: 100vw;
  overflow-x: hidden;
  margin-top: 54px;
}

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}
.logout-btn:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}

/* Custom slow pulse animation for room selection */
@keyframes slow-pulse {
  0%, 100% {
    opacity: 1;
    transform: scale(1);
  }
  50% {
    opacity: 0.7;
    transform: scale(1.02);
  }
}

.animate-pulse {
  animation: slow-pulse 2s ease-in-out infinite;
}

/* Responsive and minimal tweaks */
@media (max-width: 900px) {
  main.flex-1 {
    flex-direction: column;
    gap: 2rem;
  }
  .grid-cols-3 {
    grid-template-columns: 1fr !important;
  }
}
</style>
