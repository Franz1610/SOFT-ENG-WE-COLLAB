<template>
  <div style="background: #ffffff; min-height: 100vh;">
    <!-- Header (kept consistent with Home.vue) -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo" @click="goHome" role="button" tabindex="0">WECOLLAB</div>
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
          <button class="home-btn" @click="goHome">HOME</button>
        </nav>
      </div>
    </header>

    <main class="main-content" style="padding: 80px 20px 120px;">
      <div style="max-width: 1100px; margin: 0 auto; color: #232323;">
        <div style="font-size: 0.85rem; font-weight: 700; color: #888; margin-bottom: 8px;">CREATE NEW SURVEY</div>
        <h1 style="font-size: 2.25rem; margin: 0 0 12px;">Pick a survey</h1>
        <p style="color: #666; margin-bottom: 28px;">
          Select the survey template you’d like to start with. You can customize it later.
        </p>

        <div class="survey-grid">
          <div
            v-for="(card, idx) in cards"
            :key="card.key"
            :class="['survey-card', { 'selected': selected === idx }]"
            @click="openCardModal(idx)"
          >
            <div class="card-top">
              <div class="emoji">{{ card.emoji }}</div>
              <div class="card-title">{{ card.title }}</div>
            </div>
            <div class="card-desc">{{ card.description }}</div>
            <div class="card-footer">
              <div class="icons">
                <span v-for="n in (card.previewIcons || 0)" :key="n" class="mini-ico">●</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Feedback Modal (with transition) -->
        <transition name="modal" appear>
          <div v-if="showModal" class="modal-overlay" @keydown.esc="closeModal" tabindex="-1">
            <div class="modal-panel" role="dialog" aria-modal="true">
              <header class="modal-header">
                <h2>{{ modalTitle }}</h2>
                <button class="modal-close" @click="closeModal" aria-label="Close">×</button>
              </header>

              <!-- animated swap between form and thank-you -->
              <transition name="thanks" mode="out-in">
                <div class="modal-body" v-if="!showThanks" key="form">
                  <div class="rating-row">
                    <div class="stars" @mouseleave="hoverRating = 0">
                      <button
                        v-for="n in 5"
                        :key="n"
                        type="button"
                        class="star-btn"
                        :class="{ filled: (hoverRating || rating) >= n }"
                        @mouseover="hoverRating = n"
                        @focus="hoverRating = n"
                        @click="setRating(n)"
                        :aria-pressed="rating >= n"
                        :aria-label="`Rate ${n} stars`"
                      >★</button>
                    </div>
                    <div class="rating-value">{{ rating }} / 5</div>
                  </div>
                  <label class="comment-label">Your thoughts</label>
                  <textarea v-model="comment" class="comment-input" placeholder="Share your opinion or suggestions..." rows="6"></textarea>
                </div>

                <div class="modal-body" v-else key="thanks">
                  <div class="thanks">Thank you for your feedback!</div>
                </div>
              </transition>

              <!-- animated footer swap -->
              <transition name="footer" mode="out-in">
                <footer class="modal-footer" v-if="!showThanks" key="footer-form">
                  <button class="btn-secondary" @click="closeModal">Cancel</button>
                  <button
                    class="btn-primary"
                    @click="submitFeedback"
                    :disabled="!isValid"
                    :aria-disabled="!isValid"
                  >
                    Submit
                  </button>
                </footer>
                <footer class="modal-footer" v-else key="footer-thanks">
                  <button class="btn-secondary" @click="closeModal">Close</button>
                </footer>
              </transition>
            </div>
          </div>
        </transition>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

// Basic auth/header helpers (keeps header consistent with Home.vue)
const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

function handleAuthAction() {
  if (user.value) {
    // Immediately log the user out
    router.post('/logout');
  } else {
    router.visit('/login');
  }
}
function goHome() {
  router.visit('/');
}

// Survey cards data (replicates the design blocks from your screenshot)
const cards = [
  { key: 'product', emoji: '🙂', title: 'Product feedback', description: 'Get product suggestions or bug reports', previewIcons: 3 },
  { key: 'page', emoji: '🙂', title: 'Page feedback', description: 'Get quick ratings for pages or behaviors', previewIcons: 4 },
  { key: 'general', emoji: '≡', title: 'General satisfaction', description: 'Get overall satisfaction and recommendation feedback', previewIcons: 5 }
];

const selected = ref<number|null>(0);


// --- NEW / updated: modal + feedback state & handlers ---
const showModal = ref(false);
const rating = ref<number>(0);
const hoverRating = ref<number>(0);
const comment = ref<string>('');
const modalTitle = ref<string>('');
const modalKey = ref<string | null>(null);

// NEW: show thank you message after submit
const showThanks = ref(false);

// validation state (no inline error message)
const isValid = computed(() => rating.value > 0 && comment.value.trim().length > 0);

function openCardModal(idx: number) {
  selected.value = idx;
  modalTitle.value = cards[idx].title;
  modalKey.value = cards[idx].key;
  rating.value = 0;
  hoverRating.value = 0;
  comment.value = '';
  showThanks.value = false; // reset thank-you state
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
  showThanks.value = false; // reset when closing
}

// rating helpers
function setRating(n: number) {
  rating.value = n;
}

function submitFeedback() {
  if (!isValid.value) return;

  router.post('/feedback', {
    type: modalKey.value,
    rating: rating.value,
    comments: comment.value,
  }, {
    onSuccess: () => {
      showThanks.value = true;
    }
  });
}
// --- END NEW ---
</script>

<style scoped>
/* reuse header styles same color scheme as Home.vue (scoped so matches page) */
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

/* Page content */
.main-content { margin-top: 64px; background: #ffffff; }

/* Survey grid */
.survey-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
  gap: 16px;
  margin-top: 8px;
}
.survey-card {
  background: #fff;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.06);
  border: 1px solid rgba(0,0,0,0.06);
  cursor: pointer;
  transition: transform .12s ease, box-shadow .12s ease, border-color .12s;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 120px;
}
.survey-card:hover { transform: translateY(-6px); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
.survey-card.selected { border-color: #ee6b5a; box-shadow: 0 12px 30px rgba(238,107,90,0.08); }
.survey-card.dotted {
  background: transparent;
  border: 2px dashed #e2e2e2;
  color: #777;
}
.card-top { display:flex; align-items:center; gap:12px; margin-bottom:8px; }
.emoji { font-size: 1.4rem; width:36px; height:36px; display:flex; align-items:center; justify-content:center; background:#fff0; border-radius:8px; }
.card-title { font-weight: 700; font-size: 1.05rem; color:#232323; }
.card-desc { color:#666; font-size: 0.95rem; margin-top:6px; line-height:1.35; }
.card-footer { display:flex; justify-content:flex-end; margin-top:14px; }
.mini-ico { display:inline-block; margin-left:6px; font-size:10px; color:#495846; opacity:0.85; }

/* Header & nav styles (copied from Home.vue to match exact header design) */
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

/* Modal overlay & panel */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 400;
}
.modal-panel {
  width: 100%;
  max-width: 720px;
  background: #fff;
  border-radius: 12px;
  box-shadow: 0 12px 48px rgba(0,0,0,0.2);
  overflow: hidden;
  display: flex;
  flex-direction: column;
}
.modal-header {
  display:flex;
  align-items:center;
  justify-content:space-between;
  padding: 18px 20px;
  background: #495846; /* match site header color */
  border-bottom: none;
}
.modal-header h2 {
  margin: 0;
  font-size: 1.25rem;
  color: #ffffff; /* title text white */
}
.modal-close {
  background: transparent;
  border: none;
  font-size: 1.6rem;
  cursor: pointer;
  color: #ffffff; /* close button white to match header */
}

/* Modal body */
.modal-body { padding: 20px; }
.rating-row { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.stars { display:flex; gap:8px; }
.star-btn {
  background: transparent;
  border: none;
  font-size: 1.8rem;
  cursor: pointer;
  color: #dcdcdc;
  transition: color 0.12s ease, transform 0.08s ease;
  line-height: 1;
  padding: 4px;
}
.star-btn.filled { color: #ffc107; transform: translateY(-2px); }
.rating-value { color:#666; font-weight:600; }

/* Comment input */
.comment-label { display:block; font-weight:600; margin-bottom:8px; color:#333; }
.comment-input {
  width: 100%;
  border: 1px solid #e6e6e6;
  border-radius: 8px;
  padding: 12px;
  font-size: 0.95rem;
  color: #232323;
  resize: vertical;
  box-sizing: border-box;
}

/* Modal footer buttons */
.modal-footer {
  display:flex;
  justify-content:flex-end;
  gap:12px;
  padding: 14px 20px;
  border-top: 1px solid #f1f1f1;
  background: #fafafa;
}
.btn-primary {
  background: #495846;
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 8px;
  cursor: pointer;
  font-weight:600;
}
/* Make disabled submit appear grey and not-allowed */
.btn-primary[disabled],
.btn-primary:disabled {
  background: #9ca3af; /* gray */
  color: #ffffff;
  cursor: not-allowed;
  opacity: 1;
  box-shadow: none;
}

/* Modal transition: overlay fade */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 180ms ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-to,
.modal-leave-from {
  opacity: 1;
}

/* Modal-panel scale + fade (applies to panel inside the overlay during transition) */
.modal-enter-from .modal-panel {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}
.modal-enter-to .modal-panel {
  transform: translateY(0) scale(1);
  opacity: 1;
}
.modal-leave-from .modal-panel {
  transform: translateY(0) scale(1);
  opacity: 1;
}
.modal-leave-to .modal-panel {
  transform: translateY(12px) scale(0.98);
  opacity: 0;
}
.modal-enter-active .modal-panel,
.modal-leave-active .modal-panel {
  transition: transform 220ms cubic-bezier(.2,.9,.2,1), opacity 220ms ease;
}

/* small screens */
@media (max-width: 600px) {
  .modal-panel { margin: 0 12px; }
}

/* thank-you message style */
.thanks {
  text-align: center;
  font-size: 1.15rem;
  font-weight: 700;
  color: #232323;
  padding: 28px 16px;
}

/* thank-you content transition */
.thanks-enter-active, .thanks-leave-active {
  transition: opacity 220ms ease, transform 220ms ease;
}
.thanks-enter-from {
  opacity: 0;
  transform: translateY(8px) scale(0.995);
}
.thanks-enter-to {
  opacity: 1;
  transform: translateY(0) scale(1);
}
.thanks-leave-from {
  opacity: 1;
  transform: translateY(0) scale(1);
}
.thanks-leave-to {
  opacity: 0;
  transform: translateY(-8px) scale(0.995);
}

/* footer buttons transition */
.footer-enter-active, .footer-leave-active {
  transition: opacity 200ms ease, transform 200ms ease;
}
.footer-enter-from {
  opacity: 0;
  transform: translateY(6px);
}
.footer-enter-to {
  opacity: 1;
  transform: translateY(0);
}
.footer-leave-from {
  opacity: 1;
  transform: translateY(0);
}
.footer-leave-to {
  opacity: 0;
  transform: translateY(6px);
}

/* validation error text (removed from template usage, optional to keep) */
/* .error-text {
  color: #dc2626;
  margin-top: 10px;
  font-size: 0.95rem;
  font-weight: 600;
} */
</style>
