<script setup lang="ts">

import { RouterLink } from 'vue-router';

</script>

<template>
    <div class="nav-logo">
        <RouterLink :to="{ name: 'Accueil' }">
            <img src="/logo.png"/>
        </RouterLink>
    </div>
</template>