<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';

interface PaymentEntry {
  id: number;
  booking_id: number;
  customer: string;
  email?: string | null;
  date?: string | null;
  amount_received: number;
  gross_total: number;
  reference: string;
  status: 'Unprocessed' | 'Pending Review' | 'Verified';
  proof_url?: string | null;
  created_at: string;
}

const props = defineProps<{ payments: PaymentEntry[] }>();
const search = ref('');
const filter = ref<'all'|'pending'|'verified'|'declined'>('pending');

const rows = computed(() => {
  let list = props.payments || [];
  if (filter.value !== 'all') {
    // Map UI filter values to backend status strings
    const map: Record<string,string> = { pending: 'Pending Review', verified: 'Verified', declined: 'Unprocessed' };
    list = list.filter(p => p.status === map[filter.value]);
  }
  if (search.value.trim()) {
    const q = search.value.toLowerCase();
    list = list.filter(p =>
      String(p.booking_id).includes(q) ||
      (p.customer||'').toLowerCase().includes(q) ||
      (p.email||'').toLowerCase().includes(q)
    );
  }
  return list;
});

function accept(id: number) {
  router.post(`/admin/payments/${id}/accept`);
}
function decline(id: number) {
  const reason = prompt('Decline reason (optional)') || '';
  router.post(`/admin/payments/${id}/decline`, { reason });
}

// Proof preview modal state and handlers
const showProof = ref(false);
const proofUrl = ref<string | null>(null);
const proofTitle = ref<string>('Payment Proof');

function openProofById(id: number, title?: string) {
  const url = `/admin/payments/${id}/proof`;
  proofUrl.value = url;
  proofTitle.value = title || 'Payment Proof';
  showProof.value = true;
}
function closeProof() {
  showProof.value = false;
  proofUrl.value = null;
}
</script>

<template>
  <Head title="Manage Payments" />
  <AppLayout>
    <div class="manage-payments-container">
      <div class="page-header">
        <h1 class="page-title">Manage Payments</h1>
        <p class="page-subtitle">Review and process payment submissions</p>
      </div>
      
      <div class="filters-section">
        <input 
          v-model="search" 
          placeholder="Search by booking, name, email" 
          class="search-input"
        />
        <select 
          v-model="filter" 
          class="filter-select"
        >
          <option value="pending">Pending</option>
          <option value="verified">Verified</option>
          <option value="declined">Declined</option>
          <option value="all">All</option>
        </select>
      </div>

      <div class="table-container">
        <table class="payments-table">
          <thead>
            <tr class="table-header">
              <th class="table-th">Booking</th>
              <th class="table-th">Customer</th>
              <th class="table-th">Amount</th>
              <th class="table-th">Status</th>
              <th class="table-th">Proof</th>
              <th class="table-th">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="p in rows" :key="p.id" class="table-row">
              <td class="table-td">
                <div class="booking-info">
                  <span class="booking-id">#{{ p.booking_id }}</span>
                  <div class="booking-date">{{ p.date }}</div>
                </div>
              </td>
              <td class="table-td">
                <div class="customer-info">
                  <div class="customer-name">{{ p.customer }}</div>
                  <div class="customer-email">{{ p.email }}</div>
                </div>
              </td>
              <td class="table-td">
                <span class="amount">₱{{ p.amount_received.toFixed(2) }}</span>
              </td>
              <td class="table-td">
                <div class="status-container">
                  <span :class="{
                    'status-badge status-pending': p.status==='Pending Review',
                    'status-badge status-verified': p.status==='Verified',
                    'status-badge status-declined': p.status==='Unprocessed'
                  }">{{ p.status==='Unprocessed' ? 'Declined' : p.status }}</span>
                  <span v-if="p.status==='Unprocessed' && (p as any).decline_reason" class="decline-reason">
                    Declined: {{ (p as any).decline_reason }}
                  </span>
                </div>
              </td>
              <td class="table-td">
                <button
                  v-if="p.proof_url"
                  type="button"
                  class="proof-link"
                  @click="openProofById(p.id, `Booking #${p.booking_id} - Proof`)"
                >view</button>
                <span v-else class="no-proof">—</span>
              </td>
              <td class="table-td">
                <div class="action-buttons">
                  <Button 
                    variant="outline"
                    class="accept-btn"
                    @click="accept(p.id)" 
                    :disabled="p.status==='Verified'"
                  >Accept</Button>
                  <Button 
                    variant="outline"
                    class="decline-btn"
                    @click="decline(p.id)"
                  >Decline</Button>
                </div>
              </td>
            </tr>
            <tr v-if="rows.length===0">
              <td colspan="6" class="no-data">No payments to show</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>

  <!-- Proof Preview Modal -->
  <Dialog :open="showProof" @update:open="closeProof">
    <DialogContent class="proof-modal">
      <DialogHeader>
        <DialogTitle class="modal-title">{{ proofTitle }}</DialogTitle>
        <DialogDescription class="modal-description">Click the image to open in a new tab.</DialogDescription>
      </DialogHeader>
      <div v-if="proofUrl" class="proof-content">
        <a :href="proofUrl" target="_blank" rel="noopener">
          <img :src="proofUrl" alt="Payment Proof" class="proof-image" />
        </a>
      </div>
      <DialogFooter class="modal-footer">
        <Button class="close-btn" @click="closeProof">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
.manage-payments-container {
  background: #FFFAE9;
  min-height: 100vh;
  padding: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-size: 2rem;
  font-weight: bold;
  color: #4b824b;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  color: #6b956b;
  font-size: 1rem;
}

.filters-section {
  display: flex;
  gap: 1rem;
  margin-bottom: 2rem;
  align-items: center;
}

.search-input, .filter-select {
  padding: 0.75rem 1rem;
  border: 2px solid #4b824b;
  border-radius: 8px;
  background: #FFFAE9;
  color: #4b824b;
  font-size: 1rem;
  transition: all 0.2s ease;
}

.search-input {
  width: 320px;
}

.filter-select {
  min-width: 140px;
}

.search-input:focus, .filter-select:focus {
  outline: none;
  border-color: #3d6b3d;
  box-shadow: 0 0 0 3px rgba(75, 130, 75, 0.1);
}

.table-container {
  background: white;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(75, 130, 75, 0.1);
  border: 2px solid #4b824b;
}

.payments-table {
  width: 100%;
  border-collapse: collapse;
}

.table-header {
  background: #4b824b;
  color: #FFFAE9;
}

.table-th {
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.table-row {
  border-bottom: 1px solid #e5e7eb;
  transition: background-color 0.2s ease;
}

.table-row:hover {
  background: #f8f9fa;
}

.table-td {
  padding: 1rem;
  vertical-align: top;
}

.booking-info .booking-id {
  font-weight: 600;
  color: #4b824b;
}

.booking-date, .customer-email {
  font-size: 0.875rem;
  color: #6b956b;
  margin-top: 0.25rem;
}

.customer-name {
  font-weight: 500;
  color: #4b824b;
}

.amount {
  font-weight: 600;
  color: #4b824b;
  font-size: 1.125rem;
}

.status-container {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.status-badge {
  display: inline-block;
  padding: 0.5rem 0.75rem;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  width: fit-content;
}

.status-pending {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #f59e0b;
}

.status-verified {
  background: #d1fae5;
  color: #065f46;
  border: 1px solid #10b981;
}

.status-declined {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #ef4444;
}

.decline-reason {
  font-size: 0.75rem;
  color: #dc2626;
  font-style: italic;
}

.proof-link {
  color: #4b824b;
  text-decoration: underline;
  background: none;
  border: none;
  cursor: pointer;
  font-weight: 500;
  transition: color 0.2s ease;
}

.proof-link:hover {
  color: #3d6b3d;
}

.no-proof {
  color: #9ca3af;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.accept-btn {
  background: #4b824b !important;
  color: #FFFAE9 !important;
  border: 2px solid #4b824b !important;
  padding: 0.5rem 1rem !important;
  border-radius: 6px !important;
  font-weight: 500 !important;
  transition: all 0.2s ease !important;
}

.accept-btn:hover:not(:disabled) {
  background: #3d6b3d !important;
  border-color: #3d6b3d !important;
}

.accept-btn:disabled {
  background: #9ca3af !important;
  border-color: #9ca3af !important;
  cursor: not-allowed !important;
}

.decline-btn {
  background: transparent !important;
  color: #dc2626 !important;
  border: 2px solid #dc2626 !important;
  padding: 0.5rem 1rem !important;
  border-radius: 6px !important;
  font-weight: 500 !important;
  transition: all 0.2s ease !important;
}

.decline-btn:hover {
  background: #dc2626 !important;
  color: white !important;
}

.no-data {
  text-align: center;
  padding: 3rem;
  color: #6b956b;
  font-style: italic;
}

/* Modal styles */
.proof-modal {
  background: #FFFAE9;
  border: 2px solid #4b824b;
}

.modal-title {
  color: #4b824b;
  font-weight: 600;
}

.modal-description {
  color: #6b956b;
}

.proof-content {
  max-height: 70vh;
  overflow: auto;
  padding: 1rem 0;
}

.proof-image {
  width: 100%;
  height: auto;
  border-radius: 8px;
  border: 2px solid #4b824b;
}

.modal-footer {
  justify-content: center;
}

.close-btn {
  background: transparent;
  color: #4b824b;
  border: 2px solid #4b824b;
  padding: 0.5rem 1.5rem;
  border-radius: 6px;
  font-weight: 500;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #4b824b;
  color: #FFFAE9;
}
</style>
