<script setup lang="ts">

import router from '@/router';

const currentRoute = router.currentRoute.value;

</script>

<template>
    <main class="main">
        <h1>{{ currentRoute.name }}</h1>

        <div class="container">
            <h3 class="container-subtitle">Session d'écriture en cours</h3>
            <div class="container-title"></div>
        </div>
    </main>
</template>
