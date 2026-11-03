<script setup lang="ts">
import { ref } from 'vue';
import { useRoute, RouterLink } from 'vue-router';
import { useResetPassword } from '@/API/useAuth';
import AuthHero from '@/components/organisms/AuthHero.vue';

const route = useRoute();
const token = String(route.query.token ?? '');
const email = String(route.query.email ?? '');

const showPassword = ref(false);
const password = ref('');
const passwordConfirmation = ref('');
const message = ref('');
const error = ref(token && email ? '' : 'Ce lien de réinitialisation est incomplet.');
const submitting = ref(false);

const onSubmit = async () => {
    error.value = '';

    if (!password.value) {
        error.value = 'Veuillez renseigner un nouveau mot de passe.';
        return;
    }
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Les deux mots de passe ne correspondent pas.';
        return;
    }

    submitting.value = true;
    const result = await useResetPassword({
        token,
        email,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
    });
    submitting.value = false;

    if (result.errors) {
        error.value = result.errors.message || 'La réinitialisation a échoué.';
        return;
    }

    message.value = 'Votre mot de passe a été modifié, vous pouvez vous connecter.';
    password.value = '';
    passwordConfirmation.value = '';
};
</script>

<template>
    <div class="auth-stage">
        <AuthHero />

        <!-- Form panel -->
        <div class="auth-form-panel">
            <form class="auth-form" @submit.prevent="onSubmit">
                <p class="auth-form__eyebrow">Un nouveau départ</p>
                <h1 class="auth-form__title">Nouveau mot de passe</h1>

                <div class="auth-form__fields">
                    <label class="field-label">
                        Email
                        <input :value="email" class="field" type="email" readonly />
                    </label>

                    <label class="field-label auth-form__password-wrap">
                        Mot de passe
                        <input
                            v-model="password"
                            class="field"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="8 caractères minimum"
                        />
                        <button type="button" class="auth-form__eye" @click="showPassword = !showPassword">
                            <svg v-if="showPassword" viewBox="0 0 24 24" fill="none" stroke="#89868D" stroke-width="1.8" stroke-linecap="round" style="pointer-events:none">
                                <path d="M3 3l18 18M10.5 6.5A9 9 0 0 1 12 6c6.5 0 10 6 10 6a17 17 0 0 1-3.2 3.8M6.3 6.3A17 17 0 0 0 2 12s3.5 6 10 6a10 10 0 0 0 3.7-.7M9 9.5a3 3 0 0 0 4.2 4.2"/>
                            </svg>
                            <svg v-else viewBox="0 0 24 24" fill="none" stroke="#89868D" stroke-width="1.8" stroke-linecap="round" style="pointer-events:none">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </label>

                    <label class="field-label">
                        Confirmer le mot de passe
                        <input
                            v-model="passwordConfirmation"
                            class="field"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
                        />
                    </label>

                    <p v-if="message" class="auth-form__success">{{ message }}</p>
                </div>

                <p v-if="error" class="auth-form__error">{{ error }}</p>

                <button type="submit" class="btn btn--primary auth-form__submit" :disabled="submitting || !!message">
                    {{ submitting ? 'Enregistrement…' : 'Changer mon mot de passe' }}
                </button>

                <p class="auth-form__switch">
                    <RouterLink :to="{ name: 'Login' }" class="auth-form__switch-link">Se connecter</RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>
