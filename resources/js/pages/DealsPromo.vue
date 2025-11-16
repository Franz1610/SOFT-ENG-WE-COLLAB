<template>
  <div class="promo-page">
    <AppHeader :user="user" active="deals" @auth="handleAuthAction" />

    <main class="promo-content">
      <section class="hero">
        <div class="hero-copy">
          <p class="eyebrow">Limited-time savings</p>
          <h1>Deals & Promo</h1>
          <p class="hero-text">
            Stretch every hour inside WeCollab.42. Pick a table, phone booth, or conference room and
            lock in the same friendly rates featured in our in-store posters.
          </p>
          <div class="hero-cta">
            <button class="primary-btn" @click="handleBookClick">Book a Slot</button>
            <button class="ghost-btn" @click="scrollToRates">See Rate Table</button>
          </div>
        </div>
        <div class="hero-poster" aria-label="Promo rates poster">
          <img src="/images/booking/promo_rates.jpg" alt="Promo rates poster" loading="lazy" />
          <span class="poster-caption">Direct scan from our in-cafe display</span>
        </div>
      </section>

      <section class="rates" ref="ratesSection">
        <div class="section-header">
          <p class="eyebrow">Transparent pricing</p>
          <h2>Pick a setup that matches your session length</h2>
          <p>
            Every duration below mirrors the promo board, so you can compare options before you arrive.
          </p>
        </div>
        <div class="rate-grid">
          <article
            v-for="category in promoCategories"
            :key="category.key"
            class="rate-card"
          >
            <div class="card-banner">
              <p>{{ category.category }}</p>
            </div>
            <div class="card-body">
              <header>
                <h3>{{ category.title }}</h3>
                <p class="category-description">{{ category.description }}</p>
              </header>
              <ul class="rates-list">
                <li v-for="rate in category.rates" :key="rate.label" class="rate-row">
                  <div>
                    <span class="rate-label">{{ rate.label }}</span>
                    <p v-if="rate.caption" class="rate-caption">{{ rate.caption }}</p>
                  </div>
                  <span class="rate-price">{{ rate.price }}</span>
                </li>
              </ul>
            </div>
          </article>
        </div>
      </section>

      <section class="perks">
        <div class="section-header">
          <p class="eyebrow">Why book early?</p>
          <h2>Perks bundled with every promo slot</h2>
        </div>
        <div class="perk-grid">
          <article v-for="perk in perks" :key="perk.title" class="perk-card">
            <h3>{{ perk.title }}</h3>
            <p>{{ perk.body }}</p>
          </article>
        </div>
      </section>

      <section class="cta-panel">
        <div>
          <p class="eyebrow">Ready when you are</p>
          <h2>Reserve a table, booth, or full room in under two minutes.</h2>
        </div>
        <button class="primary-btn" @click="handleBookClick">Start a Booking</button>
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
import { getDurationOptions, computePrice, formatPHP } from '@/utils/promo'

const page = usePage()
const user = computed(() => page.props.auth?.user)
const showLogoutModal = ref(false)
const ratesSection = ref(null)

const promoCategories = [
  {
    key: 'common',
    category: 'Regular Tables',
    title: 'Flexible solo or duo seating',
    description: 'Perfect for heads-down work, quick consultations, or study groups.',
    highlight: 'All sessions include power outlets and unlimited water refills.',
    rates: getDurationOptions('common', 8).map((hours) => ({
      label: `${hours} ${hours === 1 ? 'Hour' : 'Hours'}`,
      price: formatPrice('common', hours),
    })),
  },
  {
    key: 'individual',
    category: 'Phone Booth Rooms',
    title: 'Private calls without distractions',
    description: 'Sound-dampened booths for online classes, interviews, or coaching.',
    highlight: 'Best paired with our unlimited coffee add-ons for deep focus.',
    rates: getDurationOptions('individual', 4).map((hours) => ({
      label: `${hours} ${hours === 1 ? 'Hour' : 'Hours'}`,
      price: formatPrice('individual', hours),
    })),
  },
  {
    key: 'master',
    category: 'Conference Rooms',
    title: 'Pitch, train, or celebrate',
    description: 'Spacious rooms with whiteboards and HDMI-ready displays.',
    highlight: 'Flat hourly pricing depending on the headcount you need to host.',
    rates: [
      { label: 'Up to 6 pax', price: 'Php 200 / hr' },
      { label: 'Up to 10 pax', price: 'Php 300 / hr' },
    ],
  },
]

const perks = [
  {
    title: 'No surprise fees',
    body: 'What you see is what you pay—VAT and service charges are already factored into our poster rates.',
  },
  {
    title: 'Complimentary amenities',
    body: 'Fast Wi-Fi, secured lockers, and community managers on standby during your booking.',
  },
  {
    title: 'Easy rescheduling',
    body: 'Need to move your slot? Adjust your booking online at least 24 hours before your visit.',
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

function scrollToRates() {
  ratesSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' })
}

function formatPrice(category, hours) {
  const price = computePrice(category, hours)
  return price == null ? '—' : formatPHP(price)
}
</script>

<style scoped>
.promo-page {
  background: #f6f2ea;
  min-height: 100vh;
}

.promo-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 100px 2rem 4.5rem;
  display: flex;
  flex-direction: column;
  gap: 4rem;
}

.hero {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2.5rem;
  align-items: center;
  background: radial-gradient(circle at top left, #ffffff 0%, #ece8db 45%, #e4dfd2 100%);
  border-radius: 28px;
  padding: 2.5rem;
  box-shadow: 0 35px 60px rgba(48, 61, 42, 0.15);
}

.hero-copy h1 {
  font-size: 3.5rem;
  margin: 0.5rem 0;
  color: #1f2a1f;
}

.hero-copy .eyebrow {
  margin-bottom: 0.5rem;
}

.hero-text {
  font-size: 1.1rem;
  color: #3a3a3a;
  line-height: 1.6;
}

.hero-cta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin-top: 1.75rem;
}

.primary-btn,
.ghost-btn {
  border-radius: 999px;
  padding: 0.85rem 2rem;
  font-size: 1rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.primary-btn {
  background: #4b824b;
  color: #fff;
  box-shadow: 0 12px 30px rgba(75, 130, 75, 0.25);
}

.primary-btn:hover {
  transform: translateY(-2px);
}

.ghost-btn {
  background: transparent;
  color: #4b824b;
  border: 1px solid rgba(75, 130, 75, 0.4);
}


.hero-poster {
  background: #1d241d;
  border-radius: 24px;
  padding: 1.75rem;
  text-align: center;
  box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
}

.hero-poster img {
  width: 100%;
  border-radius: 16px;
  object-fit: cover;
}

.poster-caption {
  display: inline-block;
  margin-top: 0.75rem;
  color: #d9d7cf;
  font-size: 0.9rem;
}

.section-header {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-bottom: 2rem;
  max-width: 640px;
}

.section-header h2 {
  margin: 0.5rem 0;
  font-size: 2.25rem;
  color: #1f2a1f;
}

.eyebrow {
  font-size: 0.95rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #7f8b7f;
}

.rate-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
  gap: 2rem;
}

.rate-card {
  position: relative;
  background: linear-gradient(180deg, #ffffff 0%, #fbf8ef 100%);
  border-radius: 30px;
  overflow: hidden;
  box-shadow: 0 25px 60px rgba(0, 0, 0, 0.08);
  display: flex;
  flex-direction: column;
  border: 1px solid rgba(73, 88, 70, 0.08);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.rate-card::after {
  content: '';
  position: absolute;
  inset: 0;
  pointer-events: none;
  border-radius: inherit;
  opacity: 0;
  box-shadow: inset 0 0 0 2px rgba(75, 130, 75, 0.15);
  transition: opacity 0.3s ease;
}

.rate-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 35px 70px rgba(41, 54, 41, 0.18);
}

.rate-card:hover::after {
  opacity: 1;
}

.card-body {
  padding: 1.85rem;
  display: flex;
  flex-direction: column;
  gap: 1.15rem;
}

.card-body header {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
  padding: 0;
  background: transparent;
}

.card-body header h3 {
  margin: 0;
  font-size: 1.45rem;
  color: #233223;
  line-height: 1.3;
}

.card-body header p {
  margin: 0;
  color: #585858;
  font-size: 0.97rem;
}

.category-description {
  color: #445044;
  font-size: 0.96rem;
  margin-top: 0.4rem;
  line-height: 1.4;
}

.rates-list {
  list-style: none;
  padding: 0;
  margin: 0;
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.rate-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.85rem 0.25rem;
  border-bottom: 1px solid rgba(73, 88, 70, 0.12);
}

.rate-row:last-child {
  border-bottom: none;
}


.rate-label {
  font-weight: 600;
  color: #1f2a1f;
  letter-spacing: 0.01em;
}

.rate-caption {
  margin: 0.35rem 0 0;
  color: #7a7a7a;
  font-size: 0.85rem;
  line-height: 1.35;
}

.rate-price {
  font-weight: 700;
  color: #376237;
  font-size: 1.2rem;
}

.perks {
  margin-bottom: 4rem;
}


.perk-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1.5rem;
}

.perk-card {
  background: #1d271d;
  color: #f6f4eb;
  border-radius: 24px;
  padding: 1.75rem;
  min-height: 180px;
  box-shadow: 0 25px 55px rgba(0, 0, 0, 0.25);
}

.perk-card h3 {
  margin-bottom: 0.75rem;
}


.cta-panel {
  background: #4b824b;
  color: #fff;
  border-radius: 32px;
  padding: 2.5rem;
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  align-items: flex-start;
  box-shadow: 0 30px 60px rgba(26, 48, 26, 0.25);
}

.cta-panel .primary-btn {
  background: #fff;
  color: #4b824b;
  box-shadow: none;
}

.logout-btn {
  background: #b91c1c;
}

@media (max-width: 640px) {
  .promo-content {
    padding: 90px 1.25rem 3rem;
  }

  .hero-copy h1 {
    font-size: 2.5rem;
  }

  .cta-panel {
    text-align: left;
  }
}
</style>
