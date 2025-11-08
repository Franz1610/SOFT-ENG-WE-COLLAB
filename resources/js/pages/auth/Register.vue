<template>
  <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo">WECOLLAB</div>
        <button
          class="hamburger-btn"
          @click="menuOpen = !menuOpen"
          :aria-expanded="menuOpen"
          aria-label="Toggle navigation menu"
        >
          <span class="hamburger-icon" aria-hidden="true"></span>
        </button>
        <nav class="nav">
          <a href="/login" class="nav-link">Log in</a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="/whats-new" class="nav-link">What's NEW?</a>
          <a href="#" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
        <div v-if="menuOpen" class="mobile-menu">
          <a href="/login" class="nav-link" @click="menuOpen = false">Log in</a>
          <a href="#" class="nav-link" @click="menuOpen = false">Deals & Promo</a>
          <a href="/whats-new" class="nav-link" @click="menuOpen = false">What's NEW?</a>
          <a href="#" class="nav-link" @click="menuOpen = false">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </div>
      </div>
    </header>
    <main class="main-content flex items-center justify-center min-h-screen" style="background: rgba(0,0,0,0.55);">
      <div class="signup-card bg-white bg-opacity-90 rounded-2xl shadow-lg p-6 w-full max-w-sm flex flex-col items-center">
        <h1 class="text-3xl font-bold mb-4 text-center">Sign up</h1>
        <Form method="post" :action="route('register')" v-slot="{ errors, processing }" class="w-full flex flex-col gap-3">
          <div>
            <label for="name" class="block font-semibold mb-1">Name</label>
            <input id="name" name="name" type="text" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.name" class="text-red-500 text-xs mt-1">{{ errors.name }}</div>
          </div>
          <div>
            <label for="email" class="block font-semibold mb-1">Email</label>
            <input id="email" name="email" type="email" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
          </div>
          <div>
            <label for="contact" class="block font-semibold mb-1">Contact Number</label>
            <input id="contact" name="contact" type="text" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.contact" class="text-red-500 text-xs mt-1">{{ errors.contact }}</div>
          </div>
          <div>
            <label for="password" class="block font-semibold mb-1">Password</label>
            <input id="password" name="password" type="password" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</div>
          </div>
          <div>
            <label for="password_confirmation" class="block font-semibold mb-1">Confirm Password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required class="w-full border rounded px-3 py-1.5 text-sm" />
            <div v-if="errors.password_confirmation" class="text-red-500 text-xs mt-1">{{ errors.password_confirmation }}</div>
          </div>
          <div class="flex items-center mb-2">
            <input type="checkbox" name="remember" id="remember" class="mr-2" />
            <label for="remember" class="text-sm">Remember me</label>
          </div>
          <button type="submit" class="w-full py-2 rounded bg-black text-white font-semibold text-base hover:bg-neutral-800 transition"
            :disabled="processing">Sign up</button>
        </Form>
        <div class="mt-3 text-center text-black text-sm">
          Already have an account? <a href="/login" class="text-blue-700 hover:underline">Sign in now!</a>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { Form } from '@inertiajs/vue3';
import { ref } from 'vue';
function goHome() {
  window.location.href = '/';
}

// Mobile menu state
const menuOpen = ref(false);
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
.home-btn {
  background: transparent;
  color: #fff;
  border: none;
  border-radius: 6px;
  padding: 0.3rem 1rem;
  font-size: 1rem;
  font-weight: 400;
  cursor: pointer;
  transition: background 0.2s, color 0.2s;
  margin-left: 0.5rem;
  line-height: 1.2;
}
.home-btn:hover {
  background: #fff;
  color: #495846;
}
main.main-content {
  min-height: 100vh;
  width: 100vw;
  max-width: 100vw;
  overflow-x: hidden;
  margin-top: 54px;
}
.nav { display: flex; }

@media (max-width: 900px) {
  .nav { display: none; }
  .hamburger-btn { display: inline-flex; align-items: center; justify-content: center; }
}
.signup-card {
  box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>
