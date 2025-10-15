<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';

interface Feedback {
  id: number;
  created_at: string;
  user_name: string;
  type: string;
  rating: number;
  comments: string;
}

const feedbackList = ref<Feedback[]>([]);
const loading = ref(true);

// Filters
const filterType = ref('');
const filterRating = ref('');
const filterStart = ref('');
const filterEnd = ref('');
const search = ref('');

// Pagination (if present)
const currentPage = ref(1);
const lastPage = ref(1);

// Summary counters
const totalFeedback = computed(() => filteredFeedback.value.length);
const averageRating = computed(() => {
  if (!filteredFeedback.value.length) return 0;
  return (
    filteredFeedback.value.reduce((sum, f) => sum + Number(f.rating), 0) /
    filteredFeedback.value.length
  ).toFixed(2);
});

// Fetch feedback from backend with filters
function fetchFeedback(page = 1) {
  loading.value = true;
  router.get(
    route('admin.feedback.index'),
    {
      type: filterType.value,
      rating: filterRating.value,
      start_date: filterStart.value,
      end_date: filterEnd.value,
      search: search.value,
      page: page
    },
    {
      preserveState: true,
      replace: false,
      onSuccess: page => {
        const feedback = page.props.feedback as {
          data: Feedback[];
          current_page: number;
          last_page: number;
        };
        feedbackList.value = Array.isArray(feedback.data)
          ? feedback.data
          : [];
        currentPage.value = feedback.current_page || 1;
        lastPage.value = feedback.last_page || 1;
        loading.value = false;
      }
    }
  );
}

// Clear all filters and reload data
function clearFilters() {
  filterType.value = '';
  filterRating.value = '';
  filterStart.value = '';
  filterEnd.value = '';
  search.value = '';
  fetchFeedback(1);
}

onMounted(() => fetchFeedback());

// Filtering logic (for summary boxes)
const filteredFeedback = computed(() => feedbackList.value);

// Delete feedback
function confirmDelete(id: number) {
  if (confirm('Are you sure you want to delete this feedback?')) {
    router.delete(route('admin.feedback.destroy', id), {
      onSuccess: () => fetchFeedback(currentPage.value)
    });
  }
}

const showCommentModal = ref(false);
const modalComment = ref('');

function openCommentModal(comment: string) {
  modalComment.value = comment;
  showCommentModal.value = true;
}

function closeCommentModal() {
  showCommentModal.value = false;
  modalComment.value = '';
}
</script>

<template>
  <AppLayout>
    <div class="feedback-bg min-h-screen">
      <!-- Header Section -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-[#4b824b] mb-2">Feedback Management</h1>
          <p class="text-[#344C34]">View and manage all user-submitted feedback</p>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid auto-rows-min gap-4 md:grid-cols-3 mb-8">
        <div class="stats-card feedback">
          <div class="card-title">Total Feedback</div>
          <div class="card-value">{{ totalFeedback }}</div>
        </div>
        <div class="stats-card rating">
          <div class="card-title">Average Rating</div>
          <div class="card-value">{{ averageRating }}</div>
        </div>
        <!-- Replace the Feedback Count per Type card with this Rating Summary card -->
        <div class="stats-card rating-summary">
          <div class="card-title">Rating Summary</div>
          <div class="card-value rating-list">
            <div class="rating-row">
              <span class="rating-badge positive">Positive</span>
              <span class="rating-count">{{ filteredFeedback.filter(f => f.rating >= 4).length }}</span>
            </div>
            <div class="rating-row">
              <span class="rating-badge neutral">Neutral</span>
              <span class="rating-count">{{ filteredFeedback.filter(f => f.rating === 3).length }}</span>
            </div>
            <div class="rating-row">
              <span class="rating-badge negative">Negative</span>
              <span class="rating-count">{{ filteredFeedback.filter(f => f.rating <= 2).length }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters mb-6">
        <select v-model="filterType">
          <option value="">All Types</option>
          <option value="general">General</option>
          <option value="page">Page</option>
          <option value="product">Product</option>
        </select>
        <select v-model="filterRating">
          <option value="">All Ratings</option>
          <option v-for="r in [1,2,3,4,5]" :key="r" :value="r">{{ r }}</option>
        </select>
        <label>
          From:
          <input v-model="filterStart" type="date" />
        </label>
        <label>
          To:
          <input v-model="filterEnd" type="date" />
        </label>
        <input type="text" v-model="search" placeholder="Search by user or comment" />
        <button @click="fetchFeedback(currentPage)" class="filter-btn" :disabled="loading">Refresh</button>
        <button @click="clearFilters" class="filter-btn" :disabled="loading" style="background:#cfcfcf; color:#333;">Clear Filter</button>
      </div>

      <!-- Feedback Table -->
      <div class="main-content-area p-6 rounded-xl border border-[#4b824b] bg-[#FFFAE9]">
        <table class="feedback-table w-full">
          <thead>
            <tr>
              <th>Date Submitted</th>
              <th>User Name</th>
              <th>Type</th>
              <th>Rating</th>
              <th class="comments-col">Comments</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="feedback in filteredFeedback" :key="feedback.id">
              <td>{{ new Date(feedback.created_at).toLocaleString() }}</td>
              <td>{{ feedback.user_name }}</td>
              <td>{{ feedback.type }}</td>
              <td>{{ feedback.rating }}</td>
              <td class="comments-col">
                <span
                  class="comments-text clickable"
                  :title="feedback.comments"
                  @click="openCommentModal(feedback.comments)"
                >
                  {{ feedback.comments }}
                </span>
                <span
                  class="view-icon"
                  @click="openCommentModal(feedback.comments)"
                  title="View full comment"
                >&#128065;</span>
              </td>
              <td>
                <button @click="confirmDelete(feedback.id)" class="delete-btn">Delete</button>
              </td>
            </tr>
            <tr v-if="!filteredFeedback.length">
              <td colspan="6">No feedback found.</td>
            </tr>
          </tbody>
        </table>
        <!-- Pagination controls (if present) -->
        <div v-if="lastPage > 1" class="pagination-controls">
          <button
            :disabled="currentPage === 1"
            @click="fetchFeedback(currentPage - 1)"
          >Previous</button>
          <span>Page {{ currentPage }} of {{ lastPage }}</span>
          <button
            :disabled="currentPage === lastPage"
            @click="fetchFeedback(currentPage + 1)"
          >Next</button>
        </div>
      </div>

      <!-- Comment Modal -->
      <div v-if="showCommentModal" class="modal-overlay">
        <div class="modal-content">
          <h3>Full Comment</h3>
          <div class="modal-body">{{ modalComment }}</div>
          <button class="close-btn" @click="closeCommentModal">Close</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.feedback-bg {
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
.stats-card.feedback { border-left: 6px solid #2e7d32; }
.stats-card.rating { border-left: 6px solid #1565c0; }
.stats-card.type { border-left: 6px solid #6a1b9a; }
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
.feedback-table {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 2rem;
  table-layout: fixed;
}
.feedback-table th,
.feedback-table td {
  border: 1px solid #cfcfcf;
  padding: 0.6rem;
  text-align: left;
  word-break: break-word;
}
.comments-col {
  max-width: 220px;
  width: 220px;
  min-width: 120px;
  overflow: hidden;
}
.comments-text {
  display: block;
  white-space: normal;
  overflow: hidden;
  text-overflow: ellipsis;
  max-height: 3.6em;
  line-height: 1.2em;
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  cursor: pointer;
}
.comments-text.clickable {
  cursor: pointer;
  color: #1565c0;
  text-decoration: underline dotted;
}
.view-icon {
  margin-left: 6px;
  cursor: pointer;
  font-size: 1.1em;
  vertical-align: middle;
  color: #4b824b;
}
.delete-btn {
  background: #c62828;
  color: #fff;
  border: none;
  padding: 0.3rem 0.8rem;
  border-radius: 4px;
  cursor: pointer;
}
.modal-overlay {
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: #fff;
  padding: 2rem 2rem 4.5rem 2rem;
  border-radius: 14px;
  width: 60vw;
  max-width: 600px;
  min-width: 320px;
  max-height: 60vh;
  box-shadow: 0 2px 16px #0002;
  text-align: left;
  display: flex;
  flex-direction: column;
  position: relative;
}

.modal-content h3 {
  margin-top: 0;
  color: #4b824b;
  margin-bottom: 1rem;
  font-size: 1.5rem;
  font-weight: bold;
}

.modal-body {
  color: #222;
  word-break: break-word;
  white-space: pre-line;
  line-height: 1.6;
  font-size: 1.1rem;
  padding: 0.5rem 0.2rem 0.5rem 0.2rem;
  overflow-y: auto;
  max-height: 32vh;
  margin-bottom: 0.5rem;
}

.close-btn {
  background: #4b824b;
  color: #fff;
  border: none;
  padding: 0.7rem 1.5rem;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
  position: absolute;
  left: 2rem;
  right: 2rem;
  bottom: 1.2rem;
}
.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 1rem;
}
.pagination-controls button {
  background: #4b824b;
  color: #fff;
  border: none;
  padding: 0.4rem 1.2rem;
  border-radius: 6px;
  font-weight: bold;
  cursor: pointer;
}
.pagination-controls button:disabled {
  background: #cfcfcf;
  color: #333;
  cursor: not-allowed;
}
.type-list {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
  align-items: flex-start;
}
.type-badge-row {
  display: flex;
  align-items: center;
  gap: 0.7em;
  margin-bottom: 0.1em;
}
.type-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.4em;
  padding: 0.2em 0.9em;
  border-radius: 14px;
  font-size: 1em;
  font-weight: 500;
  color: #fff;
  margin-right: 0.2em;
}
.type-badge.general {
  background: #4b824b;
}
.type-badge.page {
  background: #1565c0;
}
.type-badge.product {
  background: #6a1b9a;
}
.type-count {
  font-weight: bold;
  color: #344C34;
  min-width: 2em;
  text-align: right;
}
.type-percent {
  font-size: 0.95em;
  color: #888;
  min-width: 3em;
}
.type-none {
  color: #888;
  font-size: 0.95em;
}
.rating-summary {
  border-left: 6px solid #ffb300;
}
.rating-list {
  display: flex;
  flex-direction: column;
  gap: 0.5em;
  align-items: flex-start;
}
.rating-row {
  display: flex;
  align-items: center;
  gap: 0.7em;
  margin-bottom: 0.1em;
}
.rating-badge {
  display: inline-block;
  padding: 0.2em 0.9em;
  border-radius: 14px;
  font-size: 1em;
  font-weight: 500;
  color: #fff;
  margin-right: 0.2em;
}
.rating-badge.positive {
  background: #388e3c;
}
.rating-badge.neutral {
  background: #ffb300;
  color: #222;
}
.rating-badge.negative {
  background: #c62828;
}
.rating-count {
  font-weight: bold;
  color: #344C34;
  min-width: 2em;
  text-align: right;
}
</style>