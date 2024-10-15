<script setup lang="ts">

import { useUser } from '@/API/useUser';
import {ref} from 'vue'

const logoutRoute = ref({});
const user = ref(null);

let demo = await useUser(1).then((demo) => demo)

</script>

<template>
    <div class="nav-account">
        <div class="nav-link">
            <RouterLink :to="{ name: 'Profil' }">
                Hello
            </RouterLink>
        </div>
    </div>
</template>