<script setup lang="ts">
import { RouterLink, useRoute } from 'vue-router';
import { computed, watch, onUnmounted } from 'vue';
import { useStorageUser } from '@/API/useUser';
import { useLogout } from '@/API/useAuth';
import { useMobileNav } from '@/composables/useMobileNav';
import BrandWordmark from '@/components/molecules/BrandWordmark.vue';

const route = useRoute();

const user = useStorageUser();

const logout = () => useLogout();

const { isOpen, close } = useMobileNav();

// While the drawer is open, lock body scroll and let Escape dismiss it.
const onKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') close();
};

watch(isOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
    if (open) {
        document.addEventListener('keydown', onKeydown);
    } else {
        document.removeEventListener('keydown', onKeydown);
    }
});

onUnmounted(() => {
    document.body.style.overflow = '';
    document.removeEventListener('keydown', onKeydown);
});

const navItems = [
    { name: 'Home',      label: 'Accueil',    icon: 'home' },
    { name: 'Session',   label: 'Sessions',   icon: 'sessions' },
    { name: 'Les Plumes', label: 'Les plumes', icon: 'plumes' },
    { name: 'Statistiques', label: 'Statistiques', icon: 'stats' },
];

const activeFamily = computed(() => {
    if (route.name === 'Session' || route.name === 'SessionDetail') return 'Session';
    return route.name as string;
});
</script>

<template>
    <div v-if="isOpen" class="sidebar-scrim" @click="close" />

    <aside class="sidebar" :class="{ 'sidebar--open': isOpen }">
        <div class="sidebar__wordmark">
            <BrandWordmark />
        </div>

        <nav class="sidebar__nav">
            <RouterLink
                v-for="item in navItems"
                :key="item.name"
                :to="{ name: item.name }"
                class="sidebar__link"
                :class="{ 'sidebar__link--active': activeFamily === item.name }"
                @click="close"
            >
                <span class="sidebar__link-indicator" />
                <svg class="sidebar__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path v-if="item.icon === 'home'" d="M3 11.5 12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1v-8.5Z" />
                    <template v-else-if="item.icon === 'sessions'">
                        <path d="M8 3h9l4 4v14H8z" />
                        <path d="M17 3v4h4M12 13h5M12 17h3" />
                    </template>
                    <!-- Feather, from Lucide (ISC). It fills the whole 24px box where
                         the other icons span ~18px: scaled down around the center, the
                         stroke widened back to the same 1.8 (1.8 / 0.85). -->
                    <g v-else-if="item.icon === 'plumes'" transform="translate(12 12) scale(0.85) translate(-12 -12)" stroke-width="2.12">
                        <path d="M12.67 19a2 2 0 0 0 1.416-.588l6.154-6.172a6 6 0 0 0-8.49-8.49L5.586 9.914A2 2 0 0 0 5 11.328V18a1 1 0 0 0 1 1z" />
                        <path d="M16 8 2 22" />
                        <path d="M17.5 15H9" />
                    </g>
                    <path v-else-if="item.icon === 'stats'" d="M4 20V10M10 20V4M16 20v-7M22 20H2" />
                </svg>
                {{ item.label }}
            </RouterLink>
        </nav>

        <div v-if="user" class="sidebar__profile">
            <span class="sidebar__profile-avatar">{{ user.first_name?.charAt(0).toUpperCase() }}</span>
            <div class="sidebar__profile-info">
                <div class="sidebar__profile-name">{{ user.first_name }} {{ user.last_name }}</div>
                <div class="sidebar__profile-role">{{ user.user_name }}</div>
            </div>
            <button class="sidebar__profile-logout" title="Se déconnecter" @click="logout">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9" />
                </svg>
            </button>
        </div>
    </aside>
</template>
