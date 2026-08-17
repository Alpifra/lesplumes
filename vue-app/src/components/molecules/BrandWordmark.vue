<script setup lang="ts">
defineProps<{
    onDark?: boolean;
}>();
</script>

<template>
    <div class="wordmark" :class="{ 'wordmark--on-dark': onDark }">
        <img
            class="wordmark__logo"
            :src="onDark ? '/logo-v2-w.svg' : '/logo-v2.svg'"
            alt="Les Plumes"
            width="38"
            height="38"
        />
        <div class="wordmark__text">
            <span class="wordmark__name">Les Plumes</span>
            <span class="wordmark__tagline">cercle d'écriture</span>
        </div>
    </div>
</template>
