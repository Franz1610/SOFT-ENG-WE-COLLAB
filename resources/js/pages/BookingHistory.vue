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
                    v-if="booking.status && !['pending payment','paid','rejected'].includes(booking.status.toLowerCase())"
                    :class="getStatusClass(booking.status)"
                    class="status-badge"
                  >
                    {{ booking.status }}
                  </span>
                  <button 
                    v-if="booking.can_cancel"
                    @click="cancelBooking(booking.id)" 
                    class="cancel-btn"
                    title="Cancel Booking"
                  >
                    Cancel
                  </button>
                  <!-- If booking is done (admin accepted), show Pay button instead of plain text -->
                  <Button
                    v-else-if="booking.status && booking.status.toLowerCase() === 'done'"
                    @click="openPayModal(booking.id)"
                    class="pay-btn"
                    title="Pay for Booking"
                  >
                    Pay
                  </Button>
                  <span v-else-if="booking.status && booking.status.toLowerCase() === 'pending payment'" class="status-badge bg-yellow-100 text-yellow-800">Pending Payment</span>
                  <template v-else-if="booking.status && booking.status.toLowerCase() === 'rejected'">
                    <span class="status-badge bg-red-200 text-red-800 mr-2">Rejected</span>
                    <Button
                      @click="openPayModal(booking.id)"
                      class="pay-btn"
                      title="Resubmit Payment"
                    >Resubmit</Button>
                  </template>
                  <span v-else-if="booking.status && booking.status.toLowerCase() === 'paid'" class="status-badge bg-green-200 text-green-900">Paid</span>
                  <span v-else class="cannot-cancel-text">
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

    <!-- Pay Modal (QR + payment form) -->
    <Dialog :open="showPayModal" @update:open="closePayModal">
      <DialogContent class="sm:max-w-4xl max-h-[90vh] overflow-y-auto bg-white pay-dialog">
        <div class="pay-modal-content">
          <div class="pay-left">
            <h2 class="pay-heading">Pay Booking <span v-if="payBookingId">#{{ payBookingId }}</span></h2>
            <p class="pay-sub">Scan this QR with GCash:</p>
            <div class="qr-card" aria-label="GCash QR Code">
              <img src="/images/booking/gcash_qr.jpg" alt="GCash QR Code" class="qr-img" />
            </div>
          </div>
          <div class="pay-right">
            <form @submit.prevent="submitPayment" class="pay-form" novalidate>
              <div class="form-group">
                <label for="amountPaid" class="form-label">Amount Paid (PHP)</label>
                <select id="amountPaid" v-model="amountPaid" class="input">
                  <option disabled value="">Select amount</option>
                  <option v-for="opt in allowedAmounts" :key="opt.value" :value="opt.value">
                    {{ opt.label }}
                  </option>
                </select>
                <div class="amount-info" v-if="activeBooking">
                  Amount due: {{ formatCurrency(amountDue) }} • Paid: {{ formatCurrency(currentPaid) }} • Remaining: {{ formatCurrency(remainingAmount) }}
                </div>
              </div>
              <div class="form-group">
                <label for="referenceNo" class="form-label">Reference No.</label>
                <input id="referenceNo" v-model="referenceNo" type="text" class="input" placeholder="GCash reference" />
              </div>
              <div class="form-group">
                <label for="proof" class="form-label">Upload Proof (Screenshot)</label>
                <input id="proof" type="file" accept="image/*" class="file-input" @change="onFileChange" />
                <div v-if="proofFile" class="file-meta">Selected: {{ proofFile.name }}</div>
              </div>
              <p v-if="amountHint" class="hint" aria-live="polite">{{ amountHint }}</p>
              <div class="actions">
                <Button type="submit" :disabled="isSubmitting" class="submit-btn text-white">{{ isSubmitting ? 'Pay…' : 'Pay' }}</Button>
                <Button type="button" variant="outline" class="cancel-btn-alt" @click="closePayModal">Cancel</Button>
              </div>
              <!-- Promo image sits below the two buttons -->
              <div class="promo-wrap" aria-label="Promo Rates">
                <img src="/images/booking/promo_rates.jpg" alt="WeCollab Promo Rates" class="promo-img" />
              </div>
            </form>
          </div>
        </div>
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
// Mobile menu state
const menuOpen = ref(false);
const showCancelModal = ref(false);
const bookingToCancel = ref<number | null>(null);
// Pay modal state
const showPayModal = ref(false);
const payBookingId = ref<number | null>(null);
// Pay form state
const amountPaid = ref<string>('');
const referenceNo = ref<string>('');
const proofFile = ref<File | null>(null);
const isSubmitting = ref(false);
const amountHint = ref<string>('');

// Derive active booking and payment figures
const activeBooking = computed(() => bookings.value.find(b => b.id === payBookingId.value) || null);
// Fallbacks if fields not present in booking object
const amountDue = computed(() => {
  if (!activeBooking.value) return 0;
  return activeBooking.value.amount_due ?? activeBooking.value.total ?? 0;
});
const currentPaid = computed(() => {
  if (!activeBooking.value) return 0;
  return activeBooking.value.amount_paid ?? activeBooking.value.paid ?? 0;
});
const remainingAmount = computed(() => Math.max(amountDue.value - currentPaid.value, 0));

function formatCurrency(v: number) {
  return v.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
}

// Allowed dropdown amounts per category
type AmountOption = { label: string; value: string };
const allowedAmounts = computed<AmountOption[]>(() => {
  const cat = (activeBooking.value?.category ?? '').toString().toLowerCase();
  const make = (nums: number[], suffix = ''): AmountOption[] =>
    nums.map(n => ({ label: `PHP ${n.toFixed(2)}${suffix}`, value: String(n) }));

  if (cat.includes('individual') || cat.includes('indiv')) {
    return make([39, 99, 195, 245]);
  }
  if (cat.includes('common')) {
    return make([70, 120, 150, 200]);
  }
  if (cat.includes('master')) {
    return make([200, 300], '/hr');
  }
  // Fallback: no predefined options
  return [];
});

// Get status styling class
function getStatusClass(status: string) {
  switch (status.toLowerCase()) {
    case 'paid':
      return 'bg-green-200 text-green-900';
    case 'pending payment':
      return 'bg-yellow-100 text-yellow-800';
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
        alert('❌ Unable to cancel booking. This booking may no longer be eligible for cancellation or there was a network error. Please refresh the page and try again.');
        showCancelModal.value = false;
        bookingToCancel.value = null;
      }
    });
  }
}

// Handle pay action for bookings that are done
// Open the pay confirmation modal
function openPayModal(bookingId: number) {
  if (!bookingId) return;
  payBookingId.value = bookingId;
  showPayModal.value = true;
}

function closePayModal() {
  showPayModal.value = false;
  payBookingId.value = null;
  amountPaid.value = '';
  referenceNo.value = '';
  proofFile.value = null;
  isSubmitting.value = false;
  amountHint.value = '';
}

// Confirm and navigate to payment route
// Deprecated legacy redirect (no longer used) removed to satisfy linter

function onFileChange(e: Event) {
  const target = e.target as HTMLInputElement;
  const files = target.files;
  proofFile.value = files && files[0] ? files[0] : null;
}

async function submitPayment() {
  if (!payBookingId.value) return;
  const amt = parseFloat(amountPaid.value || '');
  if (!amountPaid.value) {
    amountHint.value = 'Please select an amount.';
    return;
  }
  if (!(amt > 0)) {
    amountHint.value = 'Invalid amount selected.';
    return;
  }
  if (!referenceNo.value.trim()) {
    amountHint.value = 'Please provide the GCash reference number.';
    return;
  }
  amountHint.value = '';
  try {
    isSubmitting.value = true;
    const data = new FormData();
    data.append('amount_paid', String(amt));
    data.append('reference_no', referenceNo.value.trim());
    if (proofFile.value) data.append('proof', proofFile.value);
    await router.post(`/booking/${payBookingId.value}/pay`, data, {
      forceFormData: true,
      preserveScroll: true,
      onSuccess: () => {
        // Optimistic local update: mark booking as Pending Payment
        const idx = bookings.value.findIndex(b => b.id === payBookingId.value);
        if (idx !== -1) {
          bookings.value[idx].status = 'Pending Payment';
        }
        isSubmitting.value = false; closePayModal();
      },
      onError: () => { isSubmitting.value = false; }
    } as any);
  } catch (err) {
    console.error('Payment submission error', err);
    isSubmitting.value = false;
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
  background: #fff1f2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 0.25em 0.75em;
  margin-left: 0.5rem;
  font-size: 0.8em;
  font-weight: 500;
  cursor: pointer;
  border-radius: 6px;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 50px;
  height: 26px;
}
.cancel-btn:hover {
  background: #fee2e2;
  border-color: #fca5a5;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);
}
.cannot-cancel-text {
  color: #9ca3af;
  font-size: 0.8em;
  font-weight: 500;
  margin-left: 0.5rem;
  padding: 0.25em 0.75em;
  height: 26px;
  min-width: 50px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
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
  .nav { display: none; }
  .hamburger-btn { display: inline-flex; align-items: center; justify-content: center; }
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

/* Pay modal specific styles: widen without increasing length */
/* Hide the default close (X) button for the Pay dialog only.
   We target multiple possible class combinations to be resilient to library updates. */
:deep(.pay-dialog .absolute.right-4.top-4),
:deep(.pay-dialog button.absolute.right-4.top-4),
:deep(.pay-dialog [class*="absolute"][class*="right-4"][class*="top-4"]) {
  display: none !important;
  pointer-events: none !important;
}
/* Pay modal layout */
.pay-modal-content {
  display: grid;
  grid-template-columns: 1.1fr 0.9fr;
  gap: 1.25rem;
}
.pay-left { display: flex; flex-direction: column; gap: 0.5rem; }
.pay-right { display: flex; }
.pay-form { display: flex; flex-direction: column; gap: 0.9rem; width: 100%; }
.pay-heading { font-size: 1.25rem; font-weight: 700; color: #111827; }
.pay-sub { font-size: 0.95rem; color: #4b5563; }
.qr-card {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.75rem;
  background: #fafafa;
  display: flex; align-items: center; justify-content: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.qr-card .qr-img { max-height: 52vh; width: auto; border-radius: 8px; object-fit: contain; }
.form-group { display: flex; flex-direction: column; gap: 0.4rem; }
.form-label { font-size: 0.9rem; color: #374151; }
.input { width: 100%; border: 1px solid #d1d5db; border-radius: 8px; padding: 0.55rem 0.75rem; font-size: 0.95rem; }
.file-input { width: 100%; }
.file-meta { font-size: 0.8rem; color: #6b7280; }
.amount-info { font-size: 0.85rem; color: #6b7280; }
.hint { font-size: 0.85rem; color: #ef4444; }
.actions { display: flex; gap: 0.6rem; margin-top: 0.25rem; }
.submit-btn { background: #111827 !important; }
.cancel-btn-alt { color: #111827; border-color: #111827; }
.promo-wrap { margin-top: 0.75rem; }
.promo-img {
  width: 100%;
  height: auto;
  max-height: 32vh;
  object-fit: contain;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  background: #fff;
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
@media (max-width: 768px) {
  .pay-modal-content { grid-template-columns: 1fr; }
  .qr-card .qr-img { max-height: 40vh; width: 100%; }
  .promo-img { max-height: 25vh; }
}
/* Ensure dialog content can scroll vertically when content exceeds viewport */
.pay-dialog {
  overflow-y: auto;
}
</style>