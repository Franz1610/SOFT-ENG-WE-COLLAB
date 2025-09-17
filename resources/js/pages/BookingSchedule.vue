<template>
  <div style="background: #232323; min-height: 100vh;">
    <!-- Header -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo" @click="goHome">WECOLLAB</div>
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
          <a href="/booking" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content flex-1 flex flex-col items-center justify-center px-6 py-16">
      <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-2 text-center text-neutral-900">SELECT DATE & TIME</h1>
        <p class="text-center text-neutral-600 mb-8">Choose your preferred date and time for your booking</p>
        
        <form @submit.prevent="submitSchedule" class="space-y-6">
          <div>
            <Label for="date">Date</Label>
            <Input 
              id="date" 
              v-model="form.date" 
              type="date" 
              :min="minDate"
              required 
              class="mt-1"
            />
          </div>
          
          <!-- Start Time -->
          <div>
            <Label>Start Time</Label>
            <div class="mt-1 flex gap-2 items-center">
              <div class="flex items-center bg-gray-50 rounded-md p-1">
                <input 
                  v-model="form.startTime.hour"
                  type="number" 
                  min="01" 
                  max="12" 
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'startTime', 'hour')"
                />
                <span class="mx-1 text-lg">:</span>
                <input 
                  v-model="form.startTime.minute"
                  type="number" 
                  min="00" 
                  max="59" 
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'startTime', 'minute')"
                />
              </div>
              <select 
                v-model="form.startTime.period"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>
            </div>
          </div>

          <!-- End Time -->
          <div>
            <Label>End Time</Label>
            <div class="mt-1 flex gap-2 items-center">
              <div class="flex items-center bg-gray-50 rounded-md p-1">
                <input 
                  v-model="form.endTime.hour"
                  type="number" 
                  min="01" 
                  max="12" 
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'endTime', 'hour')"
                />
                <span class="mx-1 text-lg">:</span>
                <input 
                  v-model="form.endTime.minute"
                  type="number" 
                  min="00" 
                  max="59" 
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'endTime', 'minute')"
                />
              </div>
              <select 
                v-model="form.endTime.period"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>
            </div>
          </div>
          
          <div class="flex gap-3">
            <Button 
              type="button" 
              variant="outline" 
              class="flex-1" 
              @click="goBack"
            >
              Back
            </Button>
            <Button 
              type="submit" 
              class="flex-1 bg-[#495846] hover:bg-[#38613a] text-white border-none"
            >
              Confirm Schedule
            </Button>
          </div>
        </form>
      </div>
    </main>

    <!-- Confirmation Modal -->
    <Dialog :open="showConfirmationModal" @update:open="closeModal">
      <DialogContent class="sm:max-w-md bg-white">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
            🎉 Booking Confirmed!
          </DialogTitle>
          <DialogDescription class="text-center text-gray-600 mt-2">
            Your booking has been successfully scheduled.
          </DialogDescription>
        </DialogHeader>
        
        <div class="py-6 px-4 rounded-lg border mx-6" style="background: #FFFAE9; border-color: #495846;">
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="font-medium" style="color: #495846;">Date:</span>
              <span class="font-semibold" style="color: #495846;">{{ form.date }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium" style="color: #495846;">Start Time:</span>
              <span class="font-semibold" style="color: #495846;">{{ formatTimeDisplay(form.startTime) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium" style="color: #495846;">End Time:</span>
              <span class="font-semibold" style="color: #495846;">{{ formatTimeDisplay(form.endTime) }}</span>
            </div>
          </div>
        </div>
        
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button 
            variant="outline" 
            @click="viewMyBookings"
            class="flex-1"
            style="border-color: #495846; color: #495846;"
          >
            View My Booking
          </Button>
          <Button 
            @click="confirmBooking"
            class="flex-1 text-white border-none modal-confirm-btn"
          >
            Go to Home
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

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
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
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

// Get authentication data from Inertia
const page = usePage();
const user = computed(() => page.props.auth.user);

const form = ref({
  date: '',
  startTime: {
    hour: '00',
    minute: '00',
    period: 'AM'
  },
  endTime: {
    hour: '00',
    minute: '00',
    period: 'AM'
  }
});

// Modal state
const showConfirmationModal = ref(false);
const showLogoutModal = ref(false);

// Set minimum date to today
const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

// Format time input to ensure 2 digits
function formatTimeInput(event: Event, timeType: 'startTime' | 'endTime', field: 'hour' | 'minute') {
  const target = event.target as HTMLInputElement;
  const value = parseInt(target.value);
  
  if (field === 'hour') {
    if (value < 1) target.value = '01';
    else if (value > 12) target.value = '12';
    else target.value = value.toString().padStart(2, '0');
    form.value[timeType].hour = target.value;
  } else if (field === 'minute') {
    if (value < 0) target.value = '00';
    else if (value > 59) target.value = '59';
    else target.value = value.toString().padStart(2, '0');
    form.value[timeType].minute = target.value;
  }
}

// Format time for display
function formatTimeDisplay(timeObj: { hour: string; minute: string; period: string }) {
  return `${timeObj.hour}:${timeObj.minute} ${timeObj.period}`;
}

function submitSchedule() {
  if (!form.value.date || !form.value.startTime.hour || !form.value.endTime.hour) {
    alert('Please select date, start time, and end time');
    return;
  }
  
  // Show confirmation modal instead of alert
  showConfirmationModal.value = true;
}

function confirmBooking() {
  // Get booking data from props (passed from session)
  const bookingData = page.props as any;
  
  // Prepare booking data
  const submitData = {
    first_name: bookingData.firstName || '',
    last_name: bookingData.lastName || '',
    contact: bookingData.contact || '',
    email: bookingData.email || '',
    additional_info: bookingData.additional || '',
    pax: bookingData.pax || 1,
    category: bookingData.category || 'individual',
    room_id: bookingData.room_id || 1,
    booking_date: form.value.date,
    start_time: formatTimeDisplay(form.value.startTime),
    end_time: formatTimeDisplay(form.value.endTime),
  };

  // Send booking data to backend
  router.post('/booking/store', submitData, {
    onSuccess: () => {
      showConfirmationModal.value = false;
      router.visit('/');
    },
    onError: (errors) => {
      console.error('Booking error:', errors);
      alert('There was an error creating your booking. Please try again.');
    }
  });
}

function closeModal() {
  showConfirmationModal.value = false;
}

function viewMyBookings() {
  // Get booking data from props (passed from session)
  const bookingData = page.props as any;
  
  // Prepare booking data
  const submitData = {
    first_name: bookingData.firstName || '',
    last_name: bookingData.lastName || '',
    contact: bookingData.contact || '',
    email: bookingData.email || '',
    additional_info: bookingData.additional || '',
    pax: bookingData.pax || 1,
    category: bookingData.category || 'individual',
    room_id: bookingData.room_id || 1,
    booking_date: form.value.date,
    start_time: formatTimeDisplay(form.value.startTime),
    end_time: formatTimeDisplay(form.value.endTime),
  };

  // Send booking data to backend
  router.post('/booking/store', submitData, {
    onSuccess: () => {
      showConfirmationModal.value = false;
      router.visit('/booking/history');
    },
    onError: (errors) => {
      console.error('Booking error:', errors);
      alert('There was an error creating your booking. Please try again.');
    }
  });
}

function closeLogoutModal() {
  showLogoutModal.value = false;
}

function confirmLogout() {
  showLogoutModal.value = false;
  router.post('/logout');
}

function goBack() {
  router.visit('/booking');
}

function goHome() {
  router.visit('/');
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
  cursor: pointer;
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

/* Modal button overrides */
.modal-confirm-btn {
  background-color: #495846 !important;
  border-color: #495846 !important;
}
.modal-confirm-btn:hover {
  background-color: #38613a !important;
  border-color: #38613a !important;
}

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}
.logout-btn:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}
</style>
