<script setup lang="ts">

import { RouterView } from "vue-router";
import Nav from "./components/nav/MainNav.vue";

const currentUser = localStorage.getItem('user');

</script>

<template>
    <header v-if="currentUser">
        <Nav :user="currentUser" />
    </header>
    <RouterView />
</template>
