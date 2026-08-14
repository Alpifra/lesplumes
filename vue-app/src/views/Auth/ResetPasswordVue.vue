<script setup lang="ts">
import { ref } from 'vue';
import { RouterLink } from 'vue-router';
import { useForgotPassword } from '@/API/useAuth';
import AuthHero from '@/components/organisms/AuthHero.vue';

const email = ref('');
const message = ref('');
const error = ref('');
const submitting = ref(false);

const onSubmit = async () => {
    message.value = '';
    error.value = '';

    if (!email.value.trim()) {
        error.value = 'Veuillez renseigner votre email.';
        return;
    }

    submitting.value = true;
    const result = await useForgotPassword(email.value.trim());
    submitting.value = false;

    if (result.errors) {
        error.value = result.errors.message || "L'envoi a échoué.";
        return;
    }

    message.value = 'Si un compte existe, un lien de réinitialisation vient d\'être envoyé.';
    email.value = '';
};
</script>

<template>
    <div class="auth-stage">
        <AuthHero />

        <!-- Form panel -->
        <div class="auth-form-panel">
            <form class="auth-form" @submit.prevent="onSubmit">
                <p class="auth-form__eyebrow">Une plume oublieuse</p>
                <h1 class="auth-form__title">Mot de passe oublié</h1>

                <p class="auth-form__hint">
                    Saisissez votre email et nous vous enverrons un lien pour choisir un nouveau mot de passe.
                </p>

                <div class="auth-form__fields">
                    <label class="field-label">
                        Email
                        <input v-model="email" class="field" type="email" placeholder="jeanne@plumes.fr" />
                    </label>

                    <p v-if="message" class="auth-form__hint" style="color:#2E7D5B">{{ message }}</p>
                </div>

                <p v-if="error" class="auth-form__error">{{ error }}</p>

                <button type="submit" class="btn btn--primary auth-form__submit" :disabled="submitting">
                    {{ submitting ? 'Envoi…' : 'Réinitialiser mon mot de passe' }}
                </button>

                <div class="auth-form__switch">
                    <RouterLink :to="{ name: 'Login' }" class="btn btn--ghost btn--small">
                        ← Retour à la connexion
                    </RouterLink>
                </div>
            </form>
        </div>
    </div>
</template>
