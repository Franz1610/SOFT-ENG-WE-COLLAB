<template>
  <div style="background: #232323; min-height: 100vh;">
    <AppHeader :user="user" active="booking" @auth="handleAuthAction" />

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
                  placeholder="00"
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                  @input="formatTimeInput($event, 'startTime', 'hour')"
                />
                <span class="mx-1 text-lg">:</span>
                <select 
                  v-model="form.startTime.minute"
                  class="w-16 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold"
                >
                  <option value="00">00</option>
                  <option value="30">30</option>
                </select>
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

          <!-- Duration -->
          <div>
            <Label>Duration</Label>
            <select 
              v-model="form.durationHours"
              class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :disabled="isConferenceCategory"
            >
              <option v-for="h in allowedDurations" :key="h" :value="String(h)">{{ h }} hour{{ h > 1 ? 's' : '' }}</option>
            </select>
            <p class="text-xs text-neutral-500 mt-1" v-if="!isConferenceCategory">End time is computed from start time and duration.</p>
            <p class="text-xs text-green-700 mt-1" v-else>
              Conference rooms are per-hour ({{ conferencePromoTier }} pax bracket).
            </p>
            <p class="text-sm mt-2" style="color:#495846" v-if="priceBlurb">
              {{ priceBlurb }}
            </p>
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
                  placeholder="00"
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold opacity-70"
                  @input="formatTimeInput($event, 'endTime', 'hour')"
                  :readonly="true"
                  :disabled="true"
                />
                <span class="mx-1 text-lg">:</span>
                <input 
                  v-model="form.endTime.minute"
                  type="number" 
                  min="00" 
                  max="59" 
                  placeholder="00"
                  class="w-12 text-center border-0 bg-transparent focus:ring-0 text-lg font-semibold opacity-70"
                  @input="formatTimeInput($event, 'endTime', 'minute')"
                  :readonly="true"
                  :disabled="true"
                />
              </div>
              <select 
                v-model="form.endTime.period"
                class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 opacity-70"
                :disabled="true"
              >
                <option value="AM">AM</option>
                <option value="PM">PM</option>
              </select>
            </div>
          </div>

          <!-- Room selection -->
          <div>
            <Label>Room</Label>
            <div class="mt-1">
              <select 
                v-model="selectedRoomId"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                :disabled="roomLoading || availableRooms.length === 0"
              >
                <option value="" disabled>Select a room</option>
                <option v-for="r in availableRooms" :key="r.id" :value="r.id">
                  {{ r.number }}
                </option>
              </select>
              <p v-if="!canFetchRooms" class="text-xs text-neutral-500 mt-1">Select date and time first to see available rooms.</p>
              <p v-else-if="roomLoading" class="text-xs text-neutral-500 mt-1">Loading rooms…</p>
              <p v-else-if="availableRooms.length === 0" class="text-xs text-red-600 mt-1">No rooms available for this slot.</p>
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
              :disabled="!selectedRoomId"
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
            <div class="flex justify-between items-center">
              <span class="font-medium" style="color: #495846;">Duration:</span>
              <span class="font-semibold" style="color: #495846;">{{ form.durationHours }} hour{{ parseInt(form.durationHours) > 1 ? 's' : '' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium" style="color: #495846;">Room:</span>
              <span class="font-semibold" style="color: #495846;">{{ selectedRoomLabel }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="font-medium" style="color: #495846;">Price Estimate:</span>
              <span class="font-semibold" style="color: #495846;">{{ estimatedPriceDisplay }}</span>
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
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppHeader from '@/components/AppHeader.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { getDurationOptions, computePrice, formatPHP } from '@/utils/promo';
import axios from 'axios';
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
  },
  durationHours: '1' // default 1 hour
});

// Modal state
const showConfirmationModal = ref(false);
const showLogoutModal = ref(false);
const showOneHourWarningModal = ref(false);
const showPastTimeWarningModal = ref(false);

// Reactive time display properties that auto-update
const currentTimeDisplay = ref('');
const minimumTimeDisplay = ref('');
let timeUpdateInterval: number | null = null;

// Allowed durations (in hours). Sourced from promo rules per category
const allowedDurations = ref<number[]>([1]);

// Payment context (best-effort detection from Inertia props)
const paymentType = computed(() => {
  const p: any = page.props as any;
  return p?.payment_type || p?.paymentType || p?.payment_method || p?.paymentMethod || '';
});
const isOnSitePayment = computed(() => {
  const val = String(paymentType.value || '').toLowerCase();
  return val.includes('on') && val.includes('site');
});

// Booking context from previous page (category and pax)
const bookingCategory = computed(() => {
  const p: any = page.props as any;
  return p?.category || 'individual';
});
const bookingPax = computed<number>(() => {
  const p: any = page.props as any;
  const n = parseInt(String(p?.pax ?? ''), 10);
  return isNaN(n) ? 1 : n;
});
const isConferenceCategory = computed(() => bookingCategory.value === 'master');
const conferencePromoTier = computed(() => {
  if (!isConferenceCategory.value) return '';
  return bookingPax.value <= 6 ? '1–6' : '7–10';
});

// Recompute end time whenever start time or duration changes
watch([
  () => form.value.startTime.hour,
  () => form.value.startTime.minute,
  () => form.value.startTime.period,
  () => form.value.durationHours
], () => {
  if (!form.value.startTime.hour || !form.value.startTime.minute) return;
  const startHour24 = convertTo24Hour(form.value.startTime.hour, form.value.startTime.period);
  const startMinute = parseInt(form.value.startTime.minute);
  // Determine duration from selection (conference rooms are per-hour; no forced lock)
  const duration = parseInt(form.value.durationHours);
  if (isNaN(duration) || duration <= 0) return;
  const endTotalMinutes = startHour24 * 60 + startMinute + duration * 60;
  const endHour24 = Math.floor(endTotalMinutes / 60) % 24;
  const endMinute = endTotalMinutes % 60; // will match startMinute
  const endPeriod = endHour24 >= 12 ? 'PM' : 'AM';
  let endHour12 = endHour24 % 12; if (endHour12 === 0) endHour12 = 12;
  form.value.endTime = {
    hour: String(endHour12).padStart(2, '0'),
    minute: String(endMinute).padStart(2, '0'),
    period: endPeriod
  };
});

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
  const minimumMinutesRaw = (currentHour * 60 + currentMinute) + 60; // Add 1 hour
  const minimumSlotMinutes = roundUpToSlot(minimumMinutesRaw, 30); // align to 00/30 slots
  const minimumHour = Math.floor(minimumSlotMinutes / 60) % 24;
  const minimumMin = minimumSlotMinutes % 60;
  
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

    // REMOVED: Auto-filling of start and end times
    // Users must now manually select their preferred times
    
  } catch (e) {
    void e;
    // Non-fatal: ignore and let user choose manually
  }

  // Initialize allowed durations for the default start time minutes (00 or 30)
  recomputeAllowedDurations();
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

// Round arbitrary minutes to next slot boundary (slotSize minutes)
function roundUpToSlot(totalMinutes: number, slotSize: number): number {
  return Math.ceil(totalMinutes / slotSize) * slotSize;
}

// Recompute allowed durations based on start time, end-of-day constraint, and promo rules
function recomputeAllowedDurations() {
  if (!form.value.startTime.hour || !form.value.startTime.minute) {
    // Use promo defaults without time constraint when time not yet selected
    allowedDurations.value = getDurationOptions(bookingCategory.value, 8);
    return;
  }
  const startHour24 = convertTo24Hour(form.value.startTime.hour, form.value.startTime.period);
  const startMinute = parseInt(form.value.startTime.minute);
  const startTotal = startHour24 * 60 + startMinute;
  const lastEndSameMinute = 23 * 60 + startMinute; // latest end on same day keeping minute alignment
  let maxHours = Math.floor((lastEndSameMinute - startTotal) / 60);
  if (maxHours < 1) {
    allowedDurations.value = [];
    return;
  }
  const MAX_CAP = 8; // business cap; adjust as needed
  maxHours = Math.min(maxHours, MAX_CAP);
  // Pull promo-based options and then enforce time boundary
  const fromPromo = getDurationOptions(bookingCategory.value, maxHours);
  allowedDurations.value = fromPromo.filter(h => h <= maxHours);

  // Ensure selected duration is still valid
  const current = parseInt(form.value.durationHours || '1');
  if (!allowedDurations.value.includes(current)) {
    form.value.durationHours = String(allowedDurations.value[0] ?? '1');
  }
}

// Watch start time fields to recompute allowed durations
watch([
  () => form.value.startTime.hour,
  () => form.value.startTime.minute,
  () => form.value.startTime.period
], () => {
  recomputeAllowedDurations();
});

// validateTimeForToday removed — validation handled inline in submitSchedule to avoid duplication.
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
    // Per-hour timeslot rule: only 00 or 30 are valid minute values.
    if (isNaN(value) || value < 0) {
      target.value = '00';
    } else if (value < 30) {
      target.value = '00';
    } else {
      target.value = '30';
    }
    form.value[timeType].minute = target.value;
    // Keep endTime minute synced to startTime minute automatically
    if (timeType === 'startTime') {
      form.value.endTime.minute = target.value;
    }
  }
}

// Format time for display (defensive)
function formatTimeDisplay(timeObj: { hour?: string; minute?: string; period?: string }) {
  if (!timeObj?.hour || !timeObj?.minute || !timeObj?.period) {
    return '';
  }
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

  // Enforce per-hour slot rule: minutes must match and be either 00 or 30
  const startMinVal = form.value.startTime.minute;
  const endMinVal = form.value.endTime.minute;
  if (startMinVal !== endMinVal) {
    // Auto-fix by syncing end minutes to start minutes
    form.value.endTime.minute = startMinVal;
  }
  if (!(startMinVal === '00' || startMinVal === '30')) {
    // Snap invalid start minutes (should already be snapped by input) and reflect to end
    form.value.startTime.minute = '00';
    form.value.endTime.minute = '00';
  }

  // Ensure durationHours produces current endTime correctly (redundant but defensive)
  const durationH = parseInt(form.value.durationHours);
  if (isNaN(durationH) || durationH < 1) {
    alert('Invalid duration selected.');
    return;
  }
  
  // Check if selected date is in the past (handled via string compare with Manila date below)
  
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
  // Compute earliest allowed start slot: +1 hour from now, then round to next 00/30 slot
  const minimumBookingMinutesRaw = currentTotalMinutes + 60;
  const minimumBookingMinutes = roundUpToSlot(minimumBookingMinutesRaw, 30);
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

  // Duration logic: if on-site payment restrict to max 1 hour per day
  const durationHours = isOnSitePayment.value ? 1 : parseInt(form.value.durationHours || '1');
  const eMins = mins + durationHours * 60; // duration-based end
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

      // Adjust durationHours field to reflect enforced duration
      form.value.durationHours = String(isOnSitePayment.value ? 1 : durationHours);

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

  // Per-hour duration enforcement: duration must be a whole number of hours (multiple of 60)
  const duration = endTotalMinutes - startTotalMinutes;
  if (duration < 60 || duration % 60 !== 0) {
    // Auto-adjust to 1-hour slot (minimum) keeping minute component
  const enforcedHours = isOnSitePayment.value ? 1 : 1; // minimum is still 1 hour either way
  const adjustedEndTotal = startTotalMinutes + enforcedHours * 60;
    const adjHour24 = Math.floor(adjustedEndTotal / 60) % 24;
    const adjMinute = adjustedEndTotal % 60; // will match start minute
    const adjPeriod = adjHour24 >= 12 ? 'PM' : 'AM';
    let adjHour12 = adjHour24 % 12; if (adjHour12 === 0) adjHour12 = 12;
    form.value.endTime = {
      hour: String(adjHour12).padStart(2, '0'),
      minute: String(adjMinute).padStart(2, '0'),
      period: adjPeriod
    };
    form.value.durationHours = String(enforcedHours);
    // Inform user via modal rather than alert (reuse one-hour warning modal)
    showOneHourWarningModal.value = true;
    return; // Let user confirm again after adjustment
  }

  // Final sanity check: end time should equal start time + durationHours * 60
  const expectedEndTotal = startTotalMinutes + (isOnSitePayment.value ? 1 : durationH) * 60;
  if (endTotalMinutes !== expectedEndTotal) {
    const endHour24Fix = Math.floor(expectedEndTotal / 60) % 24;
    const endMinuteFix = expectedEndTotal % 60;
    const endPeriodFix = endHour24Fix >= 12 ? 'PM' : 'AM';
    let endHour12Fix = endHour24Fix % 12; if (endHour12Fix === 0) endHour12Fix = 12;
    form.value.endTime = {
      hour: String(endHour12Fix).padStart(2, '0'),
      minute: String(endMinuteFix).padStart(2, '0'),
      period: endPeriodFix
    };
  }
  
  // Show confirmation modal instead of alert
  showConfirmationModal.value = true;
}

function confirmBooking() {
  // Get booking data from props (passed from session)
  const bookingData = page.props as any;
  if (!selectedRoomId.value) {
    alert('Please select a room for your chosen time slot.');
    return;
  }
  
  // Prepare booking data
  const submitData = {
    first_name: bookingData.firstName || '',
    last_name: bookingData.lastName || '',
    contact: bookingData.contact || '',
    email: bookingData.email || '',
    additional_info: bookingData.additional || '',
    pax: bookingData.pax || 1,
    category: bookingData.category || 'individual',
    room_id: Number(selectedRoomId.value),
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
  if (!selectedRoomId.value) {
    alert('Please select a room for your chosen time slot.');
    return;
  }
  
  // Prepare booking data
  const submitData = {
    first_name: bookingData.firstName || '',
    last_name: bookingData.lastName || '',
    contact: bookingData.contact || '',
    email: bookingData.email || '',
    additional_info: bookingData.additional || '',
    pax: bookingData.pax || 1,
    category: bookingData.category || 'individual',
    room_id: Number(selectedRoomId.value),
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

// Pricing blurb displayed under Duration, based on current selection and promo
const priceBlurb = computed(() => {
  const dur = parseInt(form.value.durationHours || '0');
  if (!dur) return '';
  const cat = bookingCategory.value;
  if (cat === 'master') {
    const perHour = bookingPax.value <= 6 ? 200 : 300;
    const est = computePrice(cat, dur, bookingPax.value);
    return `Rate: ${formatPHP(perHour)} / hr • Estimated: ${formatPHP(est)}`;
  }
  const price = computePrice(cat, dur, bookingPax.value);
  return price ? `Promo price: ${formatPHP(price)}` : '';
});

// Price estimate for the confirmation modal
const estimatedPrice = computed(() => {
  const dur = parseInt(form.value.durationHours || '0');
  if (!dur) return null;
  return computePrice(bookingCategory.value, dur, bookingPax.value);
});
const estimatedPriceDisplay = computed(() => {
  const val = estimatedPrice.value;
  return val == null ? '—' : formatPHP(val);
});

// -------- Room availability & selection --------
type AvailRoom = { id: number; number: string; actual: string };
const availableRooms = ref<AvailRoom[]>([]);
const roomLoading = ref(false);
const selectedRoomId = ref<number | null>(null);

const canFetchRooms = computed(() => {
  return Boolean(
    form.value.date &&
    form.value.startTime.hour && form.value.startTime.minute && form.value.startTime.period &&
    form.value.endTime.hour && form.value.endTime.minute && form.value.endTime.period
  );
});

const selectedRoomLabel = computed(() => {
  const r = availableRooms.value.find(x => x.id === selectedRoomId.value);
  return r ? r.number : '—';
});

async function fetchAvailableRooms() {
  if (!canFetchRooms.value) {
    availableRooms.value = [];
    selectedRoomId.value = null;
    return;
  }
  roomLoading.value = true;
  try {
    const resp = await axios.get('/rooms/available', {
      params: {
        category: bookingCategory.value,
        date: form.value.date,
        start_time: formatTimeDisplay(form.value.startTime),
        end_time: formatTimeDisplay(form.value.endTime),
      }
    });
    const rooms = Array.isArray(resp.data?.rooms) ? resp.data.rooms as AvailRoom[] : [];
    availableRooms.value = rooms;
    if (!rooms.find(r => r.id === selectedRoomId.value)) {
      selectedRoomId.value = null;
    }
  } catch (e) {
    console.error('Failed to load rooms', e);
    availableRooms.value = [];
    selectedRoomId.value = null;
  } finally {
    roomLoading.value = false;
  }
}

// Refetch when date/time/duration changes
watch([
  () => form.value.date,
  () => form.value.startTime.hour,
  () => form.value.startTime.minute,
  () => form.value.startTime.period,
  () => form.value.durationHours,
], () => {
  // endTime already syncs from start + duration via existing watcher
  fetchAvailableRooms();
});
</script>

<style scoped>
.header {
  background: #495846;
  color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 100;
  box-shadow: none;
}
.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.5rem 2rem;
  min-height: 54px;
  width: 100%;
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
  width: 100%;
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
