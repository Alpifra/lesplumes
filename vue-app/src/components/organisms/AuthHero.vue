<script setup lang="ts">
import BrandWordmark from '@/components/molecules/BrandWordmark.vue';

const bullets = [
    'Rejoindre le cercle des plumes',
    "Participer aux sessions d'écriture autour d'un mot",
    'Retrouver et télécharger les écrits des sessions passées',
];
</script>

<template>
    <div class="auth-hero">
        <div class="auth-hero__wordmark">
            <BrandWordmark on-dark />
        </div>

        <div class="auth-hero__body">
            <p class="auth-hero__eyebrow">Bienvenue</p>
            <h2 class="auth-hero__headline">
                Un mot pour<br><em>mille plumes.</em>
            </h2>

            <ul class="auth-hero__bullets">
                <li v-for="bullet in bullets" :key="bullet">
                    <span class="auth-hero__check">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#B388FE" stroke-width="2.2" stroke-linecap="round"><path d="M4 13l5 5L20 6"/></svg>
                    </span>
                    {{ bullet }}
                </li>
            </ul>
        </div>
    </div>
</template>
