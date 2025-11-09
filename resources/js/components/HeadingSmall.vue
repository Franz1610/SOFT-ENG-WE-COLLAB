<script setup lang="ts">
interface Props {
    title: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <header class="heading-small-container">
        <h3 class="heading-small-title">{{ title }}</h3>
        <p v-if="description" class="heading-small-description">
            {{ description }}
        </p>
    </header>
</template>

<style scoped>
.heading-small-container {
    margin-bottom: 1.5rem;
}

.heading-small-title {
    margin-bottom: 0.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: #4b824b;
    line-height: 1.25;
}

.heading-small-description {
    font-size: 0.875rem;
    color: #6b956b;
    line-height: 1.5;
}
</style>
