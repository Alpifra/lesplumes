<script setup lang="ts">

import { useStorageUser } from '@/API/useUser';
import NavAccount from './NavAccount.vue';
import NavLink from './NavLink.vue'
import NavLogo from './NavLogo.vue'
import { navRoutes } from '@/router/routes';

const user = useStorageUser()

</script>

<template>
    <div class="nav">
        <div>
            <NavLogo />
            <div v-for="route in navRoutes">
                <NavLink :title="(route.name as string)"/>
            </div>
        </div>
        <NavAccount :user="user" />
    </div>
</template>