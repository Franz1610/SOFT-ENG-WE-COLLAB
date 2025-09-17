<script setup lang="ts">
import { Form } from '@inertiajs/vue3';

function goHome() {
  window.location.href = '/';
}
</script>

<template>
  <div style="background: url('/images/homepage/hero.png') center/cover no-repeat fixed; min-height: 100vh; width: 100vw;">
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo">WECOLLAB</div>
        <nav class="nav">
          <a href="login" class="nav-link">Log in</a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <a href="#" class="nav-link">What's NEW?</a>
          <a href="#" class="nav-link">Booking</a>
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>
    <main class="main-content flex items-center justify-center min-h-screen" style="background: rgba(0,0,0,0.55);">
      <div class="login-card bg-white bg-opacity-90 rounded-2xl shadow-lg p-10 w-full max-w-md flex flex-col items-center">
        <h1 class="text-4xl font-bold mb-6 text-center">Login</h1>
        <Form method="post" :action="route('login')" v-slot="{ errors, processing }" class="w-full flex flex-col gap-5">
          <div>
            <label for="email" class="block font-semibold mb-1">Email</label>
            <input id="email" name="email" type="email" required class="w-full border rounded px-3 py-2" />
            <div v-if="errors.email" class="text-red-500 text-xs mt-1">{{ errors.email }}</div>
          </div>
          <div>
            <label for="password" class="block font-semibold mb-1">Password</label>
            <input id="password" name="password" type="password" required class="w-full border rounded px-3 py-2" />
            <div v-if="errors.password" class="text-red-500 text-xs mt-1">{{ errors.password }}</div>
          </div>
          <div class="flex items-center justify-between w-full mb-2">
            <label class="flex items-center gap-2">
              <input type="checkbox" name="remember" />
              <span>Remember me</span>
            </label>
            <a href="/forgot-password" class="text-sm text-neutral-700 hover:underline">Forgot Password?</a>
          </div>
          <button type="submit" class="w-full py-2 rounded bg-black text-white font-semibold text-lg hover:bg-neutral-800 transition" :disabled="processing">Login</button>
        </Form>
        <div class="mt-4 text-center text-black">
          Don't have an account? <a href="/register" class="text-blue-700 hover:underline">Register now!</a>
        </div>
      </div>
    </main>
  </div>
</template>

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
  width: 100vw;
  max-width: 100vw;
  overflow-x: hidden;
  margin-top: 54px;
}
.login-card {
  box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
}
</style>