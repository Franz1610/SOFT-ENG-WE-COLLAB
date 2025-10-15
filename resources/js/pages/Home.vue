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
            <Link href="/booking" class="nav-link">Booking</Link>
          <button class="home-btn">HOME</button>
        </nav>
      </div>
    </header>
    <main class="main-content">
      <div class="hero">
        <div class="hero-center">
          <img src="/images/homepage/logo.png" alt="Logo" class="hero-logo-large" />
          <div class="hero-title">We collab</div>
          <div class="hero-tagline">CO-WORKING SPACE & CAFE</div>
        </div>
      </div>

      <!-- The Wecollab Story Section -->
      <section style="background: #f7f5ed; padding: 64px 0; display: flex; justify-content: center;">
        <div style="max-width: 1200px; width: 100%; display: flex; flex-direction: row; gap: 48px; align-items: flex-start; background: #f7f5ed; border-radius: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.06); padding: 48px;">
          <img src="/images/homepage/home1.png" alt="Wecollab Story" style="width: 400px; height: 400px; object-fit: cover; border-radius: 24px; box-shadow: 0 4px 16px rgba(0,0,0,0.10);">
          <div style="flex: 1; display: flex; flex-direction: column; justify-content: center;">
            <h2 style="font-size: 2.5rem; font-weight: bold; margin-bottom: 28px; color: #232323;">The Wecollab Story</h2>
            <p style="font-size: 1.35rem; color: #232323; margin-bottom: 32px; line-height: 1.7;">
              Founded on August 28, 2024, WeCollab.42 has quickly become known for its quiet and welcoming atmosphere. It’s a nice place where visitors can concentrate and accomplish their goals without distractions.
            </p>
            <a href="#" style="display: inline-block; background: #38613a; color: #fff; padding: 18px 40px; border-radius: 10px; font-size: 1.15rem; font-weight: 600; text-decoration: none; width: fit-content;">Learn More About Us</a>
          </div>
        </div>
      </section>

      <!-- Branch Section -->
      <section style="background: #f7f5ed; padding: 64px 0 80px 0; display: flex; flex-direction: column; align-items: center;">
        <h2 style="font-size: 2.5rem; font-weight: bold; color: #232323; margin-bottom: 48px;">BRANCH</h2>
        <div style="display: flex; flex-direction: row; gap: 48px; justify-content: center; align-items: flex-start; width: 100%; max-width: 1100px;">
          <div style="display: flex; flex-direction: column; align-items: center;">
            <img src="/images/homepage/branch.png" alt="We collab Jacinto" style="width: 420px; height: 280px; object-fit: cover; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.10); margin-bottom: 18px;">
            <div style="font-size: 1.25rem; font-weight: 600; color: #232323; margin-bottom: 8px; text-align: center;">We collab Jacinto</div>
            <div style="font-size: 1.05rem; color: #888; text-align: center;">
              2F, Remfield Bldg. Juan, J. Dela Cruz St,<br>
              Poblacion District, Davao City, Davao del Sur
            </div>
          </div>
          <div>
            <img src="/images/homepage/map.png" alt="Map to WeCollab.42" style="width: 420px; height: 280px; object-fit: cover; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.10);">
          </div>
        </div>
      </section>

      <!-- Reviews Section -->
      <section id="reviews" style="background: #f7f5ed; padding: 64px 0;">
        <div style="max-width: 1100px; margin: 0 auto;">
          <h2 style="text-align: center; font-size: 2.5rem; font-weight: bold; color: #232323; margin-bottom: 40px;">REVIEWS</h2>
          <div class="carousel-wrapper" style="position: relative; overflow: visible;">
            <!-- Carousel Slides -->
            <div class="carousel-slides-container">
              <div class="carousel-track" style="display: flex; align-items: center; justify-content: center;">
                <div
                  v-for="(review, idx) in visibleSlides"
                  :key="idx"
                  class="review-card"
                  :style="getCardStyle(idx)"
                >
                  <template v-if="review">
                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                      <img :src="review.profile" alt="profile" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; margin-right: 12px; border: 2px solid #eee;">
                      <div>
                        <div style="font-weight: 600; font-size: 1.1rem;">{{ review.name }}</div>
                        <div style="font-size: 0.95rem; color: #888;">1 review · 1 photo</div>
                      </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                      <span v-for="n in 5" :key="n" style="color: #ffc107; font-size: 1.15rem;">★</span>
                      <span style="font-size: 0.95rem; color: #888; margin-left: 8px;">{{ review.date }}</span>
                    </div>
                    <div style="font-size: 1.15rem; font-weight: 500; margin-bottom: 10px; margin-top: 8px;">“{{ review.text }}”</div>
                    <img :src="review.photo" alt="review" style="width: 100%; height: 170px; object-fit: cover; border-radius: 10px; margin-top: 10px;">
                  </template>
                </div>
              </div>
            </div>
            <!-- Carousel Controls and Dots Below -->
            <div style="display: flex; flex-direction: column; align-items: center; margin-top: 32px;">
              <div style="display: flex; align-items: center; gap: 32px;">
                <button @click="prev"
                  style="background: none; border: none; font-size: 2rem; color: #495846; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;"
                  :disabled="currentIndex === 0"
                  :style="{ opacity: currentIndex === 0 ? 0.4 : 1 }"
                >
                  <span>&larr;</span>
                </button>
                <div style="display: flex; gap: 12px;">
                  <span v-for="(review, idx) in reviews" :key="idx"
                    :style="{
                      width: '12px',
                      height: '12px',
                      borderRadius: '50%',
                      background: idx === currentIndex ? '#495846' : '#d2d2d2',
                      display: 'inline-block',
                      cursor: 'pointer'
                    }"
                    @click="goTo(idx)">
                  </span>
                </div>
                <button @click="next"
                  style="background: none; border: none; font-size: 2rem; color: #495846; cursor: pointer; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; transition: background 0.2s;"
                  :disabled="currentIndex === reviews.length - 1"
                  :style="{ opacity: currentIndex === reviews.length - 1 ? 0.4 : 1 }"
                >
                  <span>&rarr;</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>
      <!-- End Reviews Section -->

      <!-- Floating Create Survey button -->
      <button
        class="create-survey-btn"
        @click="createSurvey"
        aria-label="Create a Survey"
      >
        <span class="plus">+</span>
        <span class="label">Create a Survey!</span>
      </button>
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
html, body {
  width: 100%;
  max-width: 100vw;
  overflow-x: hidden !important;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

section, .main-content, .header, .header-inner, .hero, .review-carousel-container {
  width: 100% !important;
  max-width: 100vw !important;
  overflow-x: hidden;
  box-sizing: border-box;
}
html, body, #app, .main-content {
  width: 100vw;
  max-width: 100vw;
  overflow-x: hidden !important;
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

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
.logout-link:hover {
  background: #dc2626 !important;
  color: #fff !important;
}
.logout-link:hover {
  background: #dc2626 !important;
  color: #fff !important;
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

section, .hero {
  width: 100vw !important;
  max-width: 100vw !important;
  overflow-x: hidden;
  box-sizing: border-box;
}

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
  .main-content {
    padding: 0;
  }
  section, .hero {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
}

@media (max-width: 600px) {
  .header-inner {
    padding: 0.5rem 0.5rem;
  }
  .main-content {
    padding: 0;
  }
  section, .hero {
    padding-left: 0 !important;
    padding-right: 0 !important;
  }
}
  .hero {
    position: relative;
    width: 100vw;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background-image: url('/images/homepage/hero.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: scroll;
  }
  .hero-center {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    width: 100vw;
    min-height: 80vh;
    color: #fff;
    text-align: center;
  }
  .hero-logo-large {
    width: 140px;
    height: 140px;
    margin-bottom: 2rem;
    object-fit: contain;
  }
  .hero-title {
    font-size: 3rem;
    font-weight: bold;
    margin-bottom: 1rem;
    letter-spacing: 0.04em;
    color: #fff;
    text-shadow: 0 2px 12px rgba(0,0,0,0.18);
  }
  .hero-tagline {
    font-size: 1.25rem;
    font-weight: 400;
    color: #fff;
    letter-spacing: 0.08em;
    text-shadow: 0 1px 8px rgba(0,0,0,0.15);
  }
  @media (max-width: 600px) {
    .hero-logo-large {
      width: 90px;
      height: 90px;
      margin-bottom: 1.2rem;
    }
    .hero-title {
      font-size: 2rem;
      margin-bottom: 0.7rem;
    }
    .hero-tagline {
      font-size: 1rem;
    }
    .hero-center {
      min-height: 60vh;
    }
  }

/* Testimonial Carousel Styles */

/* Refined Testimonial Carousel Styles */
/* Fix centering and container width */
.testimonial-slide {
  background: #fff;
  border-radius: 20px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10);
  padding: 48px 48px 48px 36px;
  display: flex;
  align-items: center;
  width: 100%;
  max-width: 500px;
  min-width: 0;
  min-height: 220px;
  margin: 0 auto;
  transition: box-shadow 0.3s, transform 0.3s;
  box-sizing: border-box;
}
.testimonial-profile {
  width: 90px;
  height: 90px;
  object-fit: cover;
  border: 3px solid #eee;
  box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.testimonial-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  margin-left: 32px;
}
.testimonial-name {
  font-size: 1.35rem;
  font-weight: 700;
  margin-bottom: 0.3rem;
}
.testimonial-rating {
  font-size: 1.15rem;
  color: #ffc107;
  font-weight: 500;
}
.testimonial-date {
  font-size: 1rem;
  color: #888;
}
.testimonial-text {
  font-size: 1.18rem;
  color: #232323;
  margin-top: 0.7rem;
  font-style: italic;
}
.section-testimonials > .testimonial-slide,
section > .testimonial-slide {
  /* make testimonial slides participate in a responsive single-row grid when adjacent */
  box-sizing: border-box;
  margin: 12px auto;
}

.reviews-section > .testimonial-slide {
  box-sizing: border-box;
  margin: 12px auto;
}

.reviews-section {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 20px;
  width: 100%;
  max-width: 900px;
  margin: 0 auto;
  padding: 0 12px;
  box-sizing: border-box;
}
@media (max-width: 900px) {
  .reviews-section {
    max-width: 98vw;
  }
  .testimonial-slide {
    max-width: 98vw;
    width: 100%;
    padding: 32px 10px 32px 10px;
    min-height: 180px;
  }
  .testimonial-profile {
    width: 64px;
    height: 64px;
  }
  .testimonial-info {
    margin-left: 16px;
  }
}
@media (max-width: 600px) {
  .testimonial-slide {
    flex-direction: column;
    align-items: flex-start;
    padding: 20px 6px 20px 6px;
    min-height: 120px;
  }
  .testimonial-profile {
    margin-bottom: 10px;
    width: 48px;
    height: 48px;
  }
  .testimonial-info {
    margin-left: 0;
  }
}
.carousel-slides-container {
  width: 100%;
  display: flex;
  justify-content: center; /* Center the slides horizontally */
  align-items: center;
  overflow: visible;
  position: relative;
  margin-left: 0; /* Remove the margin hack */
}
.carousel-slides-viewport {
  width: 100%;
  max-width: 1100px;
  overflow: hidden;
  margin: 0 auto;
  position: relative;
  height: 420px; /* adjust as needed */
}
.carousel-track {
  display: flex;
  transition: transform 0.5s cubic-bezier(.77,0,.18,1);
  will-change: transform;
}
.review-card {
  flex: 0 0 350px;
  margin-left: -80px;
  margin-right: -80px;
}
.carousel-track.next-anim {
  animation: slide-next 0.4s cubic-bezier(.77,0,.18,1);
}
.carousel-track.prev-anim {
  animation: slide-prev 0.4s cubic-bezier(.77,0,.18,1);
}
@keyframes slide-next {
  0% { transform: translateX(60px); }
  100% { transform: translateX(0); }
}
@keyframes slide-prev {
  0% { transform: translateX(-60px); }
  100% { transform: translateX(0); }
}

.logout-btn {
  background-color: #dc2626 !important;
  border-color: #dc2626 !important;
}
.logout-btn:hover {
  background-color: #b91c1c !important;
  border-color: #b91c1c !important;
}

.create-survey-btn {
  position: fixed;
  right: 1.5rem;
  bottom: 1.5rem;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  background: #495846;
  color: #fff;
  border: none;
  padding: 0.6rem 0.9rem;
  border-radius: 9999px;
  font-weight: 600;
  box-shadow: 0 6px 18px rgba(73,88,70,0.18);
  cursor: pointer;
  transition: transform 0.12s ease, box-shadow 0.12s ease;
  z-index: 200;
}
.create-survey-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 24px rgba(73,88,70,0.22);
}
.create-survey-btn .plus {
  background: rgba(255,255,255,0.12);
  padding: 0.25rem 0.45rem;
  border-radius: 999px;
  font-size: 1.05rem;
  line-height: 1;
}
.create-survey-btn .label {
  font-size: 0.95rem;
}
</style>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

// Get authentication data from Inertia
const page = usePage();
const user = computed(() => page.props.auth.user);

// Modal state
const showLogoutModal = ref(false);

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

const reviews = [
  {
    name: 'Luis Palparan',
    profile: 'https://randomuser.me/api/portraits/men/32.jpg',
    text: 'Gwapo siya, ok lng.',
    photo: 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80',
    date: '1 week ago'
  },
  {
    name: 'Hanz Regalado',
    profile: 'https://randomuser.me/api/portraits/men/45.jpg',
    text: 'Very minimalist',
    photo: 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80',
    date: '3 days ago'
  },
  {
    name: 'Julianne Casia',
    profile: 'https://randomuser.me/api/portraits/women/65.jpg',
    text: 'A new generation of co-working.',
    photo: 'https://images.unsplash.com/photo-1464983953574-0892a716854b?auto=format&fit=crop&w=400&q=80',
    date: '2 days ago'
  },
  {
    name: 'John Doe',
    profile: 'https://randomuser.me/api/portraits/men/12.jpg',
    text: 'Great place for studying.',
    photo: 'https://images.unsplash.com/photo-1515378791036-0648a3ef77b2?auto=format&fit=crop&w=400&q=80',
    date: '1 week ago'
  },
  {
    name: 'Maria Santos',
    profile: 'https://randomuser.me/api/portraits/women/44.jpg',
    text: 'Love the ambiance and coffee!',
    photo: 'https://images.unsplash.com/photo-1519125323398-675f0ddb6308?auto=format&fit=crop&w=400&q=80',
    date: '5 days ago'
  }
]

const currentIndex = ref(2) // Start at the middle


const visibleSlides = computed(() => {
  const slides = []
  const total = reviews.length
  for (let i = -2; i <= 2; i++) {
    // Always wrap around for infinite effect, no empty slots
    let idx = (currentIndex.value + i + total) % total
    slides.push(reviews[idx])
  }
  return slides
})


function prev() {
  currentIndex.value = (currentIndex.value - 1 + reviews.length) % reviews.length
}
function next() {
  currentIndex.value = (currentIndex.value + 1) % reviews.length
}
function goTo(idx: number) {
  currentIndex.value = idx
}

function getCardStyle(idx: number) {
  const diff = idx - 2 // 2 is the center index for 5 cards
  const base = {
    minWidth: '350px',
    maxWidth: '350px',
    background: '#fff',
    borderRadius: '18px',
    boxShadow: '0 2px 12px rgba(0,0,0,0.10)',
    padding: '24px 18px',
    position: 'relative', // Set position as string value
    zIndex: 1,
    marginLeft: '-80px',
    marginRight: '-80px',
    opacity: 0.5,
    transform: 'scale(0.92) translateY(30px)',
    transition: 'all 0.35s cubic-bezier(.77,0,.18,1)'
  }
  if (diff === 0) {
    return {
      ...base,
      zIndex: 3,
      opacity: 1,
      marginLeft: '0',
      marginRight: '0',
      transform: 'scale(1.08) translateY(0px)',
      boxShadow: '0 8px 32px rgba(0,0,0,0.13), 0 2px 8px rgba(0,0,0,0.10)'
    }
  }
  if (Math.abs(diff) === 1) {
    return {
      ...base,
      zIndex: 2,
      opacity: 0.8,
      transform: 'scale(0.98) translateY(12px)'
    }
  }
  if (Math.abs(diff) === 2) {
    return {
      ...base,
      zIndex: 1,
      opacity: 0.5,
      transform: 'scale(0.92) translateY(30px)'
    }
  }
  // Hide cards outside the visible 5 (shouldn't happen)
  return {
    ...base,
    opacity: 0,
    pointerEvents: 'none'
  }
}

import { router } from '@inertiajs/vue3';

function createSurvey() {
  // If user is authenticated, go to survey create page.
  // Otherwise redirect to login and include intended redirect so user returns after login.
  if (user && user.value) {
    router.visit('/survey/create');
  } else {
    router.visit('/login?redirect=/survey/create');
  }
}
</script>