<template>
  <div style="background: #232323; min-height: 100vh;">
    <!-- Header -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo">WECOLLAB</div>
        <nav class="nav">
          <Link href="/login" class="nav-link">Log in</Link>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="#" class="nav-link">What's NEW?</a>
          <a href="#" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>

    <!-- Main Content -->
    <main class="main-content flex-1 flex flex-col items-center justify-start px-2 py-8 md:py-12">
      <div class="w-full max-w-6xl flex flex-col md:flex-row gap-12 md:gap-20 items-start justify-center">
        <!-- Left: Booking Title and History -->
        <section class="flex-1 min-w-[260px] max-w-xs w-full flex flex-col items-start">
          <h1 class="text-4xl font-bold mb-2 text-neutral-900">BOOKING</h1>
          <p class="mb-8 text-neutral-600">Wanna reserve for today or tommorow?</p>
          <Button variant="default" class="mb-8 w-full md:w-auto bg-[#495846] hover:bg-[#38613a] text-white border-none">Booking History/modify</Button>
        </section>

        <!-- Right: Booking Form -->
        <section class="flex-1 min-w-[320px] max-w-lg w-full">
          <h2 class="text-xl font-semibold mb-6 text-neutral-900">Information</h2>
          <form class="space-y-4" @submit.prevent="submitBooking">
            <div class="flex gap-3">
              <div class="flex-1">
                <Label for="firstName">First name</Label>
                <Input id="firstName" v-model="form.firstName" autocomplete="given-name" required />
              </div>
              <div class="flex-1">
                <Label for="lastName">Last name</Label>
                <Input id="lastName" v-model="form.lastName" autocomplete="family-name" required />
              </div>
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <Label for="contact">Contact Number</Label>
                <Input id="contact" v-model="form.contact" type="tel" autocomplete="tel" required />
              </div>
              <div class="flex-1">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" autocomplete="email" required />
              </div>
            </div>
            <div class="flex gap-3">
              <div class="flex-1">
                <Label for="additional">Additional Info</Label>
                <Input id="additional" v-model="form.additional" />
              </div>
              <div class="flex-1">
                <Label for="pax">No. of Pax</Label>
                <Input id="pax" v-model="form.pax" type="number" min="1" required />
              </div>
            </div>
            <div>
              <Label for="category">Category</Label>
              <select id="category" v-model="form.category" class="w-full border rounded-md px-3 py-2 mt-1" required>
                <option value="" disabled selected>Select an option</option>
                <option value="individual">Individual</option>
                <option value="master">Master</option>
                <option value="common">Common</option>
              </select>
            </div>
            <Button type="submit" class="w-full mt-4 bg-[#495846] hover:bg-[#38613a] text-white border-none">Submit</Button>
          </form>
        </section>
      </div>

      <!-- Select a Room Section -->
      <div class="w-full max-w-6xl mt-16">
        <h2 class="text-xl font-semibold mb-6 text-neutral-900">SELECT A ROOM</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div v-for="room in rooms" :key="room.id" @click="selectRoom(room.id)"
            :class="['cursor-pointer transition-all border-2 rounded-xl p-4 flex flex-col items-center',
              selectedRoom === room.id ? 'border-green-700 bg-green-50' : 'border-neutral-200 bg-white',
              'hover:border-green-600']">
            <img :src="room.image" :alt="room.name" class="w-48 h-36 object-cover rounded-lg mb-3" />
            <div class="text-lg font-bold text-neutral-900">{{ room.name }}</div>
            <div class="text-sm font-medium text-neutral-600">{{ room.availability }}</div>
            <div class="text-xs text-neutral-500 mt-1">{{ room.capacity }}</div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>


<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref } from 'vue';
import { router, Link } from '@inertiajs/vue3';

const form = ref({
  firstName: '',
  lastName: '',
  contact: '',
  email: '',
  additional: '',
  pax: '',
  category: '',
});

const rooms = ref([
  {
    id: 1,
    name: 'INDIV ROOM',
    image: '/images/booking/indiv_room.png',
    availability: 'FULL', // Placeholder, will be dynamic
    capacity: '1 PAX ONLY',
  },
  {
    id: 2,
    name: 'MASTER ROOM',
    image: '/images/booking/master_room.png',
    availability: '3 ROOMS VACANT', // Placeholder, will be dynamic
    capacity: '5-10 PAX ONLY',
  },
  {
    id: 3,
    name: 'COMMON ROOM',
    image: '/images/booking/common_room.png',
    availability: '1 ROOM VACANT', // Placeholder, will be dynamic
    capacity: '3-5 PAX ONLY',
  },
]);

const selectedRoom = ref<number|null>(null);
function selectRoom(id: number) {
  selectedRoom.value = id;
}


function submitBooking() {
  // You can send the form data to the backend here if needed
  // Redirect to the schedule selection page using Inertia
  router.visit('/booking/schedule');
}

function goHome() {
  window.location.href = '/';
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

/* Responsive and minimal tweaks */
@media (max-width: 900px) {
  main.flex-1 {
    flex-direction: column;
    gap: 2rem;
  }
  .grid-cols-3 {
    grid-template-columns: 1fr !important;
  }
}
</style>
