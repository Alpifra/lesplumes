<script setup lang="ts">

import NavAccount from './NavAccount.vue';
import NavLink from './NavLink.vue'
import NavLogo from './NavLogo.vue'
import { navRoutes } from '@/router/routes';

</script>

<template>
    <div class="nav">
        <div>
            <NavLogo />
            <div v-for="route in navRoutes">
                <NavLink :title="route.name" />
            </div>
        </div>
        <NavAccount />
    </div>
</template>