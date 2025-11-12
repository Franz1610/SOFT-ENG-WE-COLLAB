<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import UserCrud from '@/components/UserCrud.vue';
import { computed, ref } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

// Get authentication data from Inertia
const page = usePage();

// Modal state
const showLogoutModal = ref(false);

const users = usePage().props.users as any[];

// Dashboard metrics from server
const metrics = computed(() => (page.props.metrics || {
  totalUsers: users?.length ?? 0,
  activeBookings: 0,
  blockedUsers: users?.filter((u: any) => u.is_blocked)?.length ?? 0,
}) as {
  totalUsers: number;
  activeBookings: number;
  blockedUsers: number;
});

function closeLogoutModal() {
  showLogoutModal.value = false;
}

function confirmLogout() {
  showLogoutModal.value = false;
  router.post('/logout');
}
</script>

<template>
  <div style="background: #232323; min-height: 100vh;">
    <Head title="Dashboard" />

    <AppLayout>
          <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
          <!-- Total Users -->
          <div class="stat-card">
            <div class="stat-inner">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                  <path d="M16 11c1.657 0 3-1.79 3-4s-1.343-4-3-4-3 1.79-3 4 1.343 4 3 4zM8 11c1.657 0 3-1.79 3-4S9.657 3 8 3 5 4.79 5 7s1.343 4 3 4zM8 13c-3.866 0-7 2.239-7 5v2h10v-2c0-1.657.895-3.156 2.344-4.25C12.106 13.275 10.145 13 8 13zm8 0c-.79 0-1.543.098-2.25.28A6.476 6.476 0 0 1 18 18v2h6v-2c0-2.761-3.134-5-7-5z"/>
                </svg>
              </div>
              <div class="stat-text">
                <div class="stat-label">Total Users</div>
                <div class="stat-value">{{ metrics.totalUsers }}</div>
              </div>
            </div>
          </div>

          <!-- Active Bookings -->
          <div class="stat-card">
            <div class="stat-inner">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                  <path d="M7 2a1 1 0 0 1 1 1v1h8V3a1 1 0 1 1 2 0v1h1a2 2 0 0 1 2 2v3H2V6a2 2 0 0 1 2-2h1V3a1 1 0 1 1 2 0v1zm15 8v8a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-8h20zm-6.293 3.293-4.414 4.414-2.293-2.293-1.414 1.414 3.707 3.707 5.828-5.828-1.414-1.414z"/>
                </svg>
              </div>
              <div class="stat-text">
                <div class="stat-label">Active Bookings</div>
                <div class="stat-value">{{ metrics.activeBookings }}</div>
              </div>
            </div>
          </div>

          <!-- Blocked Users -->
          <div class="stat-card">
            <div class="stat-inner">
              <div class="stat-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8">
                  <path d="M12 2a10 10 0 1 0 10 10A10.011 10.011 0 0 0 12 2Zm5.657 5.757-9.9 9.9A8 8 0 0 1 17.657 7.757ZM6.343 16.243l9.9-9.9A8 8 0 0 1 6.343 16.243Z"/>
                </svg>
              </div>
              <div class="stat-text">
                <div class="stat-label">Blocked Users</div>
                <div class="stat-value">{{ metrics.blockedUsers }}</div>
              </div>
            </div>
          </div>
        </div>
              <div class="relative min-h-100vh flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                  <UserCrud :users="users" />
              </div>
          </div>
      </AppLayout>

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
  </div>
</template>

<style scoped>
/* Main dashboard area */
.flex {
    background-color: #FFFAE9;
    color: #4b824b;
}

/* Accent for cards and borders */
.border-sidebar-border {
    border-color: #4b824b !important;
}

/* Stats card styling */
.stat-card {
  background-color: #4b824b; /* green */
  color: #FFFAE9; /* cream text */
  border: 1px solid #4b824b;
  border-radius: 0.75rem; /* rounded-lg */
  padding: 1rem; /* p-4 */
  box-shadow: 0 4px 12px rgba(0,0,0,0.12); /* soft shadow */
}

.stat-inner {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-height: 4.5rem;
}

.stat-icon {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 3rem;
  height: 3rem;
  border-radius: 9999px;
  background: rgba(255, 250, 233, 0.18); /* subtle cream tint */
}

.stat-text .stat-label {
  font-size: 0.875rem;
  opacity: 0.95;
}

.stat-text .stat-value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1.2;
}

/* Main content area */
.rounded-xl,
.min-h-100vh {
    background-color: #FFFAE9;
    color: #4b824b;
    border-color: #4b824b !important;
}

/* Placeholder accent */
.placeholder-accent {
    background-color: #4b824b;
    color: #FFFAE9;
}

/* Sidebar styles (add these to your sidebar component or global CSS) */
.sidebar,
.sidebar-content {
    background-color: #FFFAE9 !important;
    color: #4b824b !important;
}

/* Sidebar links */
.sidebar a,
.sidebar-content a {    
    color: #4b824b !important;
}

/* Sidebar active item */
.sidebar .active,
.sidebar-content .active {
    background-color: #4b824b !important;
    color: #FFFAE9 !important;
}

/* Make the big rectangle at the bottom use #4b824b as background and #FFFAE9 as text */
.min-h-100vh {
    background-color: #4b824b !important;
    color: #FFFAE9 !important;
    border-color: #4b824b !important;
}

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}
.logout-btn:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}
</style>