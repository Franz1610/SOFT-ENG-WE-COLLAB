<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  booking: Object,
  feedback: Object,
});

const rating = ref(props.feedback?.rating || 0);
const comment = ref(props.feedback?.comment || '');
const isEdit = !!props.feedback;

function submitFeedback() {
  if (!rating.value) {
    alert('Please select a rating.');
    return;
  }
  if (!props.booking || !props.booking.id) {
    alert('Booking information is missing.');
    return;
  }
  const method = isEdit ? 'put' : 'post';
  router[method](
    `/bookings/${props.booking.id}/feedback`,
    {
      rating: rating.value,
      comment: comment.value,
    },
    {
      onSuccess: () => {
        alert(isEdit ? 'Feedback updated.' : 'Feedback submitted.');
      },
    }
  );
}

function deleteFeedback() {
  if (!props.booking || !props.booking.id) {
    alert('Booking information is missing.');
    return;
  }
  if (confirm('Delete your feedback?')) {
    router.delete(`/bookings/${props.booking.id}/feedback`, {
      onSuccess: () => alert('Feedback deleted.'),
    });
  }
}
</script>

<template>
  <div class="feedback-form">
    <h2>Booking Feedback for #{{ booking?.id }}</h2>
    <div>
      <label>Rating:</label>
      <select v-model="rating">
        <option value="">Select</option>
        <option v-for="r in [1,2,3,4,5]" :key="r" :value="r">{{ r }} Star{{ r > 1 ? 's' : '' }}</option>
      </select>
    </div>
    <div>
      <label>Comment:</label>
      <textarea v-model="comment" rows="4" placeholder="Your feedback..."></textarea>
    </div>
    <button @click="submitFeedback">{{ isEdit ? 'Update' : 'Submit' }} Feedback</button>
    <button v-if="isEdit" @click="deleteFeedback" style="margin-left:1em;">Delete</button>
  </div>
</template>