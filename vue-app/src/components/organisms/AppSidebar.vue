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
                    <template v-else-if="item.icon === 'plumes'">
                        <path d="M3 21c3-1 7-4 10-9 3-5 8-8 8-8s-1 6-4 10-8 7-12 9" />
                        <path d="M3 21 9 15" />
                    </template>
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
