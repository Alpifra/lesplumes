<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppSidebar from '@/components/organisms/AppSidebar.vue';
import AppTopBar from '@/components/organisms/AppTopBar.vue';
import { useUsers } from '@/API/useUser';
import type { User } from '@/API/useUser';
import { useInvite } from '@/API/useInvitation';
import { initials, avatarHue } from '@/utils/session';

const plumes = ref<User[]>([]);
const loading = ref(true);

const inviteEmail = ref('');
const inviting = ref(false);
const inviteMessage = ref('');
const inviteError = ref('');

const memberSince = (date: string) =>
    new Date(date).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' });

const load = async () => {
    loading.value = true;
    const response = await useUsers();
    plumes.value = (response.data as User[]) ?? [];
    loading.value = false;
};

const invite = async () => {
    inviteMessage.value = '';
    inviteError.value = '';

    if (!inviteEmail.value.trim()) {
        inviteError.value = 'Renseignez une adresse email.';
        return;
    }

    inviting.value = true;
    const response = await useInvite(inviteEmail.value.trim());
    inviting.value = false;

    if (response.errors) {
        inviteError.value = response.errors.message || "L'invitation a échoué.";
        return;
    }

    const status = (response.data as any)?.status;
    inviteMessage.value = status === 'exists'
        ? 'Cette plume fait déjà partie du cercle.'
        : 'Invitation envoyée !';
    inviteEmail.value = '';
    if (status === 'exists') load();
};

onMounted(load);
</script>

<template>
    <div class="page-shell">
        <AppSidebar />

        <div class="page-main">
            <AppTopBar title="Les plumes" subtitle="celles et ceux qui écrivent" />

            <div class="page-content">
                <div class="plumes-grid">
                    <div v-for="(plume, i) in plumes" :key="plume.id" class="card plume-card">
                        <div class="plume-card__identity">
                            <span class="user-avatar" :style="{width:'56px',height:'56px',fontSize:'22px',background:`hsl(${avatarHue(i)},60%,86%)`,color:`hsl(${avatarHue(i)},45%,30%)`}">{{ initials(`${plume.first_name} ${plume.last_name}`) }}</span>
                            <div>
                                <div class="plume-card__name">{{ plume.first_name }} {{ plume.last_name }}</div>
                                <div class="plume-card__role">@{{ plume.user_name }}</div>
                            </div>
                        </div>

                        <div class="plume-card__stats">
                            <div>
                                <div class="plume-card__stat-value" style="font-size:15px;text-transform:capitalize">{{ memberSince(plume.created_at) }}</div>
                                <div class="plume-card__stat-label">membre depuis</div>
                            </div>
                        </div>

                        <a class="btn btn--outline btn--small" style="width:100%" :href="`mailto:${plume.email}`">Contacter</a>
                    </div>

                    <!-- Invite card -->
                    <div class="card plume-card plume-card--invite">
                        <div class="plume-card__invite-icon">+</div>
                        <div class="plume-card__name" style="margin-top:12px">Inviter une plume</div>
                        <p class="plume-card__stat-label" style="margin-top:4px;max-width:240px;text-align:center">Envoyez une invitation à rejoindre le cercle des plumes.</p>

                        <input
                            v-model="inviteEmail"
                            class="field"
                            type="email"
                            placeholder="email@exemple.fr"
                            style="margin-top:14px;text-align:center"
                            @keyup.enter="invite"
                        />
                        <button class="btn btn--primary btn--small" style="margin-top:10px" :disabled="inviting" @click="invite">
                            {{ inviting ? 'Envoi…' : 'Inviter' }}
                        </button>
                        <p v-if="inviteMessage" class="plume-card__invite-feedback">{{ inviteMessage }}</p>
                        <p v-if="inviteError" class="plume-card__invite-feedback plume-card__invite-feedback--error">{{ inviteError }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
