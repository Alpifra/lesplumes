<script setup lang="ts">
import { RouterView } from "vue-router";
import Nav from "./components/nav/MainNav.vue";
</script>

<template>
    <header>
        <Nav />
    </header>
    <RouterView />
</template>
