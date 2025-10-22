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
          <a href="/whats-new" class="nav-link">What's NEW?</a>
          <span class="nav-link active">Booking</span>
          <Link href="/" class="nav-link">HOME</Link>
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
              <div class="text-center">STATUS</div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-gray-300">
              <div 
                v-for="booking in paginatedBookings" 
                :key="booking.id"
                class="grid grid-cols-4 gap-4 p-4 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
              >
                <div>{{ booking.date }}</div>
                <div>{{ booking.category }}</div>
                <div>{{ booking.time }}</div>
                <div class="status-cell">
                  <span 
                    :class="getStatusClass(booking.status)"
                    class="status-badge"
                  >
                    {{ booking.status }}
                  </span>
                  <button
                    v-if="booking.can_cancel"
                    @click="cancelBooking(booking.id)"
                    class="cancel-btn"
                  >
                    <span class="cancel-icon">&#128465;</span>
                  </button>
                  <span v-else class="text-gray-400 text-xs ml-2">
                    Cannot cancel
                  </span>
                </div>
              </div>
            </div>

            <!-- Empty State -->
            <div v-if="bookings.length === 0" class="p-8 text-center text-gray-500">
              <p class="text-lg">No booking history found</p>
              <p class="text-sm mt-2">Make your first booking to see it here!</p>
            </div>
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
import { router, usePage, Link } from '@inertiajs/vue3';
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

// Pagination state
const currentPage = ref(1);
const pageSize = 5;
const totalPages = computed(() => Math.ceil(bookings.value.length / pageSize));
const paginatedBookings = computed(() =>
  bookings.value.slice((currentPage.value - 1) * pageSize, currentPage.value * pageSize)
);

// Modal state
const showLogoutModal = ref(false);
const showCancelModal = ref(false);
const bookingToCancel = ref<number | null>(null);

// Get status styling class
function getStatusClass(status: string) {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800';
    case 'cancelled':
      return 'bg-red-100 text-red-800';
    case 'rejected':
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

// Reset to first page if bookings change
onMounted(() => {
  currentPage.value = 1;
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

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}

.logout-btn:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}

.status-cell {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5em;
  min-height: 38px;
}
.status-badge {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 80px;
  padding: 0.3em 0.8em;
  font-size: 0.95em;
  border-radius: 12px;
  font-weight: 500;
  text-align: center;
}
.cancel-btn {
  background: none;
  border: none;
  padding: 0.2em 0.5em;
  margin-left: 0.2em;
  font-size: 1em;
  cursor: pointer;
  border-radius: 6px;
  transition: background 0.2s;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}
.cancel-btn:hover .cancel-icon {
  color: #dc2626;
}
.cancel-icon {
  color: #495846;
  font-size: 1.1em;
  transition: color 0.2s;
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
/* Responsive design */
@media (max-width: 900px) {
  .main-content {
    padding: 1em 0.5em;
  }
  .grid-cols-4 {
    grid-template-columns: 1fr 1fr;
    gap: 2;
  }
  .status-badge {
    min-width: 60px;
    font-size: 0.9em;
    padding: 0.2em 0.5em;
  }
}
@media (max-width: 600px) {
  .main-content {
    padding: 0.5em 0.2em;
  }
  .status-cell {
    flex-direction: column;
    gap: 0.2em;
  }
  .status-badge {
    min-width: 48px;
    font-size: 0.85em;
    padding: 0.15em 0.3em;
  }
}
</style>