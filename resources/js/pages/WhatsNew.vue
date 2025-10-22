<template>
  <div style="background: #232323; min-height: 100vh;">
    <!-- Header Navigation -->
    <header class="header sticky-header">
      <div class="header-inner">
        <div class="logo" @click="goHome">WECOLLAB</div>
        <nav class="nav">
          <a 
            href="#" 
            @click.prevent="handleAuthAction"
            :class="['nav-link', { 'logout-link': user }]"
          >
            {{ user ? 'Log out' : 'Log in' }}
          </a>
          <a href="#" class="nav-link">Deals & Promo</a>
          <span class="nav-link active">What's NEW?</span>
          <Link href="/booking" class="nav-link">Booking</Link>
          <Link href="/" class="nav-link">HOME</Link>
        </nav>
      </div>
    </header>

    <!-- What's New Content -->
    <main class="whats-new-content">
      <div class="whats-new-container">
        <!-- Hero Section -->
        <div class="hero-section">
          <div class="hero-text">
            <h1>WHAT'S<br>NEW?</h1>
          </div>
          <div class="hero-image">
            <img src="/images/homepage/home1.png" alt="What's New Feature" class="main-feature-image" />
          </div>
        </div>
        
        <!-- Related Products Section -->
        <div class="related-products">
          <h2>Related products</h2>
          
          <div class="products-grid">
            <!-- Combo Wombo Product -->
            <div class="product-card">
              <img src="/images/homepage/Combo_Meal.png" alt="Combo Wombo" class="product-image" />
              <div class="product-info">
                <h3>COMBO WOMBO</h3>
                <p class="product-description">Crispy-filled flour-coated tofu included</p>
                <p class="product-price">₱10.99</p>
              </div>
            </div>
            
            <!-- Ube and Caramel Flakes Product -->
            <div class="product-card">
              <img src="/images/homepage/Ube_Caramel.png" alt="Ube and Caramel Flakes" class="product-image" />
              <div class="product-info">
                <h3>UBE AND CARAMEL FLAKES</h3>
                <p class="product-description">Description of first product</p>
                <p class="product-price">₱10.99</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

// Get authentication data from Inertia page props
const page = usePage()
const user = computed(() => page.props.auth?.user)

// Handle authentication actions
const handleAuthAction = () => {
  if (user.value) {
    // User is logged in, handle logout
    router.post('/logout')
  } else {
    // User is not logged in, redirect to login
    router.visit('/login')
  }
}

// Handle logo and home button clicks
const goHome = () => {
  router.visit('/')
}
</script>

<style scoped>
/* Header Styles (matching Home page) */
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
  color: #fff;
  cursor: pointer;
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

.nav-link.active {
  background: #fff;
  color: #495846;
  font-weight: 600;
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

/* What's New Content Styles */
.whats-new-content {
  min-height: 100vh;
  background: #f5f3ed;
  margin-top: 54px; /* Account for fixed header */
  padding: 2rem 0;
}

.whats-new-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.hero-section {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 3rem;
  padding: 2rem 0;
  background: #f7f3e9;
  border-radius: 12px;
  margin-top: 2rem;
}

.hero-text {
  flex: 1;
  padding-left: 3rem;
}

.hero-text h1 {
  font-size: 5rem;
  font-weight: bold;
  color: #2d3748;
  margin: 0;
  letter-spacing: -0.02em;
  line-height: 0.9;
}

.hero-image {
  flex: 1;
  display: flex;
  justify-content: center;
  padding-right: 2rem;
}

.main-feature-image {
  width: 100%;
  max-width: 600px;
  height: auto;
  border-radius: 12px;
  border: 4px solid #495846;
}

.related-products {
  margin-top: 3rem;
}

.related-products h2 {
  font-size: 1.5rem;
  color: #2d3748;
  margin-bottom: 2rem;
  text-align: center;
}

.products-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 6rem;
  max-width: 800px;
  margin: 0 auto;
}

.product-card {
  background: #4a5568;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.product-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
}

.product-image {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.product-info {
  padding: 1.5rem;
  color: #ffffff;
  text-align: center;
}

.product-info h3 {
  font-size: 1.1rem;
  font-weight: bold;
  margin: 0 0 0.5rem 0;
  color: #ffffff;
}

.product-description {
  font-size: 0.9rem;
  color: #e2e8f0;
  margin: 0.5rem 0 1rem 0;
  line-height: 1.4;
}

.product-price {
  font-size: 1.25rem;
  font-weight: bold;
  color: #68d391;
  margin: 0;
}

/* Responsive Design */
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
}

@media (max-width: 768px) {
  .hero-section {
    flex-direction: column;
    text-align: center;
    padding: 2rem 1rem;
  }

  .hero-text {
    padding-left: 0;
    margin-bottom: 2rem;
  }

  .hero-text h1 {
    font-size: 3rem;
  }

  .hero-image {
    padding-right: 0;
  }

  .whats-new-container {
    padding: 0 1rem;
  }

  .products-grid {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
}

@media (max-width: 600px) {
  .header-inner {
    padding: 0.5rem 0.5rem;
  }
}
</style>
