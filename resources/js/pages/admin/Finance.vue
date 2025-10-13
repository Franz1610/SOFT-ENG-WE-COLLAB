<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const props = defineProps<{
  transactions: any[],
  summary: { income: number, expense: number, net: number, revenue: number },
  incomeCategories: string[],
  expenseCategories: string[]
}>();

// Get current user from shared props
const page = usePage();
const currentUser = computed(() => page.props.auth?.user);

const showModal = ref(false);
const editId = ref(null);
const form = ref({
  date: new Date().toISOString().split('T')[0], // Default to today
  description: '',
  type: 'income',
  amount: '',
  category: '',
  payment_method: 'Cash',
  reference: ''
});

// Define specific categories based on type
const categoryOptions = computed(() => {
  if (form.value.type === 'income') {
    return ['Booking Payment', 'Additional Service', 'Other Income'];
  } else {
    return ['Maintenance', 'Utilities', 'Supplies', 'Staff Salary'];
  }
});

// Filter categories - show all when no type selected, or specific type categories
const filterCategoryOptions = computed(() => {
  if (!filterType.value || filterType.value === '') {
    // Show all categories when "All Types" is selected
    return [...props.incomeCategories, ...props.expenseCategories];
  } else if (filterType.value === 'expense') {
    return props.expenseCategories;
  } else {
    return props.incomeCategories;
  }
});

const paymentMethods = ['Credit Card', 'Bank Transfer', 'Cash', 'Gift Card', 'Other'];

function openModal(transaction: any = null) {
  console.log('Opening modal, transaction:', transaction);
  showModal.value = true;
  // Prevent body scrolling when modal is open
  document.body.style.overflow = 'hidden';
  
  editId.value = transaction ? transaction.id : null;
  if (transaction) {
    // Check if it's a general transaction (editable) or finance entry (read-only)
    if (String(transaction.id).startsWith('finance_')) {
      alert('Finance entries cannot be edited from this interface. Use the booking finance entry system instead.');
      closeModal();
      return;
    }
    // Populate form with transaction data
    form.value = {
      date: formatDateForInput(transaction.date),
      description: transaction.description,
      type: transaction.type,
      amount: transaction.amount,
      category: transaction.category,
      payment_method: transaction.payment_method || 'Cash',
      reference: transaction.reference || ''
    };
  } else {
    // Reset form for new transaction
    form.value = { 
      date: new Date().toISOString().split('T')[0],
      description: '', 
      type: 'income', 
      amount: '', 
      category: '', 
      payment_method: 'Cash',
      reference: '' 
    };
  }
}

function closeModal() {
  console.log('Closing modal...'); // Debug log
  showModal.value = false;
  editId.value = null;
  // Restore body scrolling when modal is closed
  document.body.style.overflow = '';
}

function submit() {
  if (editId.value) {
    router.put(`/admin/finance/transactions/${editId.value}`, form.value, { 
      onSuccess: () => { 
        closeModal(); 
        router.reload(); 
      },
      onError: (errors) => {
        console.error('Update error:', errors);
        alert('Error updating transaction. Please try again.');
      }
    });
  } else {
    router.post('/admin/finance/transactions', form.value, { 
      onSuccess: () => { 
        closeModal(); 
        router.reload(); 
      },
      onError: (errors) => {
        console.error('Create error:', errors);
        alert('Error creating transaction. Please try again.');
      }
    });
  }
}

// Also ensure modal closes properly if user navigates away
function handleBeforeUnload() {
  if (showModal.value) {
    document.body.style.overflow = '';
  }
}

// Add event listener for page unload
if (typeof window !== 'undefined') {
  window.addEventListener('beforeunload', handleBeforeUnload);
}

function remove(id: number) {
  if (confirm('Delete this transaction?')) {
    // Check if it's a general transaction (not a finance entry)
    if (String(id).startsWith('transaction_')) {
      router.delete(`/admin/finance/transactions/${id}`, { onSuccess: () => router.reload() });
    } else {
      alert('Finance entries cannot be deleted from this interface.');
    }
  }
}

// Filtering logic
const filterType = ref('');
const filterCategory = ref('');
const filterStart = ref('');
const filterEnd = ref('');
function applyFilters() {
  router.get('/admin/finance', {
    type: filterType.value,
    category: filterCategory.value,
    start_date: filterStart.value,
    end_date: filterEnd.value
  });
}

// Helper function to format date for display (M-D-Y)
function formatDate(dateString: string) {
  if (!dateString) return '';
  // Handle different date formats
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString; // Return original if invalid
  
  // Format as M-D-Y (e.g., 10-13-2025)
  const month = date.getMonth() + 1; // getMonth() is 0-indexed
  const day = date.getDate();
  const year = date.getFullYear();
  return `${month}-${day}-${year}`;
}

// Helper function to format date for form inputs (YYYY-MM-DD)
function formatDateForInput(dateString: string) {
  if (!dateString) return '';
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return dateString;
  return date.toLocaleDateString('en-CA'); // Returns YYYY-MM-DD format for input
}

// Helper function to capitalize first letter
function capitalizeFirst(str: string) {
  if (!str) return '';
  return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

<template>
  <AppLayout>
    <div class="finance-bg min-h-screen">
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-[#4b824b] mb-2">Finance Management</h1>
          <p class="text-[#344C34]">Monitor and manage all financial transactions and revenue</p>
        </div>
        <button @click="openModal()" class="add-btn">
          Add Transaction
        </button>
      </div>

      <!-- Statistics Cards -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-4 mb-8">
        <div class="stats-card income">
          <div class="card-title">Total Income</div>
          <div class="card-value">₱{{ summary.income }}</div>
        </div>
        <div class="stats-card expense">
          <div class="card-title">Total Expenses</div>
          <div class="card-value">₱{{ summary.expense }}</div>
        </div>
        <div class="stats-card net">
          <div class="card-title">Net Balance</div>
          <div class="card-value">₱{{ summary.net }}</div>
        </div>
        <div class="stats-card revenue">
          <div class="card-title">Revenue</div>
          <div class="card-value">₱{{ summary.revenue }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters mb-6">
        <select v-model="filterType">
          <option value="">All Types</option>
          <option value="income">Income</option>
          <option value="expense">Expense</option>
        </select>
        <select v-model="filterCategory">
          <option value="">All Categories</option>
          <option v-for="cat in filterCategoryOptions" :key="cat" :value="cat">{{ cat }}</option>
        </select>
        <label>
          From:
          <input v-model="filterStart" type="date" />
        </label>
        <label>
          To:
          <input v-model="filterEnd" type="date" />
        </label>
        <button @click="applyFilters" class="filter-btn">Filter</button>
      </div>

      <!-- Transactions Table -->
      <div class="main-content-area p-6 rounded-xl border border-[#4b824b] bg-[#FFFAE9]">
        <table class="finance-table w-full">
          <thead>
            <tr>
              <th>Date</th>
              <th>Description</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Category</th>
              <th>Added By</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="t in transactions" :key="t.id">
              <td>{{ formatDate(t.date) }}</td>
              <td>{{ t.description }}</td>
              <td>{{ capitalizeFirst(t.type) }}</td>
              <td>₱{{ t.amount }}</td>
              <td>{{ t.category }}</td>
              <td>{{ t.user?.name }}</td>
              <td>
                <button 
                  v-if="!String(t.id).startsWith('finance_')" 
                  @click="openModal(t)" 
                  class="edit-btn"
                >
                  Edit
                </button>
                <span 
                  v-else 
                  class="text-gray-500 text-sm"
                  title="Finance entries cannot be edited here"
                >
                  Finance Entry
                </span>
                <button 
                  v-if="!String(t.id).startsWith('finance_')" 
                  @click="remove(t.id)" 
                  class="delete-btn"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Modal -->
      <div v-if="showModal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 999999;">
        <div style="background: white; padding: 2rem; border-radius: 10px; min-width: 400px; max-width: 500px; max-height: 80vh; overflow-y: auto; color: #333; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
          <h2 style="font-size: 1.25rem; font-weight: bold; margin-bottom: 1rem; color: #333;">{{ editId ? 'Edit Transaction' : 'Add Transaction' }}</h2>
          <form @submit.prevent="submit">
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Date:</label>
              <input v-model="form.date" type="date" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333;" />
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Description:</label>
              <input v-model="form.description" placeholder="Enter transaction description" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333;" />
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Type:</label>
              <select v-model="form.type" required @change="form.category = ''" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333;">
                <option value="income">Income</option>
                <option value="expense">Expense</option>
              </select>
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Category:</label>
              <select v-model="form.category" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333;">
                <option value="" disabled>Select Category</option>
                <option v-for="cat in categoryOptions" :key="cat" :value="cat">{{ cat }}</option>
              </select>
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Amount:</label>
              <input v-model="form.amount" type="number" min="0" step="0.01" placeholder="0.00" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333;" />
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Payment Method:</label>
              <select v-model="form.payment_method" required style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333;">
                <option v-for="method in paymentMethods" :key="method" :value="method">{{ method }}</option>
              </select>
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Added By:</label>
              <input :value="currentUser?.name || 'Unknown User'" readonly style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; background-color: #f3f4f6; cursor: not-allowed; color: #666;" />
            </div>
            
            <div style="margin-bottom: 1rem;">
              <label style="display: block; margin-bottom: 0.3rem; color: #333; font-weight: 500;">Reference/Notes (optional):</label>
              <textarea v-model="form.reference" placeholder="Additional notes or reference information" rows="2" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; color: #333; resize: vertical; font-family: inherit;"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
              <button type="submit" style="background: #2e7d32; color: #fff; border: none; padding: 0.5rem 1.2rem; border-radius: 6px; font-weight: bold; cursor: pointer; flex: 1;">{{ editId ? 'Update Transaction' : 'Add Transaction' }}</button>
              <button type="button" @click="closeModal" style="background: #cfcfcf; color: #333; border: none; padding: 0.5rem 1.2rem; border-radius: 6px; font-weight: bold; cursor: pointer; flex: 1;">Cancel</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.finance-bg {
  background: #FFFAE9;
  min-height: 100vh;
  padding: 2rem 1rem;
}
.stats-card {
  background: #4b824b;
  border-radius: 12px;
  padding: 1.2rem 2rem;
  color: #FFFAE9;
  box-shadow: 0 2px 8px #0001;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.stats-card.income { border-left: 6px solid #2e7d32; }
.stats-card.expense { border-left: 6px solid #c62828; }
.stats-card.net { border-left: 6px solid #1565c0; }
.stats-card.revenue { border-left: 6px solid #6a1b9a; }
.card-title { font-size: 1rem; font-weight: 500; margin-bottom: 0.5rem; }
.card-value { font-size: 1.5rem; font-weight: bold; }
.filters {
  display: flex;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1.5rem;
}
.filter-btn {
  background: #4b824b;
  color: #FFFAE9;
  border: none;
  padding: 0.4rem 1.2rem;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}
.add-btn {
  background: #4b824b;
  color: #fff;
  border: none;
  padding: 0.5rem 1.2rem;
  border-radius: 6px;
  font-weight: bold;
  margin-bottom: 1rem;
  cursor: pointer;
}
.finance-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
}
.finance-table th, .finance-table td {
  border: 1px solid #cfcfcf;
  padding: 0.6rem;
  text-align: left;
}
.edit-btn, .delete-btn {
  margin-right: 0.5rem;
  padding: 0.3rem 0.8rem;
  border-radius: 4px;
  border: none;
  cursor: pointer;
}
.edit-btn { background: #1976d2; color: #fff; }
.delete-btn { background: #c62828; color: #fff; }
</style>