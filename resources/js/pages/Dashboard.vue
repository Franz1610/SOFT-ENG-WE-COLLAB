<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
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
const user = computed(() => page.props.auth.user);

// Modal state
const showLogoutModal = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const users = usePage().props.users as any[];

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
</script>

<template>
  <div style="background: #232323; min-height: 100vh;">
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo">WECOLLAB</div>
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
          <a href="#" class="nav-link">Booking</a>
          <button class="home-btn">HOME</button>
        </nav>
      </div>
    </header>
    <main class="main-content">
      <Head title="Dashboard" />

      <AppLayout :breadcrumbs="breadcrumbs">
          <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
              <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                  <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                      <!-- Content for box 1 -->
                  </div>
                  <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                      <!-- Content for box 2 -->
                  </div>
                  <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                      <!-- Content for box 3 -->
                  </div>
              </div>
              <div class="relative min-h-100vh flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                  <UserCrud :users="users" />
              </div>
          </div>
      </AppLayout>
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

/* Card backgrounds */
.grid > div {
    background-color: #4b824b;
    color: #FFFAE9;
    border-color: #4b824b !important;
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

/* Sticky header styles */
.header {
    position: sticky;
    top: 0;
    width: 100%;
    z-index: 1000;
}

.header-inner {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 1rem;
    height: 60px;
}

.logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #FFFAE9;
}

.nav {
    display: flex;
    gap: 1rem;
}

.nav-link {
    color: #FFFAE9;
    text-decoration: none;
    padding: 0.3rem 0.7rem;
    border-radius: 6px;
    transition: background 0.2s, color 0.2s;
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
    background-color: #4b824b;
    color: #FFFAE9;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
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
