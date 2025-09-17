<template>
  <div style="background: #232323; min-height: 100vh;">
    <!-- Header -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo" @click="goHome">WECOLLAB</div>
        <nav class="nav">
          <a href="/login" class="nav-link">Log in</a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="#" class="nav-link">What's NEW?</a>
          <a href="/booking" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content flex-1 flex flex-col items-center justify-center px-6 py-16">
      <div class="w-full max-w-md bg-white rounded-lg shadow-lg p-8">
        <h1 class="text-3xl font-bold mb-2 text-center text-neutral-900">SELECT DATE & TIME</h1>
        <p class="text-center text-neutral-600 mb-8">Choose your preferred date and time for your booking</p>
        
        <form @submit.prevent="submitSchedule" class="space-y-6">
          <div>
            <Label for="date">Date</Label>
            <Input 
              id="date" 
              v-model="form.date" 
              type="date" 
              :min="minDate"
              required 
              class="mt-1"
            />
          </div>
          
          <div>
            <Label for="time">Time</Label>
            <Input 
              id="time" 
              v-model="form.time" 
              type="time" 
              required 
              class="mt-1"
            />
          </div>
          
          <div class="flex gap-3">
            <Button 
              type="button" 
              variant="outline" 
              class="flex-1" 
              @click="goBack"
            >
              Back
            </Button>
            <Button 
              type="submit" 
              class="flex-1 bg-[#495846] hover:bg-[#38613a] text-white border-none"
            >
              Confirm Schedule
            </Button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const form = ref({
  date: '',
  time: '',
});

// Set minimum date to today
const minDate = computed(() => {
  const today = new Date();
  return today.toISOString().split('T')[0];
});

function submitSchedule() {
  if (!form.value.date || !form.value.time) {
    alert('Please select both date and time');
    return;
  }
  
  // Here you would typically send the booking data to the backend
  // For now, we'll just show a success message and redirect
  alert(`Booking confirmed for ${form.value.date} at ${form.value.time}`);
  
  // Redirect to a confirmation page or back to home
  router.visit('/');
}

function goBack() {
  router.visit('/booking');
}

function goHome() {
  router.visit('/');
}
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
</style>
