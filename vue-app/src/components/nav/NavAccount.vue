<script setup lang="ts">

import { RouterLink } from 'vue-router';
import IconPower from '../icons/IconPower.vue';
import type { User } from '@/API/useUser';
import { useLogout } from '@/API/useAuth';

const props = defineProps<{
    user: User,
}>();

</script>

<template>
    <div class="nav-account">
        <RouterLink :to="{ name: 'Profile' }">
            {{ user.user_name.length > 18  ? user.user_name.split('', 18).join('') + '...' : user.user_name }}
            <IconPower @click="useLogout"></IconPower>
        </RouterLink>
    </div>
</template>