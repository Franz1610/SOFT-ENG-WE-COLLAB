<template>
  <div class="whats-new-page">
    <AppHeader :user="user" active="whatsnew" @auth="handleAuthAction" />

    <main class="whats-new-content">
      <section class="hero">
        <div class="hero-copy">
          <p class="eyebrow">Fresh on the floor</p>
          <h1>What's New inside WeCollab.42</h1>
          <p>
            Weekly drops covering studio upgrades, fuel-bar experiments, and the community ideas we hear the loudest.
          </p>
          <div class="hero-actions">
            <button class="primary-btn" @click="handleBookClick">Reserve a spot</button>
            <button class="ghost-btn" @click="scrollToEvents">See events calendar</button>
          </div>
          <div class="hero-stats">
            <div v-for="stat in momentumStats" :key="stat.label" class="stat-chip">
              <span class="stat-value">{{ stat.value }}</span>
              <span class="stat-label">{{ stat.label }}</span>
            </div>
          </div>
        </div>
          <div class="hero-media" role="complementary" aria-label="Spotlight updates at a glance">
          <div class="hero-media-inner">
            <p class="mini-label">Week of {{ heroHighlights.week }}</p>
            <ul>
              <li v-for="highlight in heroHighlights.items" :key="highlight">{{ highlight }}</li>
            </ul>
            <button class="ghost-btn" @click="handleAuthAction">
              {{ user ? 'Manage bookings' : 'Sign in to join' }}
            </button>
          </div>
          <div class="ticker" aria-label="Latest quick updates">
            <span v-for="item in tickerItems" :key="item">{{ item }}</span>
          </div>
        </div>
      </section>

      <section class="highlights">
        <div class="section-header">
          <p class="eyebrow">Spotlight</p>
          <h2>Upgrades rolling out right now</h2>
        </div>
        <div class="highlight-grid">
          <article v-for="card in highlightCards" :key="card.title" class="highlight-card">
            <header>
              <p class="card-badge">{{ card.badge }}</p>
              <h3>{{ card.title }}</h3>
            </header>
            <p>{{ card.copy }}</p>
            <ul>
              <li v-for="point in card.points" :key="point">{{ point }}</li>
            </ul>
          </article>
        </div>
      </section>

      <section class="drops">
        <div class="section-header">
          <p class="eyebrow">Release board</p>
          <h2>Fresh moments captured this week</h2>
        </div>
        <div class="drop-grid">
          <article v-for="drop in latestDrops" :key="drop.title" class="drop-card">
            <div class="drop-meta">
              <span class="tag">{{ drop.tag }}</span>
              <span class="time">{{ drop.time }}</span>
            </div>
            <h3>{{ drop.title }}</h3>
            <p>{{ drop.copy }}</p>
          </article>
        </div>
      </section>

      <section class="events" ref="eventsSection">
        <div class="section-header">
          <p class="eyebrow">Calendar</p>
          <h2>Community sessions you can sign up for</h2>
        </div>
        <div class="event-list">
          <article v-for="event in upcomingEvents" :key="event.title" class="event-card">
            <div>
              <p class="event-date">{{ event.date }}</p>
              <h3>{{ event.title }}</h3>
              <p>{{ event.description }}</p>
            </div>
            <span class="event-status" :class="event.status.toLowerCase()">{{ event.status }}</span>
          </article>
        </div>
      </section>

      <section class="menu">
        <div class="section-header">
          <p class="eyebrow">Fuel bar</p>
          <h2>Limited-run menu collabs</h2>
        </div>
        <div class="menu-grid">
          <article v-for="item in featuredItems" :key="item.name" class="menu-card">
            <div class="menu-accent" :style="{ background: item.accent }"></div>
            <div class="menu-info">
              <p class="menu-label">{{ item.tag }}</p>
              <h3>{{ item.name }}</h3>
              <p>{{ item.description }}</p>
            </div>
            <p class="price">{{ item.price }}</p>
          </article>
        </div>
      </section>

      <section class="community">
        <div class="section-header">
          <p class="eyebrow">Community board</p>
          <h2>Shout-outs from regulars</h2>
        </div>
        <ul class="community-list">
          <li v-for="update in communityUpdates" :key="update.name">
            <p class="community-name">{{ update.name }}</p>
            <p class="community-handle">{{ update.handle }}</p>
            <p>{{ update.note }}</p>
          </li>
        </ul>
      </section>

      <section class="cta-panel">
        <div>
          <p class="eyebrow">Join the flow</p>
          <h2>Theme weeks fill up fast. Hold your slot before they vanish.</h2>
        </div>
        <div class="hero-actions">
          <button class="primary-btn" @click="handleBookClick">Book workstation</button>
          <button class="ghost-btn" @click="handleAuthAction">
            {{ user ? 'Switch plans' : 'Create account' }}
          </button>
        </div>
      </section>
    </main>

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
          <Button @click="confirmLogout" class="flex-1 text-white border-none logout-btn">
            Logout
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/components/AppHeader.vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const showLogoutModal = ref(false)
const eventsSection = ref<HTMLElement | null>(null)

const tickerItems = [
  'Studio A sound blankets installed',
  'New partner pop-up this Saturday',
  'Weeknight passes now bundle cold brew refills',
  'Lo-fi Sundays stretch until midnight',
  'Pandan cold brew refill promo active',
]

const momentumStats = [
  { label: 'slots held today', value: '42' },
  { label: 'events this week', value: '5' },
  { label: 'new members', value: '+18' },
]

const heroHighlights = {
  week: 'Sept 16',
  items: ['Lo-fi Sundays until midnight', 'New ergonomic chairs', 'Focus pods at 80% capacity'],
}

const latestDrops = [
  {
    tag: 'After hours',
    title: 'Lo-fi Sundays stretch to midnight',
    time: '8 hrs ago',
    copy: 'We added mellow lighting presets and soft seat toppers in Studio B for the late crowd.',
  },
  {
    tag: 'Collab',
    title: 'Bakwit Lab pop-up takes over the patio',
    time: 'Tomorrow',
    copy: 'Bring a tote. Their art zines and risograph postcards are moving fast.',
  },
  {
    tag: 'Upgrade log',
    title: 'Locker automation pilot expands',
    time: 'This week',
    copy: 'Scan-to-open lockers now sit beside the pantry doors with 30 total bays.',
  },
]

const highlightCards = [
  {
    badge: 'Now live',
    title: 'Extended quiet hours',
    copy: 'Weekday silence blocks now run from 8 AM to 2 PM so you can hold calls in peace.',
    points: ['Book through /booking', 'Noise level checks every 30 minutes', 'Headsets on request'],
  },
  {
    badge: 'Beta',
    title: 'Locker automation',
    copy: 'Scan your QR receipt to open the new smart lockers beside the pantry doors.',
    points: ['30 lockers available', 'Auto-release after 24 hours', 'Message us for large gear'],
  },
  {
    badge: 'Just dropped',
    title: 'Resident mentor hours',
    copy: 'Volunteer mentors host office hours for early-stage founders every Wednesday night.',
    points: ['Free for monthly members', 'Slots released in Discord', 'Hybrid setup via Meet'],
  },
]

const upcomingEvents = [
  {
    date: 'Sept 20 - 7 PM',
    title: 'Night Shift Build Circle',
    description: 'Bring your side project and co-build with other solo founders.',
    status: 'Open',
  },
  {
    date: 'Sept 23 - 2 PM',
    title: 'Community Skill Swap',
    description: 'Lightning talks on pitching, editing, and storytelling.',
    status: 'Filling',
  },
  {
    date: 'Sept 28 - 9 AM',
    title: 'Weekend Creator Market',
    description: 'Pop-up booths featuring indie coffee, planners, and studio merch drops.',
    status: 'Waitlist',
  },
]

const featuredItems = [
  {
    tag: 'Collab brew',
    name: 'Pandan Cold Foam Latte',
    description: 'House cold brew topped with lightly sweet pandan cream and a free refill.',
    price: 'Php 190',
    accent: 'linear-gradient(135deg, #9be89b, #4b824b)',
  },
  {
    tag: 'Pastry lab',
    name: 'Calamansi Olive Oil Loaf',
    description: 'Bright citrus loaf baked in micro batches every morning.',
    price: 'Php 140',
    accent: 'linear-gradient(135deg, #fcd9b8, #f7a35c)',
  },
  {
    tag: 'Plant-based',
    name: 'Tempeh Banh Mi Bowl',
    description: 'Pickled veggies with grilled tempeh and chili aioli crunch.',
    price: 'Php 260',
    accent: 'linear-gradient(135deg, #b8e1ff, #6fb3ff)',
  },
]

const communityUpdates = [
  {
    name: 'Mai of @studio.muse',
    handle: '@studio.muse',
    note: 'Booked the conference room for a 10-person pitch with zero HDMI hiccups.',
  },
  {
    name: 'Raf and Lea',
    handle: '@rafandlea',
    note: 'Thanks for the later curfew. Our podcast edits usually wrap past midnight.',
  },
  {
    name: 'Coach Irvin',
    handle: '@coach.irvin',
    note: 'Mentorship booth is back-to-back every Wednesday. Loving the new seats.',
  },
]

function handleAuthAction() {
  if (user.value) {
    showLogoutModal.value = true
    return
  }
  router.visit('/login')
}

function closeLogoutModal() {
  showLogoutModal.value = false
}

function confirmLogout() {
  showLogoutModal.value = false
  router.post('/logout')
}

function handleBookClick() {
  router.visit(user.value ? '/booking' : '/login')
}

function scrollToEvents() {
  eventsSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}
</script>

<style scoped>
.whats-new-page {
  background: #f4f1e8;
  min-height: 100vh;
}

.whats-new-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 100px 2rem 4rem;
  display: flex;
  flex-direction: column;
  gap: 3rem;
}

.hero {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2.5rem;
  align-items: center;
  background: radial-gradient(circle at top left, #ffffff 0%, #f0f3ea 35%, #e8efe2 100%);
  border-radius: 28px;
  padding: 2.5rem;
  box-shadow: 0 30px 60px rgba(28, 42, 26, 0.12);
}

.hero-copy h1 {
  font-size: 3.25rem;
  margin: 0.4rem 0 0.8rem;
  color: #20291f;
}

.hero-copy p {
  color: #3c3c3c;
  line-height: 1.6;
}

.hero-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 1.5rem;
}

.hero-stats {
  margin-top: 1.5rem;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 0.75rem;
}

.stat-chip {
  background: rgba(75, 130, 75, 0.08);
  border: 1px solid rgba(75, 130, 75, 0.15);
  border-radius: 16px;
  padding: 0.75rem 1rem;
}

.stat-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: #1f2a1f;
  display: block;
}

.stat-label {
  text-transform: uppercase;
  font-size: 0.7rem;
  letter-spacing: 0.2em;
  color: #6a7a6a;
}

 .hero-media {
  background: #111915;
  border-radius: 24px;
  padding: 1.5rem;
  box-shadow: 0 35px 50px rgba(0, 0, 0, 0.28);
  color: #f5f5f0;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  overflow: hidden;
}

.hero-media-inner {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.mini-label {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.75rem;
  color: #9be89b;
}

.hero-media ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.6rem;
}

.hero-media li::before {
  content: '- ';
  color: #9be89b;
}

.ticker {
  display: flex;
  gap: 2rem;
  color: #d9f6d9;
  font-size: 0.85rem;
  white-space: nowrap;
  animation: ticker-scroll 18s linear infinite;
}

.ticker span {
  position: relative;
  padding-left: 1.25rem;
}

.ticker span::before {
  content: '•';
  position: absolute;
  left: 0;
  color: #9be89b;
}

@keyframes ticker-scroll {
  0% {
    transform: translateX(0);
  }
  100% {
    transform: translateX(-50%);
  }
}

.highlight-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.5rem;
}

.highlight-card {
  background: #fff;
  border-radius: 20px;
  padding: 1.5rem;
  box-shadow: 0 18px 35px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
}

.highlight-card ul {
  list-style: disc inside;
  margin: 0;
  color: #555;
}

.card-badge {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  color: #4b824b;
}

.section-header {
  text-align: center;
  max-width: 720px;
  margin: 0 auto 1.5rem;
}

.section-header h2 {
  margin-top: 0.5rem;
}

.events .event-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.event-card {
  background: #ffffff;
  border-radius: 18px;
  padding: 1.5rem;
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  border: 1px solid rgba(73, 88, 70, 0.12);
}

.event-date {
  font-weight: 600;
  color: #4b824b;
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.85rem;
}

.event-status {
  align-self: center;
  background: #4b824b;
  color: #fff;
  padding: 0.4rem 0.9rem;
  border-radius: 999px;
  font-size: 0.85rem;
}

.event-status.waitlist {
  background: #b45309;
}

.event-status.filling {
  background: #15803d;
}
.drops {
  background: #ffffff;
  border-radius: 32px;
  padding: 2.5rem;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.08);
}

.drop-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.drop-card {
  border: 1px solid rgba(73, 88, 70, 0.12);
  border-radius: 20px;
  padding: 1.25rem;
  background: #f9fbf7;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.drop-meta {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #6d7b63;
}

.drop-card .tag {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.7rem;
}

.drop-card h3 {
  margin: 0;
  font-size: 1.25rem;
  color: #1f2a1f;
}

.menu-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.menu-card {
  background: #131b13;
  color: #f3f3f0;
  border-radius: 24px;
  padding: 1.25rem;
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 1rem;
  align-items: center;
  box-shadow: 0 25px 45px rgba(0, 0, 0, 0.25);
}

.menu-accent {
  width: 48px;
  height: 48px;
  border-radius: 16px;
}

.menu-label {
  text-transform: uppercase;
  letter-spacing: 0.2em;
  font-size: 0.75rem;
  color: #9be89b;
}

.menu-card .price {
  font-weight: 600;
  color: #9be89b;
  font-size: 1.1rem;
}

.community-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
}

.community-list li {
  background: #ffffff;
  border-radius: 18px;
  padding: 1.25rem;
  box-shadow: 0 12px 32px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.community-name {
  font-weight: 600;
  margin-bottom: 0.25rem;
}

.community-handle {
  font-size: 0.85rem;
  color: #798b79;
  margin: 0;
}

.cta-panel {
  background: #4b824b;
  color: #fff;
  border-radius: 24px;
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.primary-btn,
.ghost-btn {
  border-radius: 999px;
  padding: 0.85rem 2rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: transform 0.15s ease, opacity 0.15s ease;
}

.primary-btn {
  background: #4b824b;
  color: #fff;
}

.ghost-btn {
  background: transparent;
  color: #4b824b;
  border: 1px solid rgba(75, 130, 75, 0.5);
}

.cta-panel .primary-btn {
  background: #fff;
  color: #4b824b;
}

.eyebrow {
  font-size: 0.85rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #7f8b7f;
}

.logout-btn {
  background: #b91c1c;
}

@media (max-width: 640px) {
  .whats-new-content {
    padding: 90px 1.25rem 3rem;
  }

  .event-card {
    flex-direction: column;
  }
}
</style>
