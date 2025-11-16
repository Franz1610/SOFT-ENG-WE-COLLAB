<template>
  <div style="background: #232323; min-height: 100vh;">
    <AppHeader :user="user" active="booking" @auth="handleAuthAction" />

    <!-- Main Content -->
    <main class="main-content flex-1 flex flex-col items-center justify-start px-6 py-16">
      <div class="w-full max-w-5xl bg-white rounded-lg shadow-lg p-8">
        <div class="mb-8">
          <h1 class="text-3xl font-bold mb-2 text-neutral-900">BOOKING HISTORY/MODIFY</h1>
          <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-3">
            <p class="text-neutral-600">Any changes for the day??</p>
            <div class="flex items-center gap-3">
              <div class="flex items-center gap-2">
                <label for="categoryFilter" class="text-sm text-neutral-700">Filter by category</label>
                <select id="categoryFilter" v-model="categoryFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm min-w-[180px]">
                  <option value="All">All</option>
                  <option v-for="c in uniqueCategories" :key="c" :value="c">{{ c }}</option>
                </select>
              </div>
              <div class="flex items-center gap-2">
                <label for="roomFilter" class="text-sm text-neutral-700">Filter by room</label>
                <select id="roomFilter" v-model="roomFilter" class="border border-gray-300 rounded-md px-3 py-2 text-sm min-w-[140px]">
                  <option value="All">All</option>
                  <option v-for="r in uniqueRooms" :key="r" :value="r">{{ r }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <!-- Booking History Table -->
        <div class="w-full">
          <div class="bg-gray-200 rounded-lg overflow-hidden">
            <!-- Table Header -->
            <div class="grid grid-cols-6 gap-4 p-4 bg-gray-300 font-semibold text-gray-700 text-sm">
              <div>ID</div>
              <div>DATE</div>
              <div>CATEGORY</div>
              <div>ROOM</div>
              <div>TIME</div>
              <div class="text-center">STATUS</div>
            </div>

            <!-- Table Body -->
            <div class="divide-y divide-gray-300">
              <div 
                v-for="booking in paginatedBookings" 
                :key="booking.id"
                class="grid grid-cols-6 gap-4 p-4 text-sm text-gray-700 hover:bg-gray-100 transition-colors"
              >
                <div>
                  <button 
                    class="id-link"
                    @click="openDetailsModal(booking)"
                    :title="`View details for #${booking.id}`"
                  >
                    #{{ booking.id }}
                  </button>
                </div>
                <div>{{ booking.date }}</div>
                <div>{{ formatCategory(booking.category) }}</div>
                <div>{{ booking.room ?? '—' }}</div>
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
                      @click="rebook()"
                      class="pay-btn"
                      title="Make a new booking"
                    >Rebook</Button>
                  </template>
                  <div
                    v-else-if="booking.status && booking.status.toLowerCase() === 'paid'"
                    class="paid-status"
                  >
                    <span class="status-badge bg-green-200 text-green-900">Paid</span>
                    <Button
                      v-if="booking.receipt_available && booking.receipt"
                      variant="outline"
                      class="receipt-btn"
                      @click="openReceiptModal(booking)"
                    >
                      View Receipt
                    </Button>
                  </div>
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
        
        <DialogFooter class="receipt-footer flex gap-3 sm:justify-center">
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

    <!-- Booking Details Modal -->
    <Dialog :open="showDetailsModal" @update:open="closeDetailsModal">
      <DialogContent class="sm:max-w-md bg-white">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
            Booking Details
          </DialogTitle>
          <DialogDescription v-if="selectedBooking" class="text-center text-gray-600 mt-2">
            Booking #{{ selectedBooking.id }}
          </DialogDescription>
        </DialogHeader>
        <div v-if="selectedBooking" class="details-grid">
          <div class="label">Date</div>
          <div class="value">{{ selectedBooking.date }}</div>
          <div class="label">Category</div>
          <div class="value">{{ formatCategory(selectedBooking.category) }}</div>
          <div class="label">Room</div>
          <div class="value">{{ selectedBooking.room ?? '—' }}</div>
          <div class="label">Time</div>
          <div class="value">{{ selectedBooking.time }}</div>
          <template v-if="selectedBooking.duration_hours">
            <div class="label">Duration</div>
            <div class="value">{{ selectedBooking.duration_hours }} hour{{ selectedBooking.duration_hours > 1 ? 's' : '' }}</div>
          </template>
          <template v-if="selectedBooking.estimated_price">
            <div class="label">Price (estimated)</div>
            <div class="value">{{ formatCurrency(Number(selectedBooking.estimated_price)) }}</div>
          </template>
          <div class="label">Status</div>
          <div class="value">
            <span :class="['status-badge', getStatusClass(selectedBooking.status || '')]">
              {{ selectedBooking.status }}
            </span>
          </div>
          <template v-if="selectedBooking.decline_reason">
            <div class="label">Reason</div>
            <div class="value">{{ selectedBooking.decline_reason }}</div>
          </template>
        </div>
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button 
            variant="outline" 
            @click="closeDetailsModal"
            class="border-[#495846] text-[#495846]"
          >
            Close
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Receipt Modal -->
    <Dialog :open="showReceiptModal" @update:open="closeReceiptModal">
      <DialogContent class="sm:max-w-lg bg-white receipt-dialog">
        <DialogHeader>
          <DialogTitle class="text-center text-xl font-semibold" style="color: #495846;">
            Invoice / Receipt
          </DialogTitle>
          <DialogDescription v-if="activeReceipt" class="text-center text-gray-600 mt-2">
            {{ activeReceipt.invoice_number }} · Booking {{ activeReceipt.booking_reference }}
          </DialogDescription>
        </DialogHeader>
        <div v-if="activeReceipt" class="receipt-body">
          <div class="receipt-meta">
            <div>
              <p class="receipt-label">Customer</p>
              <p class="receipt-value">{{ activeReceipt.customer_name }}</p>
            </div>
            <div>
              <p class="receipt-label">Transaction Date</p>
              <p class="receipt-value">{{ activeReceipt.transaction_date }}</p>
            </div>
            <div>
              <p class="receipt-label">Payment Method</p>
              <p class="receipt-value">{{ activeReceipt.payment_method }}</p>
            </div>
          </div>
          <div class="receipt-split">
            <div class="receipt-amounts">
              <div class="amount-row">
                <span>Amount Due</span>
                <span>{{ formatCurrency(activeReceipt.gross_total || 0) }}</span>
              </div>
              <div class="amount-row">
                <span>Amount Paid</span>
                <span>{{ formatCurrency(activeReceipt.amount_received || 0) }}</span>
              </div>
              <div class="amount-row">
                <span>Gateway Fee</span>
                <span>{{ formatCurrency(activeReceipt.gateway_fee || 0) }}</span>
              </div>
              <div class="amount-row">
                <span>Tax Collected</span>
                <span>{{ formatCurrency(activeReceipt.tax_collected || 0) }}</span>
              </div>
              <div class="amount-row total">
                <span>Net Revenue</span>
                <span>{{ formatCurrency(activeReceipt.net_revenue || 0) }}</span>
              </div>
            </div>
            <div class="receipt-notes" v-if="receiptNotes.length">
              <p class="receipt-label mb-1">Reference Notes</p>
              <ul>
                <li v-for="note in receiptNotes" :key="note">{{ note }}</li>
              </ul>
            </div>
          </div>
          <div class="receipt-signatories">
            <div>
              <p class="receipt-label">Prepared By</p>
              <p class="receipt-value">{{ activeReceipt.prepared_by ?? '—' }}</p>
            </div>
            <div>
              <p class="receipt-label">Verified By</p>
              <p class="receipt-value">{{ activeReceipt.approved_by ?? '—' }}</p>
            </div>
          </div>
        </div>
        <DialogFooter class="flex gap-3 sm:justify-center">
          <Button
            v-if="receiptBooking"
            variant="outline"
            class="border-[#495846] text-[#495846]"
            @click="openReceiptPdf"
          >
            Download PDF
          </Button>
          <Button 
            variant="outline" 
            @click="closeReceiptModal"
            class="border-[#495846] text-[#495846]"
          >
            Close
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
                <select id="amountPaid" v-model="amountPaid" class="input" :disabled="Number(amountDue) > 0">
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
                <Button type="submit" :disabled="!canSubmitPayment" class="submit-btn text-white">{{ isSubmitting ? 'Pay…' : 'Pay' }}</Button>
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
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import AppHeader from '@/components/AppHeader.vue';
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

// Filters state
const categoryFilter = ref<string>('All');
// Room filter state and helpers
const roomFilter = ref<string>('All');
const uniqueRooms = computed<string[]>(() => {
  const vals = bookings.value
    .map(b => (b.room ? String(b.room) : null))
    .filter((v): v is string => !!v);
  // Unique
  const set = Array.from(new Set(vals));
  // Sort by numeric suffix if possible (Room 1, Room 2, ...)
  const num = (s: string) => {
    const m = /(\d+)$/.exec(s);
    return m ? parseInt(m[1], 10) : Number.MAX_SAFE_INTEGER;
  };
  return set.sort((a, b) => num(a) - num(b));
});

const filteredBookings = computed(() => {
  return bookings.value.filter(b => {
    const matchesRoom = roomFilter.value === 'All' || (b.room ? String(b.room) : '') === roomFilter.value;
    const matchesCategory = categoryFilter.value === 'All' || formatCategory(b.category) === categoryFilter.value;
    return matchesRoom && matchesCategory;
  });
});

// Parse "F j, Y" date + leading start time from range like "2:00PM-3:00PM" → timestamp
function parseStartTs(dateLabel: string, timeRange: string | undefined): number {
  const d = Date.parse(dateLabel || '');
  if (!timeRange) return isNaN(d) ? 0 : d;
  const m = /([0-9]{1,2}:[0-9]{2}\s*[AP]M)/i.exec(timeRange);
  if (!m) return isNaN(d) ? 0 : d;
  const dt = Date.parse(`${dateLabel} ${m[1].toUpperCase().replace(/\s+/g,'')}`);
  return isNaN(dt) ? (isNaN(d) ? 0 : d) : dt;
}

// Sort newest-first: by created_at when available, otherwise date+start; fallback to id desc
const sortedFilteredBookings = computed(() => {
  const list = [...filteredBookings.value];
  return list.sort((a: any, b: any) => {
    const aCreated = a.created_at ? Date.parse(a.created_at) : 0;
    const bCreated = b.created_at ? Date.parse(b.created_at) : 0;
    if (aCreated !== bCreated) return bCreated - aCreated;
    const aTs = parseStartTs(a.date, a.time);
    const bTs = parseStartTs(b.date, b.time);
    if (aTs !== bTs) return bTs - aTs;
    return (b.id ?? 0) - (a.id ?? 0);
  });
});

// Pagination state
const currentPage = ref(1);
const pageSize = 5;
const totalPages = computed(() => Math.ceil(sortedFilteredBookings.value.length / pageSize));
const paginatedBookings = computed(() =>
  sortedFilteredBookings.value.slice((currentPage.value - 1) * pageSize, currentPage.value * pageSize)
);

watch(roomFilter, () => {
  currentPage.value = 1;
});

watch(categoryFilter, () => {
  currentPage.value = 1;
});

const uniqueCategories = computed<string[]>(() => {
  const labels = bookings.value.map(b => formatCategory(b.category));
  const set = Array.from(new Set(labels));
  // Keep a friendly order if all exist
  const order = ['Phone booth rooms', 'Regular tables', 'Conference rooms'];
  return set.sort((a, b) => (order.indexOf(a) !== -1 && order.indexOf(b) !== -1)
    ? order.indexOf(a) - order.indexOf(b)
    : a.localeCompare(b));
});

// Modal state
const showLogoutModal = ref(false);
const showCancelModal = ref(false);
const bookingToCancel = ref<number | null>(null);
// Details modal state
const showDetailsModal = ref(false);
const selectedBooking = ref<any | null>(null);
// Receipt modal state
const showReceiptModal = ref(false);
const receiptBooking = ref<any | null>(null);
const activeReceipt = computed(() => receiptBooking.value?.receipt ?? null);
const receiptNotes = computed<string[]>(() => {
  if (!activeReceipt.value?.reference_notes) return [];
  return activeReceipt.value.reference_notes
    .split(/\r?\n/)
    .map((note: string) => note.trim())
    .filter(Boolean);
});
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
  const v = activeBooking.value.amount_paid ?? 0;
  return Number(v) || 0;
});
const remainingAmount = computed(() => Math.max(amountDue.value - currentPaid.value, 0));

function formatCurrency(v: number) {
  return v.toLocaleString('en-PH', { style: 'currency', currency: 'PHP' });
}

// Allowed dropdown amounts per category
type AmountOption = { label: string; value: string };
const allowedAmounts = computed<AmountOption[]>(() => {
  const cat = (activeBooking.value?.category ?? '').toString().toLowerCase();
  const due = Number(activeBooking.value?.amount_due ?? 0);
  const make = (nums: number[], suffix = ''): AmountOption[] =>
    nums.map(n => ({ label: `PHP ${n.toFixed(2)}${suffix}` + (Math.abs(n - due) < 0.001 ? ' • Due' : ''), value: String(n) }));

  // If we have an exact due, prefer to show it as the primary option
  if (due > 0) {
    return make([due]);
  }

  // Fallback presets by category (when due isn't available)
  if (cat.includes('individual') || cat.includes('indiv')) {
    return make([70, 120, 150, 200]);
  }
  if (cat.includes('common')) {
    return make([39, 99, 195, 245]);
  }
  if (cat.includes('master')) {
    // Without a computed due, provide typical choices
    return make([200, 300]);
  }
  // Fallback: no predefined options
  return [];
});

// Human-friendly category labels for Booking History table only
function formatCategory(category: unknown) {
  const c = (category ?? '').toString().toLowerCase();
  if (c.includes('common')) return 'Regular tables';
  if (c.includes('individual') || c.includes('indiv')) return 'Phone booth rooms';
  if (c.includes('master')) return 'Conference rooms';
  // Fallback to original (proper-cased if possible)
  if (typeof category === 'string' && category.length) return category;
  return '—';
}

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
  // Preselect exact due when available to avoid partial payments
  nextTick(() => {
    const dueNum = Number(amountDue.value || 0);
    amountPaid.value = dueNum > 0 ? String(dueNum) : '';
  });
}

// Open booking details modal
function openDetailsModal(booking: any) {
  selectedBooking.value = booking;
  showDetailsModal.value = true;
}
function closeDetailsModal() {
  showDetailsModal.value = false;
  selectedBooking.value = null;
}

function openReceiptModal(booking: any) {
  if (!booking?.receipt) return;
  receiptBooking.value = booking;
  showReceiptModal.value = true;
}

function closeReceiptModal() {
  showReceiptModal.value = false;
  receiptBooking.value = null;
}

function openReceiptPdf() {
  if (!receiptBooking.value) return;
  window.open(`/booking/${receiptBooking.value.id}/receipt`, '_blank');
}

// Redirect user to booking page to create a new booking after rejection
function rebook() {
  router.visit('/booking');
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
  const due = Number(amountDue.value || 0);
  if (Math.abs(amt - due) > 0.01) {
    amountHint.value = 'Partial payments are not allowed. Please pay the full amount due.';
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

const canSubmitPayment = computed(() => {
  if (isSubmitting.value) return false;
  const due = Number(amountDue.value || 0);
  const amt = parseFloat(amountPaid.value || '0');
  return due > 0 && Math.abs(amt - due) < 0.01;
});

// Navigation functions
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

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}

.paid-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.receipt-btn {
  border-color: #4b824b;
  color: #4b824b;
  padding: 0.25rem 0.75rem;
  font-size: 0.85rem;
  line-height: 1.2;
}

.receipt-btn:hover {
  background-color: #4b824b;
  color: #fff;
}

.receipt-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
  align-items: stretch;
  flex: 1 1 auto;
  min-height: 0;
  overflow-y: auto;
  padding-right: 0.25rem;
}

.receipt-body > * {
  width: 100%;
  max-width: 100%;
}

.receipt-meta {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
}

.receipt-label {
  font-size: 0.72rem;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.04em;
}

.receipt-value {
  font-size: 0.92rem;
  font-weight: 600;
  color: #111827;
  line-height: 1.25;
}

.receipt-dialog {
  border-radius: 16px;
  border: none !important;
  width: min(70vw, 390px);
  max-width: 100%;
  height: min(70vh, 520px);
  max-height: min(70vh, 520px);
  min-height: 0;
  display: flex !important;
  flex-direction: column;
  padding: 1rem !important;
  overflow: hidden;
  gap: 0 !important;
}

.receipt-dialog :deep([data-slot="dialog-header"]),
.receipt-dialog :deep([data-slot="dialog-footer"]) {
  flex-shrink: 0;
}

.receipt-dialog :deep([data-slot="dialog-header"]) {
  padding-bottom: 0.4rem;
  border-bottom: 1px solid #e5e7eb;
  margin-bottom: 0.4rem;
}

.receipt-dialog :deep([data-slot="dialog-footer"]) {
  padding-top: 0.4rem;
  border-top: 1px solid #e5e7eb;
  margin-top: 0.4rem;
}

.receipt-amounts {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.75rem 0.9rem;
  background: #f9fafb;
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  width: 100%;
  box-sizing: border-box;
}

.receipt-split {
  display: flex;
  gap: 0.75rem;
  width: 100%;
  flex-wrap: wrap;
}

.receipt-split > * {
  flex: 1 1 180px;
  min-width: 0;
}

.amount-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #1f2937;
}

.amount-row.total {
  font-size: 1.05rem;
  font-weight: 700;
  color: #0f172a;
}

.receipt-notes {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 0.75rem 0.9rem;
  background: #fff;
  width: 100%;
  box-sizing: border-box;
  max-height: 220px;
  overflow-y: auto;
}

.receipt-notes ul {
  list-style: disc;
  margin-left: 1.2rem;
  color: #374151;
  word-break: break-word;
  overflow-wrap: anywhere;
}

.receipt-signatories {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
  gap: 1rem;
}

.receipt-footer {
  margin-top: 1.25rem;
}

.receipt-dialog :deep(.dialog-footer),
.receipt-dialog > .dialog-footer {
  margin-top: 1.25rem;
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
/* ID clickable link style */
.id-link {
  color: #495846;
  font-weight: 600;
  background: transparent;
  border: none;
  padding: 0;
  cursor: pointer;
  text-decoration: underline;
}
.id-link:hover {
  opacity: 0.85;
}
/* Details modal layout */
.details-grid {
  display: grid;
  grid-template-columns: 140px 1fr;
  gap: 0.5rem 0.75rem;
  padding: 0.5rem 0.25rem 0.25rem;
}
.details-grid .label {
  color: #374151;
  font-weight: 600;
}
.details-grid .value {
  color: #111827;
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
  .grid-cols-5 {
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