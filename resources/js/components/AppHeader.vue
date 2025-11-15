<template>
  <header class="header sticky-header">
    <div class="header-inner">
      <Link :href="'/'" class="logo" aria-label="Go to homepage">
        WECOLLAB
      </Link>

      <!-- Hamburger button (visible on small screens) -->
      <button
        class="hamburger-btn"
        @click="toggleMobileMenu"
        :aria-expanded="menuOpen ? 'true' : 'false'"
        aria-label="Toggle navigation menu"
      >
        <span class="hamburger-icon" aria-hidden="true"></span>
      </button>

      <!-- Desktop nav -->
      <nav class="nav">
        <component
          :is="active === 'deals' ? 'span' : Link"
          :href="active === 'deals' ? undefined : '/deals'"
          class="nav-link"
          :class="{ active: active === 'deals' }"
        >
          Deals & Promo
        </component>
        <component :is="active === 'whatsnew' ? 'span' : Link" :href="active === 'whatsnew' ? undefined : '/whats-new'" class="nav-link" :class="{ active: active === 'whatsnew' }">
          What's NEW?
        </component>
        <component
          :is="active === 'booking' ? 'span' : Link"
          :href="active === 'booking' ? undefined : '/booking'"
          class="nav-link with-icon"
          :class="{ active: active === 'booking' }"
        >
          <CalendarDays class="icon" aria-hidden="true" />
          <span>Booking</span>
        </component>
        <!-- Account menu (visible when logged in) -->
        <div v-if="user" class="profile-menu-wrapper" ref="profileMenuRef">
          <button
            type="button"
            class="icon-btn"
            @click="toggleProfileMenu"
            :aria-expanded="profileMenuOpen ? 'true' : 'false'"
            aria-haspopup="true"
            title="Account menu"
            aria-label="Account menu"
          >
            <User class="w-5 h-5" aria-hidden="true" />
          </button>
          <div
            v-if="profileMenuOpen"
            class="profile-dropdown"
            role="menu"
            :style="profileDropdownStyles"
          >
            <Link
              :href="user?.permissions?.has_admin_access ? '/settings/profile' : '/profile'"
              class="profile-dropdown-item"
              role="menuitem"
              @click="closeProfileMenu"
            >
              <User class="icon" aria-hidden="true" />
              <span>Edit Profile</span>
            </Link>
            <button
              type="button"
              class="profile-dropdown-item logout"
              role="menuitem"
              @click="handleLogoutClick"
            >
              <LogOut class="icon" aria-hidden="true" />
              <span>Log out</span>
            </button>
          </div>
        </div>
        <a
          v-else
          href="#"
          @click.prevent="emit('auth')"
          class="nav-link with-icon"
        >
          <LogIn class="icon" aria-hidden="true" />
          <span>Log in</span>
        </a>
      </nav>

      <!-- Mobile menu -->
      <div v-if="menuOpen" class="mobile-menu">
        <div v-if="user" class="mobile-profile-group">
          <Link
            :href="user?.permissions?.has_admin_access ? '/settings/profile' : '/profile'"
            class="nav-link"
            title="Edit Profile"
            aria-label="Edit Profile"
            @click="menuOpen = false"
          >
            Edit Profile
          </Link>
          <button
            type="button"
            class="nav-link with-icon logout-link"
            @click="handleMobileLogout"
          >
            <LogOut class="icon" aria-hidden="true" />
            <span>Log out</span>
          </button>
        </div>
        <a
          v-else
          href="#"
          @click.prevent="handleMobileLogin"
          class="nav-link with-icon"
        >
          <LogIn class="icon" aria-hidden="true" />
          <span>Log in</span>
        </a>
        <component
          :is="active === 'deals' ? 'span' : Link"
          :href="active === 'deals' ? undefined : '/deals'"
          class="nav-link"
          :class="{ active: active === 'deals' }"
          @click="menuOpen = false"
        >
          Deals & Promo
        </component>
        <component :is="active === 'whatsnew' ? 'span' : Link" :href="active === 'whatsnew' ? undefined : '/whats-new'" class="nav-link" :class="{ active: active === 'whatsnew' }" @click="menuOpen = false">
          What's NEW?
        </component>
        <component
          :is="active === 'booking' ? 'span' : Link"
          :href="active === 'booking' ? undefined : '/booking'"
          class="nav-link with-icon"
          :class="{ active: active === 'booking' }"
          @click="menuOpen = false"
        >
          <CalendarDays class="icon" aria-hidden="true" />
          <span>Booking</span>
        </component>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { User, CalendarDays, LogIn, LogOut } from 'lucide-vue-next'

defineProps<{
  user: any
  active: 'home' | 'booking' | 'whatsnew' | 'deals' | string
}>()

const emit = defineEmits<{ (e: 'auth'): void }>()

const menuOpen = ref(false)
const profileMenuOpen = ref(false)
const profileMenuRef = ref<HTMLElement | null>(null)
const profileDropdownStyles = ref<{ top: string; right: string }>({ top: '0px', right: '0px' })

function setProfileDropdownPosition() {
  const wrapper = profileMenuRef.value
  if (!wrapper) return
  const rect = wrapper.getBoundingClientRect()
  profileDropdownStyles.value = {
    top: `${rect.bottom + 12}px`,
    right: `${Math.max(window.innerWidth - rect.right, 0)}px`,
  }
}

function toggleMobileMenu() {
  menuOpen.value = !menuOpen.value
  profileMenuOpen.value = false
}

function toggleProfileMenu() {
  if (profileMenuOpen.value) {
    profileMenuOpen.value = false
    return
  }
  setProfileDropdownPosition()
  profileMenuOpen.value = true
}

function closeProfileMenu() {
  profileMenuOpen.value = false
}

function handleLogoutClick() {
  closeProfileMenu()
  emit('auth')
}

function handleMobileLogout() {
  menuOpen.value = false
  emit('auth')
}

function handleMobileLogin() {
  menuOpen.value = false
  emit('auth')
}

function handleDocumentClick(event: MouseEvent) {
  if (!profileMenuOpen.value) return
  const menuEl = profileMenuRef.value
  if (!menuEl) return
  if (menuEl.contains(event.target as Node)) return
  profileMenuOpen.value = false
}

// Close the floating menu as soon as the page scrolls to avoid it sticking on screen.
function handleWindowScroll() {
  if (!profileMenuOpen.value) return
  profileMenuOpen.value = false
}

function handleWindowResize() {
  if (!profileMenuOpen.value) return
  setProfileDropdownPosition()
}

onMounted(() => {
  document.addEventListener('click', handleDocumentClick)
  window.addEventListener('scroll', handleWindowScroll, { passive: true })
  window.addEventListener('resize', handleWindowResize)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleDocumentClick)
  window.removeEventListener('scroll', handleWindowScroll)
  window.removeEventListener('resize', handleWindowResize)
})
</script>

<style scoped>
.header {
  background: #495846;
  color: #fff;
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  z-index: 100;
  box-shadow: none !important;
  border-bottom: none !important;
}
.header-inner {
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1200px;
  margin: 0 auto;
  padding: 0.5rem 2rem;
  min-height: 54px;
  width: 100%;
}
.logo {
  font-weight: bold;
  letter-spacing: 0.1em;
  font-size: 1.5rem;
  line-height: 1;
  color: #fff;
  text-decoration: none;
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
  background: transparent;
  color: #ffffff;
  text-decoration: none;
  transition: background 0.2s, color 0.2s, box-shadow 0.2s;
  border: 1px solid rgba(255, 255, 255, 0.4);
}
.icon-btn:hover {
  background: #ffffff;
  color: #495846;
}
.profile-menu-wrapper {
  position: relative;
}
.profile-dropdown {
  position: fixed;
  min-width: 180px;
  background: #ffffff;
  color: #1f2937;
  border-radius: 12px;
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
  padding: 0.4rem 0;
  z-index: 250;
}
.profile-dropdown-item {
  display: flex;
  align-items: center;
  width: 100%;
  background: transparent;
  border: none;
  text-align: left;
  padding: 0.55rem 1rem;
  font-size: 0.95rem;
  color: inherit;
  text-decoration: none;
  cursor: pointer;
  gap: 0.5rem;
}
.profile-dropdown-item .icon {
  width: 18px;
  height: 18px;
}
.profile-dropdown-item:hover {
  background: #f1f5f1;
  color: #2f3b2d;
}
.profile-dropdown-item.logout {
  color: #b91c1c;
}
.profile-dropdown-item.logout:hover {
  background: #fee2e2;
  color: #991b1b;
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
.mobile-profile-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
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
.with-icon {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
}
.with-icon .icon {
  width: 18px;
  height: 18px;
}
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  border: 0;
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
