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
                <p class="auth-form__eyebrow">Un petit oubli</p>
                <h1 class="auth-form__title">Mot de passe oublié</h1>

                <p class="auth-form__hint">
                    Saisissez votre email et nous vous enverrons un lien pour choisir un nouveau mot de passe.
                </p>

                <div class="auth-form__fields">
                    <label class="field-label">
                        Email
                        <input v-model="email" class="field" type="email" placeholder="jeanne@plumes.fr" />
                    </label>

                    <p v-if="message" class="auth-form__success">{{ message }}</p>
                </div>

                <p v-if="error" class="auth-form__error">{{ error }}</p>

                <button type="submit" class="btn btn--primary auth-form__submit" :disabled="submitting">
                    {{ submitting ? 'Envoi…' : 'Réinitialiser mon mot de passe' }}
                </button>

                <p class="auth-form__switch">
                    Vous vous en souvenez ?
                    <RouterLink :to="{ name: 'Login' }" class="auth-form__switch-link">Se connecter</RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>
