<template>
  <header class="header sticky-header">
    <div class="header-inner">
      <div class="logo">WECOLLAB</div>

      <!-- Hamburger button (visible on small screens) -->
      <button
        class="hamburger-btn"
        @click="menuOpen = !menuOpen"
        :aria-expanded="menuOpen"
        aria-label="Toggle navigation menu"
      >
        <span class="hamburger-icon" aria-hidden="true"></span>
      </button>

      <!-- Desktop nav -->
      <nav class="nav">
        <!-- Edit Profile (visible when logged in) -->
        <component
          :is="user ? Link : 'span'"
          :href="user ? (user?.permissions?.has_admin_access ? '/settings/profile' : '/profile') : undefined"
          class="icon-btn"
          v-if="user"
          title="Edit Profile"
          aria-label="Edit Profile"
        >
          <User class="w-5 h-5" aria-hidden="true" />
        </component>
        <a
          href="#"
          @click.prevent="$emit('auth')"
          :class="['nav-link', { 'logout-link': user }]"
        >
          {{ user ? 'Log out' : 'Log in' }}
        </a>
        <a href="#" class="nav-link">Deals & Promo</a>
        <component :is="active === 'whatsnew' ? 'span' : Link" :href="active === 'whatsnew' ? undefined : '/whats-new'" class="nav-link" :class="{ active: active === 'whatsnew' }">
          What's NEW?
        </component>
        <component :is="active === 'booking' ? 'span' : Link" :href="active === 'booking' ? undefined : '/booking'" class="nav-link" :class="{ active: active === 'booking' }">
          Booking
        </component>
        <component :is="active === 'home' ? 'span' : Link" :href="active === 'home' ? undefined : '/'" class="nav-link" :class="{ active: active === 'home' }">
          HOME
        </component>
      </nav>

      <!-- Mobile menu -->
      <div v-if="menuOpen" class="mobile-menu">
        <component
          :is="user ? Link : 'span'"
          :href="user ? (user?.permissions?.has_admin_access ? '/settings/profile' : '/profile') : undefined"
          class="nav-link"
          v-if="user"
          title="Edit Profile"
          aria-label="Edit Profile"
          @click="menuOpen = false"
        >
          Edit Profile
        </component>
        <a
          href="#"
          @click.prevent="$emit('auth'); menuOpen = false"
          :class="['nav-link', { 'logout-link': user }]"
        >
          {{ user ? 'Log out' : 'Log in' }}
        </a>
        <a href="#" class="nav-link" @click="menuOpen = false">Deals & Promo</a>
        <component :is="active === 'whatsnew' ? 'span' : Link" :href="active === 'whatsnew' ? undefined : '/whats-new'" class="nav-link" :class="{ active: active === 'whatsnew' }" @click="menuOpen = false">
          What's NEW?
        </component>
        <component :is="active === 'booking' ? 'span' : Link" :href="active === 'booking' ? undefined : '/booking'" class="nav-link" :class="{ active: active === 'booking' }" @click="menuOpen = false">
          Booking
        </component>
        <component :is="active === 'home' ? 'span' : Link" :href="active === 'home' ? undefined : '/'" class="nav-link" :class="{ active: active === 'home' }" @click="menuOpen = false">
          HOME
        </component>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { User } from 'lucide-vue-next'

defineProps<{
  user: any
  active: 'home' | 'booking' | 'whatsnew' | string
}>()

const menuOpen = ref(false)
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
/* Small round icon button for profile */
.icon-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 34px;
  height: 34px;
  border-radius: 9999px;
  background: #ffffff;
  color: #495846;
  text-decoration: none;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
  box-shadow: 0 0 0 1px rgba(0,0,0,0.06) inset;
}
.icon-btn:hover {
  background: #e8efe8;
  color: #2f3b2d;
}
.hamburger-btn {
  display: none; /* shown only on small screens */
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
.logout-link:hover {
  background: #dc2626 !important;
  color: #fff !important;
}

/* Match responsive spacing used site-wide */
@media (max-width: 1200px) {
  .header-inner {
    padding: 0.5rem 1rem;
  }
}
@media (max-width: 900px) {
  .header-inner {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  .nav { display: none; }
  .hamburger-btn { display: inline-flex; align-items: center; justify-content: center; }
}
@media (max-width: 600px) {
  .header-inner { padding: 0.5rem 0.5rem; }
}
</style>
