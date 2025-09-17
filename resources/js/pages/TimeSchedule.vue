<template>
  <div class="schedule-page">
    <h1>Choose Date and Time for Your Booking</h1>
    <form @submit.prevent="submitSchedule">
      <div>
        <label for="date">Date:</label>
        <input type="date" v-model="date" id="date" required />
      </div>
      <div>
        <label for="time">Time:</label>
        <input type="time" v-model="time" id="time" required />
      </div>
      <button type="submit">Confirm Schedule</button>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const date = ref('')
const time = ref('')
const router = useRouter()

function submitSchedule() {
  // You can send the selected date and time to the backend here
  // For now, just redirect to a confirmation or summary page
  router.push({ name: 'BookingSummary', query: { date: date.value, time: time.value } })
}
</script>

<style scoped>
.schedule-page {
  max-width: 400px;
  margin: 2rem auto;
  padding: 2rem;
  background: #fff;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
.schedule-page h1 {
  margin-bottom: 1.5rem;
}
.schedule-page form > div {
  margin-bottom: 1rem;
}
</style>
