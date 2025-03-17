<script setup lang="ts">

import { RouterView } from "vue-router";
import Nav from "./components/nav/MainNav.vue";
import { useStorageUser } from "./API/useUser";

const currentUser = useStorageUser()

</script>

<template>
    <div :class="{ logged: !!currentUser, unlogged: !currentUser }">
        <header v-if="currentUser">
            <Nav :user="currentUser" />
        </header>
        <RouterView />
    </div>
</template>
