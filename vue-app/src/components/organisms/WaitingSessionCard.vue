<script setup lang="ts">
import type { User } from '@/API/useUser';
import RotationOrder from '@/components/molecules/RotationOrder.vue';
import UserAvatar from '@/components/molecules/UserAvatar.vue';

defineProps<{
    /** The plume whose turn it is to pick the word — never the reader here. */
    selector: User;
    plumes: User[];
}>();
</script>

<template>
    <div class="card new-session-card">
        <div class="eyebrow eyebrow--soft">
            <span class="eyebrow__dot" />
            <span class="eyebrow__label">Prochaine session en préparation</span>
        </div>

        <div class="new-session-card__plume">
            <UserAvatar :name="`${selector.first_name} ${selector.last_name}`" :size="72" :index="selector.id" />
            <div>
                <h2 class="new-session-card__headline">{{ selector.first_name }} {{ selector.last_name }}</h2>
                <p class="new-session-card__script">choisit le mot de la prochaine session</p>
            </div>
        </div>

        <div class="new-session-card__wait">
            <svg viewBox="0 0 24 24" fill="none" stroke="#9255FD" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;flex-shrink:0;pointer-events:none"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
            <span>La session va commencer. Vous serez prévenu dès que le mot sera dévoilé.</span>
        </div>

        <RotationOrder :plumes="plumes" :selector-id="selector.id" />
    </div>
</template>
