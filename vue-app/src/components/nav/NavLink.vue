<script setup lang="ts">

// import icons
import IconHome from '../icons/IconHome.vue';
import IconSession from '../icons/IconSession.vue';
import IconParticipant from '../icons/IconParticipant.vue';

const props = defineProps<{
    title: string,
    targetUrl: string,
    active?: boolean,
}>();

let icon = IconHome;

switch (props.title) {
    case 'Accueil':
        icon = IconHome;
        break;
    case 'Session':
        icon = IconSession;
        break;
    case 'Les plumes':
        icon = IconParticipant;
        break;
}

</script>

<template>
    <div
        class="nav-link"
        :class="{ active: active }"
    >
        <a href="{{ targetUrl }}">
            <icon></icon>
            {{ title }}
        </a>
    </div>
</template>