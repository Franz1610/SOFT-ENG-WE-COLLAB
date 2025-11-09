<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="heading-container">
        <h2 class="heading-title">{{ title }}</h2>
        <p v-if="description" class="heading-description">
            {{ description }}
        </p>
    </div>
</template>

<style scoped>
.heading-container {
    margin-bottom: 2rem;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.heading-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: #4b824b;
    line-height: 1.25;
    letter-spacing: -0.025em;
}

.heading-description {
    font-size: 0.875rem;
    color: #6b956b;
    line-height: 1.5;
}
</style>
