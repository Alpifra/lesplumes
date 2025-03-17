<script setup lang="ts">

// import icons
import IconHome from '../icons/IconHome.vue';
import IconSession from '../icons/IconSession.vue';
import IconParticipant from '../icons/IconParticipant.vue';
import { RouterLink } from 'vue-router';

const props = defineProps<{
    title: string,
}>();

let icon = IconHome;

switch (props.title) {
    case 'Home':
        icon = IconHome;
        break;
    case 'Session':
        icon = IconSession;
        break;
    case 'Les Plumes':
        icon = IconParticipant;
        break;
}

</script>

<template>
    <div class="nav-link">
        <RouterLink :to="{ name: title }">
            <icon></icon>
            {{ title }}
        </RouterLink>
    </div>
</template>