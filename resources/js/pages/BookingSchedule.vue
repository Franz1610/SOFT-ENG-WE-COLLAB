<template>
  <div style="background: #232323; min-height: 100vh;">
    <!-- Header -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo" @click="goHome">WECOLLAB</div>
        <button
          class="hamburger-btn"
          @click="menuOpen = !menuOpen"
          :aria-expanded="menuOpen"
          aria-label="Toggle navigation menu"
        >
          <span class="hamburger-icon" aria-hidden="true"></span>
        </button>
        <nav class="nav">
          <a 
            href="#" 
            @click.prevent="handleAuthAction"
            :class="['nav-link', { 'logout-link': user }]"
          >
            {{ user ? 'Log out' : 'Log in' }}
          </a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="/whats-new" class="nav-link">What's NEW?</a>
          <span class="nav-link active">Booking</span>
          <Link href="/" class="nav-link">HOME</Link>
        </nav>
        <div v-if="menuOpen" class="mobile-menu">
          <a
            href="#"
            @click.prevent="handleAuthAction(); menuOpen = false"
            :class="['nav-link', { 'logout-link': user } ]"
          >
            {{ user ? 'Log out' : 'Log in' }}
          </a>
          <a href="#" class="nav-link" @click="menuOpen = false">Deals & Promo</a>
          <a href="/whats-new" class="nav-link" @click="menuOpen = false">What's NEW?</a>
          <span class="nav-link active" @click="menuOpen = false">Booking</span>
          <Link href="/" class="nav-link" @click="menuOpen = false">HOME</Link>
        </div>
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
                  placeholder="01"
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'startTime', 'hour')"
                />
                <span class="mx-1 text-lg">:</span>
                <input 
                  v-model="form.startTime.minute"
                  type="number" 
                  min="00" 
                  max="59" 
                  placeholder="00"
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
                  placeholder="01"
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'endTime', 'hour')"
                />
                <span class="mx-1 text-lg">:</span>
                <input 
                  v-model="form.endTime.minute"
                  type="number" 
                  min="00" 
                  max="59" 
                  placeholder="00"
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

    <!-- One Hour Advance Warning Modal -->
    <Dialog :open="showOneHourWarningModal" @update:open="closeOneHourWarningModal">
      <DialogContent class="sm:max-w-md bg-white">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #dc2626;">
            ⚠️ Advance Booking Required
          </DialogTitle>
          <DialogDescription class="text-center text-gray-600 mt-2">
            To avoid rush bookings, all reservations must be made at least 1 hour in advance.
          </DialogDescription>
        </DialogHeader>
        
        <div class="py-4 px-4 rounded-lg border mx-6" style="background: #fef2f2; border-color: #dc2626;">
          <div class="space-y-2 text-center">
            <div class="font-medium" style="color: #dc2626;">Current Time:</div>
            <div class="font-semibold text-lg" style="color: #dc2626;">{{ currentTimeDisplay }}</div>
            <div class="font-medium mt-3" style="color: #dc2626;">Earliest Booking Time:</div>
            <div class="font-semibold text-lg" style="color: #dc2626;">{{ minimumTimeDisplay }}</div>
          </div>
        </div>
        
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button 
            @click="closeOneHourWarningModal"
            class="flex-1 text-white border-none"
            style="background-color: #dc2626; border-color: #dc2626;"
          >
            I Understand
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Past Time Warning Modal -->
    <Dialog :open="showPastTimeWarningModal" @update:open="closePastTimeWarningModal">
      <DialogContent class="sm:max-w-md bg-white">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #dc2626;">
            ⏰ Past Time Selected
          </DialogTitle>
          <DialogDescription class="text-center text-gray-600 mt-2">
            You cannot book a time that has already passed. Please select a future time.
          </DialogDescription>
        </DialogHeader>
        
        <div class="py-4 px-4 rounded-lg border mx-6" style="background: #fef2f2; border-color: #dc2626;">
          <div class="space-y-2 text-center">
            <div class="font-medium" style="color: #dc2626;">Current Time:</div>
            <div class="font-semibold text-lg" style="color: #dc2626;">{{ currentTimeDisplay }}</div>
            <div class="font-medium mt-3" style="color: #dc2626;">Earliest Available Time:</div>
            <div class="font-semibold text-lg" style="color: #dc2626;">{{ minimumTimeDisplay }}</div>
            <div class="text-sm mt-2" style="color: #dc2626;">
              (Bookings must be at least 1 hour in advance)
            </div>
          </div>
        </div>
        
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button 
            @click="closePastTimeWarningModal"
            class="flex-1 text-white border-none"
            style="background-color: #dc2626; border-color: #dc2626;"
          >
            Choose Different Time
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { router, usePage, Link } from '@inertiajs/vue3';
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
    hour: '',
    minute: '',
    period: 'AM'
  },
  endTime: {
    hour: '',
    minute: '',
    period: 'AM'
  }
});

// Modal state
const showConfirmationModal = ref(false);
const showLogoutModal = ref(false);
const showOneHourWarningModal = ref(false);
const showPastTimeWarningModal = ref(false);
const isValidating = ref(false);

// Mobile menu state
const menuOpen = ref(false);

// Reactive time display properties that auto-update
const currentTimeDisplay = ref('');
const minimumTimeDisplay = ref('');
let timeUpdateInterval: number | null = null;

// Function to update time displays
function updateTimeDisplays() {
  const now = new Date();
  
  // Update current time display
  const manilaTimeString12h = now.toLocaleString("en-US", {
    timeZone: "Asia/Manila",
    hour12: true,
    hour: 'numeric',
    minute: '2-digit'
  });
  currentTimeDisplay.value = manilaTimeString12h;
  
  // Update minimum time display
  const manilaTimeString24h = now.toLocaleString("en-US", {
    timeZone: "Asia/Manila",
    hour12: false,
    hour: '2-digit',
    minute: '2-digit'
  });
  const [currentHour, currentMinute] = manilaTimeString24h.split(':').map(Number);
  const minimumMinutes = (currentHour * 60 + currentMinute) + 60; // Add 1 hour
  const minimumHour = Math.floor(minimumMinutes / 60) % 24;
  const minimumMin = minimumMinutes % 60;
  
  // Convert to 12-hour format
  let displayHour = minimumHour;
  const period = minimumHour >= 12 ? 'PM' : 'AM';
  if (displayHour === 0) displayHour = 12;
  else if (displayHour > 12) displayHour = displayHour - 12;
  
  minimumTimeDisplay.value = `${displayHour}:${minimumMin.toString().padStart(2, '0')} ${period}`;
}

// Setup auto-refresh when component mounts
onMounted(() => {
  // Initial update
  updateTimeDisplays();
  
  // Update every second (1000ms)
  timeUpdateInterval = setInterval(updateTimeDisplays, 1000);

  // Prefill defaults: if landing on this page for today with empty fields,
  // set start/end to the earliest allowed time (now + 1 hour, Manila time)
  try {
    // Set default date to today if not provided
    const todayManila = new Intl.DateTimeFormat('en-CA', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    }).format(new Date());
    if (!form.value.date) {
      form.value.date = todayManila;
    }

    // Only set default times if empty
    const startEmpty = !form.value.startTime.hour || !form.value.startTime.minute;
    const endEmpty = !form.value.endTime.hour || !form.value.endTime.minute;
    if (startEmpty && endEmpty) {
      const now = new Date();
      const manilaString24 = now.toLocaleString('en-US', {
        timeZone: 'Asia/Manila',
        hour12: false,
        hour: '2-digit',
        minute: '2-digit'
      });
      const [h, m] = manilaString24.split(':').map(Number);
      // Minimum = now + 60 minutes
      let mins = h * 60 + m + 60;
      // Round up to nearest 5 minutes for nicer UX
      const round = 5;
      mins = Math.ceil(mins / round) * round;
      const startHour24 = Math.floor(mins / 60) % 24;
      const startMin = mins % 60;
      const startPeriod = startHour24 >= 12 ? 'PM' : 'AM';
      let startHour12 = startHour24 % 12;
      if (startHour12 === 0) startHour12 = 12;

      // End time = start + 60 minutes
      let endMins = mins + 60;
      const endHour24 = Math.floor(endMins / 60) % 24;
      const endMin = endMins % 60;
      const endPeriod = endHour24 >= 12 ? 'PM' : 'AM';
      let endHour12 = endHour24 % 12;
      if (endHour12 === 0) endHour12 = 12;

      form.value.startTime = {
        hour: String(startHour12).padStart(2, '0'),
        minute: String(startMin).padStart(2, '0'),
        period: startPeriod
      };
      form.value.endTime = {
        hour: String(endHour12).padStart(2, '0'),
        minute: String(endMin).padStart(2, '0'),
        period: endPeriod
      };
    }
  } catch (e) {
    // Non-fatal: ignore and let user choose manually
  }
});

// Cleanup interval when component unmounts
onUnmounted(() => {
  if (timeUpdateInterval) {
    clearInterval(timeUpdateInterval);
    timeUpdateInterval = null;
  }
});

// Set minimum date to today in Asia/Manila timezone
const minDate = computed(() => {
  // Get current date in Asia/Manila timezone using Intl.DateTimeFormat
  const now = new Date();
  const manilaDate = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  }).format(now);
  return manilaDate; // Returns YYYY-MM-DD format
});

// Helper function to convert 12-hour to 24-hour format
function convertTo24Hour(hour: string, period: string): number {
  let hour24 = parseInt(hour);
  if (period === 'AM' && hour24 === 12) {
    hour24 = 0;
  } else if (period === 'PM' && hour24 !== 12) {
    hour24 += 12;
  }
  return hour24;
}

// Validate time for today's bookings - requires 1 hour advance booking (no alerts, just returns boolean)
function validateTimeForToday() {
  if (!form.value.date || isValidating.value) return true;
  
  const selectedDate = new Date(form.value.date);
  // Get current Manila date
  const now = new Date();
  const manilaDateString = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  }).format(now);
  
  const manilaToday = new Date(manilaDateString);
  selectedDate.setHours(0, 0, 0, 0);
  manilaToday.setHours(0, 0, 0, 0);
  
  // Only validate time if the selected date is today
  if (selectedDate.getTime() === manilaToday.getTime()) {
    // Get current Manila time
    const manilaTimeString = now.toLocaleString("en-US", {
      timeZone: "Asia/Manila",
      hour12: false,
      hour: '2-digit',
      minute: '2-digit'
    });
    const [currentHour, currentMinute] = manilaTimeString.split(':').map(Number);
    
    // Calculate minimum booking time (current time + 1 hour)
    const currentTotalMinutes = currentHour * 60 + currentMinute;
    const minimumBookingMinutes = currentTotalMinutes + 60; // Add 1 hour (60 minutes)
    
    // Validate start time
    if (form.value.startTime.hour && form.value.startTime.minute && form.value.startTime.period) {
      const startHour24 = convertTo24Hour(form.value.startTime.hour, form.value.startTime.period);
      const startMinute = parseInt(form.value.startTime.minute);
      const startTotalMinutes = startHour24 * 60 + startMinute;
      
      if (startTotalMinutes < minimumBookingMinutes) {
        return false;
      }
    }
    
    // Validate end time
    if (form.value.endTime.hour && form.value.endTime.minute && form.value.endTime.period) {
      const endHour24 = convertTo24Hour(form.value.endTime.hour, form.value.endTime.period);
      const endMinute = parseInt(form.value.endTime.minute);
      const endTotalMinutes = endHour24 * 60 + endMinute;
      
      if (endTotalMinutes < minimumBookingMinutes) {
        return false;
      }
    }
  }
  return true;
}

// Watch for date changes and prevent past date selection
watch(() => form.value.date, (newDate) => {
  if (newDate) {
    // Get current Manila date
    const now = new Date();
    const manilaDateString = new Intl.DateTimeFormat('en-CA', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: '2-digit',
      day: '2-digit'
    }).format(now);
    
    if (newDate < manilaDateString) {
      form.value.date = '';
      alert('Cannot select past dates. Please choose today or a future date.');
    }
  }
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
  console.log('=== SUBMIT SCHEDULE CALLED ===');
  console.log('Form data:', form.value);
  
  // Better validation for required fields
  if (!form.value.date) {
    alert('Please select a date');
    return;
  }
  
  if (!form.value.startTime.hour || !form.value.startTime.minute) {
    alert('Please select start time');
    return;
  }
  
  if (!form.value.endTime.hour || !form.value.endTime.minute) {
    alert('Please select end time');
    return;
  }
  
  // Check if selected date is in the past
  const selectedDate = new Date(form.value.date);
  
  // Get current date and time in Asia/Manila timezone using Intl API
  const now = new Date();
  const manilaDateString = new Intl.DateTimeFormat('en-CA', {
    timeZone: 'Asia/Manila',
    year: 'numeric',
    month: '2-digit',
    day: '2-digit'
  }).format(now);
  
  const manilaTimeString = now.toLocaleString("en-US", {
    timeZone: "Asia/Manila",
    hour12: false,
    hour: '2-digit',
    minute: '2-digit'
  });
  
  const selectedDateString = form.value.date;
  
  console.log('Today (Manila timezone):', manilaDateString);
  console.log('Current Manila time:', manilaTimeString);
  console.log('Selected date:', selectedDateString);
  
  if (selectedDateString < manilaDateString) {
    console.log('BLOCKING: Selected date is in the past');
    showPastTimeWarningModal.value = true;
    return;
  }
  
  // Check if selected time meets 1-hour advance requirement (only for today)
  if (selectedDateString === manilaDateString) {
    console.log('=== TIME VALIDATION FOR TODAY - 1 HOUR ADVANCE ===');
    console.log('Original browser time:', now.toLocaleString());
    console.log('Manila date:', manilaDateString);
    console.log('Manila time:', manilaTimeString);
    
    const [currentHour, currentMinute] = manilaTimeString.split(':').map(Number);
    
    console.log('Current time (Manila):', currentHour + ':' + currentMinute);
    console.log('Selected start time:', form.value.startTime);
    console.log('Selected end time:', form.value.endTime);
    
    const startHour24 = convertTo24Hour(form.value.startTime.hour, form.value.startTime.period);
    const startMinute = parseInt(form.value.startTime.minute);
    const endHour24 = convertTo24Hour(form.value.endTime.hour, form.value.endTime.period);
    const endMinute = parseInt(form.value.endTime.minute);
    
    console.log('Converted start time 24h:', startHour24 + ':' + startMinute);
    console.log('Converted end time 24h:', endHour24 + ':' + endMinute);
    
    // Calculate minimum booking time (current time + 1 hour)
    const currentTotalMinutes = currentHour * 60 + currentMinute;
    const minimumBookingMinutes = currentTotalMinutes + 60; // Add 1 hour (60 minutes)
    const startTotalMinutes = startHour24 * 60 + startMinute;
    const endTotalMinutes = endHour24 * 60 + endMinute;
    
    console.log('Current total minutes:', currentTotalMinutes);
    console.log('Minimum booking minutes (current + 1 hour):', minimumBookingMinutes);
    console.log('Start time total minutes:', startTotalMinutes);
    console.log('End time total minutes:', endTotalMinutes);
    
    // First check: Is the selected time in the past? (basic past time validation)
    if (startTotalMinutes < currentTotalMinutes) {
      console.log('BLOCKING: Start time is in the past');
      showPastTimeWarningModal.value = true;
      return;
    }
    
    if (endTotalMinutes < currentTotalMinutes) {
      console.log('BLOCKING: End time is in the past');
      showPastTimeWarningModal.value = true;
      return;
    }
    
    // Second check: Is the selected time at least 1 hour from now?
    // If not, auto-adjust to the earliest allowed time for better UX and inform via modal.
    if (startTotalMinutes < minimumBookingMinutes || endTotalMinutes < minimumBookingMinutes) {
      console.log('ADJUSTING: Selected time is less than 1 hour from now; snapping to earliest allowed.');
      let mins = minimumBookingMinutes;
      // Round up to nearest 5 minutes
      const round = 5;
      mins = Math.ceil(mins / round) * round;
      const sHour24 = Math.floor(mins / 60) % 24;
      const sMin = mins % 60;
      const sPeriod = sHour24 >= 12 ? 'PM' : 'AM';
      let sHour12 = sHour24 % 12; if (sHour12 === 0) sHour12 = 12;

      const eMins = mins + 60; // one-hour duration default
      const eHour24 = Math.floor(eMins / 60) % 24;
      const eMin = eMins % 60;
      const ePeriod = eHour24 >= 12 ? 'PM' : 'AM';
      let eHour12 = eHour24 % 12; if (eHour12 === 0) eHour12 = 12;

      form.value.startTime = {
        hour: String(sHour12).padStart(2, '0'),
        minute: String(sMin).padStart(2, '0'),
        period: sPeriod,
      };
      form.value.endTime = {
        hour: String(eHour12).padStart(2, '0'),
        minute: String(eMin).padStart(2, '0'),
        period: ePeriod,
      };

      // Show informative warning modal using existing UI
      showOneHourWarningModal.value = true;
      return;
    }
    
    console.log('=== 1-HOUR ADVANCE TIME VALIDATION PASSED ===');
  } else {
    console.log('Date is not today, skipping 1-hour advance validation');
  }
  
  // Validate that end time is after start time
  const startHour24 = convertTo24Hour(form.value.startTime.hour, form.value.startTime.period);
  const startMinute = parseInt(form.value.startTime.minute);
  const endHour24 = convertTo24Hour(form.value.endTime.hour, form.value.endTime.period);
  const endMinute = parseInt(form.value.endTime.minute);
  
  const startTotalMinutes = startHour24 * 60 + startMinute;
  const endTotalMinutes = endHour24 * 60 + endMinute;
  
  if (endTotalMinutes <= startTotalMinutes) {
    alert('End time must be after start time.');
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
    onError: (errors: any) => {
      console.error('Booking error:', errors);
      const first = errors?.first_name || errors?.booking_date || errors?.start_time || errors?.end_time || errors?.room_id;
      alert(first ? String(first) : 'There was an error creating your booking. Please try again.');
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
    onError: (errors: any) => {
      console.error('Booking error:', errors);
      const first = errors?.first_name || errors?.booking_date || errors?.start_time || errors?.end_time || errors?.room_id;
      alert(first ? String(first) : 'There was an error creating your booking. Please try again.');
    }
  });
}

function closeLogoutModal() {
  showLogoutModal.value = false;
}

function closeOneHourWarningModal() {
  showOneHourWarningModal.value = false;
}

function closePastTimeWarningModal() {
  showPastTimeWarningModal.value = false;
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
.hamburger-btn {
  display: none;
  background: transparent;
  border: none;
  padding: 8px;
  border-radius: 6px;
  cursor: pointer;
  color: #fff;
}
.hamburger-icon {
  display: inline-block;
  width: 22px;
  height: 2px;
  background: #fff;
  position: relative;
}
.hamburger-icon::before,
.hamburger-icon::after {
  content: '';
  position: absolute;
  left: 0;
  width: 22px;
  height: 2px;
  background: #fff;
}
.hamburger-icon::before { top: -7px; }
.hamburger-icon::after { top: 7px; }
.mobile-menu {
  position: absolute;
  top: 54px;
  left: 0;
  width: 100vw;
  background: #495846;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  padding: 0.75rem 1rem 1rem 1rem;
  z-index: 150;
}
.mobile-menu .nav-link {
  display: block;
  padding: 0.75rem 0.8rem;
  border-radius: 8px;
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
.nav-link.active {
  background: #fff;
  color: #495846;
  font-weight: 600;
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
.nav { display: flex; }

/* Small screen: hide desktop nav, show hamburger */
@media (max-width: 900px) {
  .nav { display: none; }
  .hamburger-btn { display: inline-flex; align-items: center; justify-content: center; }
}
</style>
