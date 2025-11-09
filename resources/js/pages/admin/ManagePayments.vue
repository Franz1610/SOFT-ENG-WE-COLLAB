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
const filter = ref<'all'|'pending'|'verified'|'unprocessed'>('pending');

const rows = computed(() => {
  let list = props.payments || [];
  if (filter.value !== 'all') {
    const map: Record<string,string> = { pending: 'Pending Review', verified: 'Verified', unprocessed: 'Unprocessed' };
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
  <div style="background: #232323; min-height: 100vh;">
    <Head title="Manage Payments" />
    <AppLayout>
      <div class="p-4">
        <h1 class="text-2xl font-bold mb-4 text-[#344C34]">Manage Payments</h1>
        <div class="flex items-center gap-3 mb-4">
          <input v-model="search" placeholder="Search by booking, name, email" class="border rounded px-3 py-2 w-80" />
          <select v-model="filter" class="border rounded px-2 py-2">
            <option value="pending">Pending</option>
            <option value="verified">Verified</option>
            <option value="unprocessed">Unprocessed</option>
            <option value="all">All</option>
          </select>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full bg-white border">
            <thead>
              <tr class="bg-[#4b824b] text-white">
                <th class="px-3 py-2 text-left">Booking</th>
                <th class="px-3 py-2 text-left">Customer</th>
                <th class="px-3 py-2 text-left">Amount</th>
                <th class="px-3 py-2 text-left">Status</th>
                <th class="px-3 py-2 text-left">Proof</th>
                <th class="px-3 py-2 text-left">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in rows" :key="p.id" class="border-b">
                <td class="px-3 py-2">#{{ p.booking_id }}<div class="text-xs text-gray-500">{{ p.date }}</div></td>
                <td class="px-3 py-2">
                  <div class="font-medium">{{ p.customer }}</div>
                  <div class="text-xs text-gray-500">{{ p.email }}</div>
                </td>
                <td class="px-3 py-2">₱{{ p.amount_received.toFixed(2) }}</td>
                <td class="px-3 py-2">
                  <div class="flex flex-col gap-1">
                    <span :class="{
                      'bg-yellow-100 text-yellow-800 px-2 py-1 rounded w-fit': p.status==='Pending Review',
                      'bg-green-100 text-green-800 px-2 py-1 rounded w-fit': p.status==='Verified',
                      'bg-gray-100 text-gray-800 px-2 py-1 rounded w-fit': p.status==='Unprocessed'
                    }">{{ p.status }}</span>
                    <span v-if="p.status==='Unprocessed' && (p as any).decline_reason" class="text-xs text-red-700">Declined: {{ (p as any).decline_reason }}</span>
                  </div>
                </td>
                <td class="px-3 py-2">
                  <button
                    v-if="p.proof_url"
                    type="button"
                    class="text-blue-600 underline"
                    @click="openProofById(p.id, `Booking #${p.booking_id} - Proof`)"
                  >view</button>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-3 py-2 flex gap-2">
                  <Button class="bg-green-700 text-white" @click="accept(p.id)" :disabled="p.status==='Verified'">Accept</Button>
                  <Button variant="outline" class="border-red-600 text-red-700" @click="decline(p.id)">Decline</Button>
                </td>
              </tr>
              <tr v-if="rows.length===0">
                <td colspan="6" class="text-center text-gray-500 py-6">No payments to show</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </AppLayout>
  </div>

  <!-- Proof Preview Modal -->
  <Dialog :open="showProof" @update:open="closeProof">
    <DialogContent class="sm:max-w-3xl bg-white">
      <DialogHeader>
        <DialogTitle class="text-lg font-semibold">{{ proofTitle }}</DialogTitle>
        <DialogDescription v-if="proofUrl" class="text-gray-600">Click the image to open in a new tab.</DialogDescription>
      </DialogHeader>
      <div v-if="proofUrl" class="w-full max-h-[70vh] overflow-auto">
        <a :href="proofUrl" target="_blank" rel="noopener">
          <img :src="proofUrl" alt="Payment Proof" class="w-full h-auto object-contain rounded border" />
        </a>
      </div>
      <DialogFooter class="sm:justify-center">
        <Button variant="outline" @click="closeProof">Close</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* keep styling aligned with app theme */
</style>
