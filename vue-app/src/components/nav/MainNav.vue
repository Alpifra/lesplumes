<script setup lang="ts">

import NavLink from './NavLink.vue'
import NavLogo from './NavLogo.vue'
import { navRoutes } from '@/router/routes';

</script>

<template>
    <div class="nav">
        <NavLogo />
        <div v-for="route in navRoutes">
            <NavLink :title=route.name />
        </div>
    </div>
</template>