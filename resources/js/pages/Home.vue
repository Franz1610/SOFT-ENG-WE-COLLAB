<template>
  <div style="background: #232323; min-height: 100vh;">
    <AppHeader :user="user" active="home" @auth="handleAuthAction" />
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
      <section id="reviews" class="reviews-section">
        <div class="reviews-container">
          <div class="reviews-header">
            <div>
              <p class="reviews-eyebrow">Community pulse</p>
              <h2>Reviews</h2>
            </div>
            <div class="reviews-live-tools">
              <div class="live-pill" aria-live="polite">
                <span class="live-dot" aria-hidden="true"></span>
                Live feed
              </div>
              <p class="live-caption">Auto refreshes every 15s · Updated {{ lastSyncedLabel }}</p>
              <button class="refresh-btn" type="button" @click="refreshLiveFeed(true)" :disabled="isFeedRefreshing">
                <span v-if="isFeedRefreshing" class="refresh-spinner" aria-hidden="true"></span>
                <span>{{ isFeedRefreshing ? 'Syncing…' : 'Refresh now' }}</span>
              </button>
            </div>
          </div>
          <p class="reviews-subcopy">Every feedback submission lands here within seconds so guests can feel the vibe before walking in.</p>
          <p v-if="feedError" class="reviews-error" role="status">{{ feedError }}</p>

          <div
            class="carousel-wrapper"
            v-if="normalizedReviews.length"
            @mouseenter="handleCarouselEnter"
            @mouseleave="handleCarouselLeave()"
            @focusin="handleCarouselEnter"
            @focusout="handleCarouselLeave"
          >
            <div class="carousel-slides-viewport">
              <div class="carousel-track" :style="trackStyle">
                <article
                  v-for="(review, idx) in normalizedReviews"
                  :key="review.id ?? idx"
                  :class="['review-card', { 'review-card--fresh': review.isFresh }]"
                  :style="cardStyle"
                >
                  <div class="review-card-header">
                    <div class="review-avatar" :style="{ background: review.avatarBackground }">
                      <span>{{ review.user_initials }}</span>
                    </div>
                    <div>
                      <p class="review-name">{{ review.user_name }}</p>
                      <p class="review-meta">{{ review.typeLabel }} · {{ review.submittedLabel }}</p>
                    </div>
                  </div>
                  <div class="review-rating-row">
                    <span
                      v-for="n in 5"
                      :key="n"
                      class="review-star"
                      :class="{ filled: n <= review.rating }"
                    >★</span>
                  </div>
                  <p class="review-comment">“{{ review.comments }}”</p>
                  <div class="review-cover" :style="{ backgroundImage: `url(${review.coverImage})` }">
                    <span class="cover-chip">{{ review.typeLabel }}</span>
                  </div>
                </article>
              </div>
            </div>

            <div class="carousel-controls">
              <button class="carousel-arrow" @click="prev" :disabled="normalizedReviews.length <= 1" aria-label="Previous review">
                &larr;
              </button>
              <div class="carousel-dots">
                <button
                  v-for="(review, idx) in normalizedReviews"
                  :key="review.id ?? idx"
                  class="dot"
                  :class="{ active: idx === currentIndex }"
                  @click="goTo(idx)"
                  :aria-label="`Go to review ${idx + 1}`"
                ></button>
              </div>
              <button class="carousel-arrow" @click="next" :disabled="normalizedReviews.length <= 1" aria-label="Next review">
                &rarr;
              </button>
            </div>
          </div>
          <p v-else class="reviews-empty">Be the first to leave feedback and launch this live board!</p>
        </div>
      </section>
      <!-- End Reviews Section -->

      <!-- Floating Create Survey button -->
      <button
        class="create-survey-btn"
        @click="createFeedback"
        aria-label="Submit Feedback"
      >
        <span class="plus">+</span>
        <span class="label">Submit Feedback!</span>
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
  width: 100%;
  z-index: 100;
  box-shadow: none;
  border-bottom: none;
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
}
.nav {
  display: flex;
  gap: 1.5rem;
  align-items: center;
}

/* Hamburger & mobile menu styles */
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
  /* On small screens hide inline nav and show hamburger */
  .nav {
    display: none;
  }
  .hamburger-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
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

/* Live reviews section */
.reviews-section {
  background: #f7f5ed;
  padding: 64px 0;
}

.reviews-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 0 1rem;
}

.reviews-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.reviews-eyebrow {
  font-size: 0.85rem;
  letter-spacing: 0.2em;
  text-transform: uppercase;
  color: #7f8b7f;
  margin-bottom: 0.4rem;
}

.reviews-live-tools {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.75rem;
}

.live-pill {
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  padding: 0.35rem 0.85rem;
  border-radius: 999px;
  background: rgba(73, 88, 70, 0.15);
  color: #232323;
  font-weight: 600;
}

.live-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #ff5722;
  box-shadow: 0 0 8px rgba(255, 87, 34, 0.8);
  animation: pulse 1.8s ease-in-out infinite;
}

@keyframes pulse {
  0% { opacity: 0.5; transform: scale(0.8); }
  50% { opacity: 1; transform: scale(1); }
  100% { opacity: 0.5; transform: scale(0.85); }
}

.live-caption {
  font-size: 0.9rem;
  color: #555;
}

.refresh-btn {
  border: 1px solid rgba(73, 88, 70, 0.35);
  border-radius: 999px;
  background: #fff;
  color: #232323;
  font-weight: 600;
  padding: 0.45rem 1.2rem;
  display: inline-flex;
  align-items: center;
  gap: 0.45rem;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.refresh-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.refresh-spinner {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  border: 2px solid rgba(73, 88, 70, 0.2);
  border-top-color: #4b824b;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.reviews-subcopy {
  text-align: center;
  margin: 1.5rem auto 0.75rem;
  max-width: 720px;
  color: #555;
}

.reviews-error {
  text-align: center;
  color: #b91c1c;
  font-weight: 600;
  margin-bottom: 1rem;
}

.carousel-wrapper {
  position: relative;
  overflow: visible;
}

.review-card {
  min-width: 0;
  flex: 0 0 auto;
  background: #fff;
  border-radius: 22px;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.12);
  padding: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.9rem;
  border: 1px solid rgba(73, 88, 70, 0.08);
}

.review-card--fresh {
  animation: review-fade-in 0.65s ease;
}

@keyframes review-fade-in {
  0% {
    opacity: 0;
    transform: translateY(20px) scale(0.96);
  }
  60% {
    opacity: 1;
    transform: translateY(-2px) scale(1.01);
  }
  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.review-card-header {
  display: flex;
  align-items: center;
  gap: 0.9rem;
}

.review-avatar {
  width: 56px;
  height: 56px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  color: #fff;
  text-transform: uppercase;
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.review-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0;
}

.review-meta {
  margin: 0;
  font-size: 0.9rem;
  color: #777;
}

.review-rating-row {
  display: flex;
  gap: 0.2rem;
}

.review-star {
  color: #ddd;
  font-size: 1.1rem;
}

.review-star.filled {
  color: #ffb300;
}

.review-comment {
  font-size: 1.05rem;
  color: #242424;
  line-height: 1.5;
  min-height: 72px;
}

.review-cover {
  position: relative;
  border-radius: 18px;
  background-size: cover;
  background-position: center;
  height: 170px;
  overflow: hidden;
}

.cover-chip {
  position: absolute;
  bottom: 12px;
  left: 12px;
  background: rgba(255, 255, 255, 0.9);
  color: #232323;
  padding: 0.2rem 0.8rem;
  border-radius: 999px;
  font-size: 0.85rem;
  font-weight: 600;
}

.carousel-controls {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1.5rem;
  margin-top: 2rem;
}

.carousel-arrow {
  border: none;
  background: rgba(73, 88, 70, 0.15);
  color: #495846;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  font-size: 1.5rem;
  cursor: pointer;
  transition: background 0.2s ease;
}

.carousel-arrow:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.carousel-dots {
  display: flex;
  gap: 0.5rem;
}

.carousel-dots .dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: #d2d2d2;
  border: none;
  cursor: pointer;
}

.carousel-dots .dot.active {
  background: #495846;
}

@media (max-width: 768px) {
  .review-card {
    min-width: 280px;
    margin-left: -30px;
    margin-right: -30px;
  }

  .reviews-live-tools {
    flex-direction: column;
    align-items: flex-start;
  }
}
.carousel-slides-viewport {
  width: 100%;
  max-width: 1100px;
  overflow: hidden;
  margin: 0 auto;
  position: relative;
}

.carousel-track {
  display: flex;
  align-items: stretch;
  will-change: transform;
  transition: transform 0.45s cubic-bezier(.77, 0, .18, 1);
  padding: 1rem 0;
}

.reviews-empty {
  text-align: center;
  color: #6a6a6a;
  padding: 2rem 1rem;
  font-weight: 500;
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
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AppHeader from '@/components/AppHeader.vue'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'

interface FeedbackCard {
  id?: number
  user_name: string
  user_initials: string
  type: string
  rating: number
  comments: string
  created_at?: string
  submitted_for_humans?: string | null
  avatar_seed?: string
}

interface DisplayReview extends FeedbackCard {
  coverImage: string
  avatarBackground: string
  submittedLabel: string
  typeLabel: string
  isFresh: boolean
}

// Get authentication data from Inertia
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Modal state
const showLogoutModal = ref(false);

// Mobile menu handled by AppHeader

function closeLogoutModal() {
  showLogoutModal.value = false
}

function confirmLogout() {
  showLogoutModal.value = false
  router.post('/logout')
}

function handleLogout() {
  showLogoutModal.value = true
}

function handleAuthAction() {
  if (user.value) {
    handleLogout()
    return
  }
  router.visit('/login')
}

function createFeedback() {
  if (user.value) {
    router.visit('/feedback/create')
    return
  }
  router.visit('/login?redirect=/feedback/create')
}

const fallbackReviews: FeedbackCard[] = [
  {
    id: 1,
    user_name: 'Luis Palparan',
    user_initials: 'LP',
    type: 'general',
    rating: 5,
    comments: 'Gwapo siya, ok lng.',
    submitted_for_humans: '1 week ago',
    avatar_seed: 'a1b2c3'
  },
  {
    id: 2,
    user_name: 'Hanz Regalado',
    user_initials: 'HR',
    type: 'product',
    rating: 5,
    comments: 'Very minimalist.',
    submitted_for_humans: '3 days ago',
    avatar_seed: 'd4e5f6'
  },
  {
    id: 3,
    user_name: 'Julianne Casia',
    user_initials: 'JC',
    type: 'general',
    rating: 4,
    comments: 'A new generation of co-working.',
    submitted_for_humans: '2 days ago',
    avatar_seed: '112233'
  },
  {
    id: 4,
    user_name: 'John Doe',
    user_initials: 'JD',
    type: 'page',
    rating: 4,
    comments: 'Great place for studying.',
    submitted_for_humans: '1 week ago',
    avatar_seed: '445566'
  },
  {
    id: 5,
    user_name: 'Maria Santos',
    user_initials: 'MS',
    type: 'product',
    rating: 5,
    comments: 'Love the ambiance and coffee!',
    submitted_for_humans: '5 days ago',
    avatar_seed: '778899'
  }
]

const liveFeed = ref<FeedbackCard[]>((page.props.liveFeedback as FeedbackCard[] | undefined) ?? [])
const lastSyncedAt = ref<string | null>((page.props.liveFeedbackRefreshedAt as string | undefined) ?? null)
const isFeedRefreshing = ref(false)
const feedError = ref<string | null>(null)
const REFRESH_INTERVAL = 15000
let refreshTimer: number | undefined

const coverImagePools: Record<string, string[]> = {
  general: [
    'https://images.unsplash.com/photo-1464750329540-2830d50d0c48?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1448932223592-d1fc686e76ea?auto=format&fit=crop&w=800&q=80'
  ],
  product: [
    'https://images.unsplash.com/photo-1481277542470-605612bd2d61?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1454165205744-3b78555e5572?auto=format&fit=crop&w=800&q=80'
  ],
  page: [
    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1454165205744-3b78555e5572?auto=format&fit=crop&w=800&q=80'
  ]
}

function getCoverImage(type: string, idx: number): string {
  const pool = coverImagePools[type] ?? coverImagePools.general
  return pool[idx % pool.length]
}

function createAvatarBackground(seed: string, idx: number): string {
  const numericSeed = parseInt(seed, 16)
  const base = Number.isNaN(numericSeed) ? idx * 57 : numericSeed
  const hue = base % 360
  return `linear-gradient(135deg, hsl(${hue}, 70%, 70%), hsl(${(hue + 35) % 360}, 65%, 55%))`
}

function getReviewKey(review: FeedbackCard): string {
  if (review.id) {
    return `id-${review.id}`
  }
  return [
    review.user_name ?? 'guest',
    review.comments ?? '',
    review.created_at ?? review.submitted_for_humans ?? ''
  ].join('::')
}

const normalizedReviews = computed<DisplayReview[]>(() => {
  const source = liveFeed.value.length ? liveFeed.value : fallbackReviews
  const freshSet = new Set(freshKeys.value)
  return source.map((review, idx) => {
    const typeKey = (review.type || 'general').toLowerCase()
    const label = typeKey.replace(/_/g, ' ')
    const reviewKey = getReviewKey(review)
    return {
      ...review,
      type: typeKey,
      typeLabel: label.charAt(0).toUpperCase() + label.slice(1),
      coverImage: getCoverImage(typeKey, idx),
      avatarBackground: createAvatarBackground(review.avatar_seed ?? `${idx}`, idx),
      submittedLabel: review.submitted_for_humans ?? 'moments ago',
      isFresh: freshSet.has(reviewKey)
    }
  })
})

const currentIndex = ref(0)

const viewportWidth = ref(typeof window !== 'undefined' ? window.innerWidth : 1024)
const cardWidth = computed(() => (viewportWidth.value <= 768 ? 280 : 340))
const cardGap = computed(() => (viewportWidth.value <= 768 ? 16 : 28))
const trackStyle = computed(() => ({
  transform: `translateX(-${currentIndex.value * (cardWidth.value + cardGap.value)}px)`,
  gap: `${cardGap.value}px`
}))
const cardStyle = computed(() => ({
  width: `${cardWidth.value}px`,
  flex: `0 0 ${cardWidth.value}px`
}))

const AUTOPLAY_DELAY = 5000
let autoplayTimer: number | undefined

const freshKeys = ref<string[]>([])
const freshKeySet = new Set<string>()
const freshKeyTimers: Record<string, number> = {}

const isCarouselHovered = ref(false)
const isDocumentHidden = ref(typeof document !== 'undefined' ? document.hidden : false)
const isAutoplayEligible = computed(() => normalizedReviews.value.length > 1 && !isCarouselHovered.value && !isDocumentHidden.value)

function startAutoplay() {
  if (typeof window === 'undefined') return
  if (autoplayTimer || !isAutoplayEligible.value) return
  autoplayTimer = window.setInterval(() => {
    next()
  }, AUTOPLAY_DELAY)
}

function stopAutoplay() {
  if (typeof window === 'undefined') return
  if (!autoplayTimer) return
  window.clearInterval(autoplayTimer)
  autoplayTimer = undefined
}

function restartAutoplay() {
  stopAutoplay()
  if (isAutoplayEligible.value) {
    startAutoplay()
  }
}

function handleCarouselEnter() {
  if (!isCarouselHovered.value) {
    isCarouselHovered.value = true
    stopAutoplay()
  }
}

function handleCarouselLeave(event?: FocusEvent | MouseEvent) {
  const target = event?.currentTarget as HTMLElement | undefined
  const related = (event as FocusEvent | MouseEvent | undefined)?.relatedTarget as Node | null | undefined
  if (target && related && target.contains(related)) {
    return
  }
  if (isCarouselHovered.value) {
    isCarouselHovered.value = false
    restartAutoplay()
  }
}

function handleVisibilityChange() {
  if (typeof document === 'undefined') return
  isDocumentHidden.value = document.hidden
}

function scheduleFreshRemoval(key: string) {
  if (typeof window === 'undefined') return
  if (freshKeyTimers[key]) {
    window.clearTimeout(freshKeyTimers[key])
  }
  freshKeyTimers[key] = window.setTimeout(() => {
    freshKeySet.delete(key)
    freshKeys.value = Array.from(freshKeySet)
    window.clearTimeout(freshKeyTimers[key])
    delete freshKeyTimers[key]
  }, 1200)
}

function tagFreshReviews(keys: string[]) {
  if (!keys.length) return
  keys.forEach(key => {
    freshKeySet.add(key)
    scheduleFreshRemoval(key)
  })
  freshKeys.value = Array.from(freshKeySet)
}

function clearFreshAnimations() {
  if (typeof window !== 'undefined') {
    Object.values(freshKeyTimers).forEach(timer => window.clearTimeout(timer))
  }
  Object.keys(freshKeyTimers).forEach(key => delete freshKeyTimers[key])
  freshKeySet.clear()
  freshKeys.value = []
}

function handleResize() {
  if (typeof window === 'undefined') return
  viewportWidth.value = window.innerWidth
}

const lastSyncedLabel = computed(() => {
  if (!lastSyncedAt.value) {
    return 'moments ago'
  }
  const date = new Date(lastSyncedAt.value)
  if (Number.isNaN(date.getTime())) {
    return 'moments ago'
  }
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
})

async function refreshLiveFeed(manual = false) {
  if (isFeedRefreshing.value) {
    return
  }
  isFeedRefreshing.value = true
  if (manual) {
    feedError.value = null
  }
  try {
    const response = await fetch('/api/feedback/live?limit=20', {
      headers: { Accept: 'application/json' }
    })
    if (!response.ok) {
      throw new Error('Unable to fetch latest feedback.')
    }
    const payload = await response.json()
    const nextFeed = Array.isArray(payload.data) ? payload.data : []
    const previousKeys = new Set(liveFeed.value.map(getReviewKey))
    const incomingKeys = nextFeed.map(getReviewKey)
    const newKeys = incomingKeys.filter((key: string) => !previousKeys.has(key))
    liveFeed.value = nextFeed
    if (newKeys.length) {
      tagFreshReviews(newKeys)
    }
    lastSyncedAt.value = payload.refreshed_at ?? new Date().toISOString()
  } catch (error: unknown) {
    const message = error instanceof Error ? error.message : 'Unable to refresh reviews.'
    feedError.value = message
  } finally {
    isFeedRefreshing.value = false
  }
}

onMounted(() => {
  if (typeof window !== 'undefined') {
    window.addEventListener('resize', handleResize)
  }
  if (typeof document !== 'undefined') {
    document.addEventListener('visibilitychange', handleVisibilityChange)
  }
  refreshTimer = window.setInterval(() => refreshLiveFeed(), REFRESH_INTERVAL)
  refreshLiveFeed()
})

onBeforeUnmount(() => {
  if (typeof window !== 'undefined') {
    window.removeEventListener('resize', handleResize)
  }
  if (typeof document !== 'undefined') {
    document.removeEventListener('visibilitychange', handleVisibilityChange)
  }
  stopAutoplay()
  clearFreshAnimations()
  if (refreshTimer) {
    window.clearInterval(refreshTimer)
  }
})

watch(
  () => normalizedReviews.value.length,
  length => {
    if (!length) {
      currentIndex.value = 0
      return
    }
    currentIndex.value = ((currentIndex.value % length) + length) % length
    restartAutoplay()
  }
)

watch(
  isAutoplayEligible,
  active => {
    if (active) {
      startAutoplay()
    } else {
      stopAutoplay()
    }
  },
  { immediate: true }
)

function prev() {
  const total = normalizedReviews.value.length
  if (!total) return
  currentIndex.value = (currentIndex.value - 1 + total) % total
  restartAutoplay()
}

function next() {
  const total = normalizedReviews.value.length
  if (!total) return
  currentIndex.value = (currentIndex.value + 1) % total
  restartAutoplay()
}

function goTo(idx: number) {
  const total = normalizedReviews.value.length
  if (!total) return
  currentIndex.value = idx % total
  restartAutoplay()
}

</script>
