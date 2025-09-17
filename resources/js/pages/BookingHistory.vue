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
    <main class="main-content flex-1 flex flex-col items-center justify-start px-6 py-16">
      <div class="w-full max-w-5xl bg-white rounded-lg shadow-lg p-8">
        <div class="mb-8">
          <h1 class="text-3xl font-bold mb-2 text-neutral-900">BOOKING HISTORY/MODIFY</h1>
          <p class="text-neutral-600">Any changes for the day??</p>
        </div>

        <!-- Booking History Table -->
        <div class="w-full">
          <div class="bg-gray-200 rounded-lg overflow-hidden">
            <!-- Table Header -->
            <div class="grid grid-cols-4 gap-4 p-4 bg-gray-300 font-semibold text-gray-700 text-sm">
              <div>DATE</div>
              <div>CATEGORY</div>
              <div>TIME</div>
              <div>STATUS</div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-gray-300">
              <div 
                v-for="booking in bookings" 
                :key="booking.id"
                class="grid grid-cols-4 gap-4 p-4 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
              >
                <div>{{ booking.date }}</div>
                <div>{{ booking.category }}</div>
                <div>{{ booking.time }}</div>
                <div class="flex items-center justify-between">
                  <span 
                    :class="getStatusClass(booking.status)"
                    class="px-2 py-1 rounded text-xs font-medium"
                  >
                    {{ booking.status }}
                  </span>
                  <button
                    v-if="booking.can_cancel"
                    @click="cancelBooking(booking.id)"
                    class="ml-3 px-3 py-1 bg-red-500 text-white text-xs rounded hover:bg-red-600 transition-colors"
                  >
                    Cancel
                  </button>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="bookings.length === 0" class="p-8 text-center text-gray-500">
              <p class="text-lg">No booking history found</p>
              <p class="text-sm mt-2">Make your first booking to see it here!</p>
              <Button 
                @click="goToBooking"
                class="mt-4 bg-[#495846] hover:bg-[#38613a] text-white border-none"
              >
                Make a Booking
              </Button>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-center">
          <Button 
            @click="goToBooking"
            variant="outline"
            class="border-[#495846] text-[#495846] hover:bg-[#495846] hover:text-white"
          >
            Make New Booking
          </Button>
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

    <!-- Cancel Booking Confirmation Modal -->
    <Dialog :open="showCancelModal" @update:open="closeCancelModal">
      <DialogContent class="sm:max-w-md bg-white">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
            Cancel Booking
          </DialogTitle>
          <DialogDescription class="text-center text-gray-600 mt-2">
            Are you sure you want to cancel this booking? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>
        
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button 
            variant="outline" 
            @click="closeCancelModal"
            class="flex-1"
            style="border-color: #495846; color: #495846;"
          >
            Keep Booking
          </Button>
          <Button 
            @click="confirmCancelBooking"
            class="flex-1 text-white border-none bg-red-500 hover:bg-red-600"
          >
            Cancel Booking
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

// Get authentication data and bookings from Inertia
const page = usePage();
const user = computed(() => page.props.auth.user);

// Get bookings from props (passed from backend)
const bookings = ref((page.props.bookings as any[]) || []);

// Modal state
const showLogoutModal = ref(false);
const showCancelModal = ref(false);
const bookingToCancel = ref<number | null>(null);

// Format date for display (bookings now come pre-formatted from backend)
function formatDate(dateString: string) {
  // If already formatted, return as is
  if (dateString.includes(',')) {
    return dateString;
  }
  // Otherwise format it
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  });
}

// Get status styling class
function getStatusClass(status: string) {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'cancelled':
      return 'bg-red-100 text-red-800';
    case 'done':
      return 'bg-green-100 text-green-800';
    default:
      return 'bg-gray-100 text-gray-800';
  }
}

// Cancel booking function
function cancelBooking(bookingId: number) {
  bookingToCancel.value = bookingId;
  showCancelModal.value = true;
}

function closeCancelModal() {
  showCancelModal.value = false;
  bookingToCancel.value = null;
}

function confirmCancelBooking() {
  if (bookingToCancel.value) {
    router.post(`/booking/${bookingToCancel.value}/cancel`, {}, {
      onSuccess: () => {
        // Update the booking status locally without page refresh
        const bookingIndex = bookings.value.findIndex(b => b.id === bookingToCancel.value);
        if (bookingIndex !== -1) {
          bookings.value[bookingIndex].status = 'Cancelled';
          bookings.value[bookingIndex].can_cancel = false;
        }
        
        showCancelModal.value = false;
        bookingToCancel.value = null;
      },
      onError: (error) => {
        console.error('Cancel error:', error);
        alert('There was an error cancelling the booking. Please try again.');
        showCancelModal.value = false;
        bookingToCancel.value = null;
      }
    });
  }
}

// Navigation functions
function goHome() {
  router.visit('/');
}

function goToBooking() {
  router.visit('/booking');
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

// Load booking history on component mount
onMounted(() => {
  // In the future, this will fetch actual booking data from the backend
  // based on the authenticated user's account
});
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

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}

.logout-btn:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}

/* Responsive design */
@media (max-width: 768px) {
  .grid-cols-4 {
    grid-template-columns: 1fr 1fr;
    gap: 2;
  }
  
  .grid-cols-4 > div:nth-child(3),
  .grid-cols-4 > div:nth-child(4) {
    grid-column: span 1;
  }
}

@media (max-width: 480px) {
  .header-inner {
    padding: 0.5rem 1rem;
  }
  
  .nav {
    gap: 0.5rem;
  }
  
  .nav-link {
    font-size: 0.875rem;
    padding: 0.25rem 0.5rem;
  }
  
  .home-btn {
    font-size: 0.875rem;
    padding: 0.25rem 0.75rem;
  }
}
</style>