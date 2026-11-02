<script setup lang="ts">
import { ref } from 'vue';
import { useRegister } from '@/API/useAuth';
import { useRoute, useRouter, RouterLink } from 'vue-router';
import AuthHero from '@/components/organisms/AuthHero.vue';

const route = useRoute();
const router = useRouter();
const showPassword = ref(false);

const firstName = ref('');
const lastName = ref('');
const userName = ref('');
// Signing up is on invitation only: the mail link (/inscription?email=...&invitation=...)
// carries the invited email and a token bound to it, checked by the API.
const invitation = typeof route.query.invitation === 'string' ? route.query.invitation : '';
const email = ref(typeof route.query.email === 'string' ? route.query.email : '');
const password = ref('');
const passwordConfirmation = ref('');
const error = ref('');
const submitting = ref(false);

const onSubmit = async () => {
    error.value = '';

    if (!firstName.value || !lastName.value || !userName.value || !email.value || !password.value) {
        error.value = 'Veuillez renseigner tous les champs.';
        return;
    }
    if (password.value !== passwordConfirmation.value) {
        error.value = 'Les deux mots de passe ne correspondent pas.';
        return;
    }

    submitting.value = true;
    const result = await useRegister({
        first_name: firstName.value,
        last_name: lastName.value,
        user_name: userName.value,
        email: email.value,
        password: password.value,
        password_confirmation: passwordConfirmation.value,
        invitation,
    });
    submitting.value = false;

    if ((result as any)?.errors) {
        error.value = (result as any).errors.message || "L'inscription a échoué.";
        return;
    }

    router.push({ name: 'Home' }).then(() => window.location.reload());
};
</script>

<template>
    <div class="auth-stage">
        <AuthHero />

        <!-- Form panel -->
        <div class="auth-form-panel">
            <div v-if="!invitation || !email" class="auth-form">
                <p class="auth-form__eyebrow">Nouvelle plume</p>
                <h1 class="auth-form__title">Sur invitation</h1>

                <p class="auth-form__hint">
                    L'inscription aux Plumes se fait uniquement sur invitation. Demandez à une plume du cercle
                    de vous inviter : vous recevrez par mail un lien pour créer votre compte.
                </p>

                <p class="auth-form__switch">
                    Déjà un compte ?
                    <RouterLink :to="{ name: 'Login' }" class="auth-form__switch-link">Se connecter</RouterLink>
                </p>
            </div>

            <form v-else class="auth-form" @submit.prevent="onSubmit">
                <p class="auth-form__eyebrow">Nouvelle plume</p>
                <h1 class="auth-form__title">S'inscrire</h1>

                <div class="auth-form__fields">
                    <div class="auth-form__row">
                        <label class="field-label">
                            Prénom
                            <input v-model="firstName" class="field" type="text" placeholder="Jeanne" />
                        </label>
                        <label class="field-label">
                            Nom
                            <input v-model="lastName" class="field" type="text" placeholder="Doe" />
                        </label>
                    </div>

                    <label class="field-label">
                        Nom de plume
                        <input v-model="userName" class="field" type="text" placeholder="jeanne.plume" />
                    </label>

                    <label class="field-label">
                        Email
                        <!-- The invitation is bound to this email. -->
                        <input v-model="email" class="field" type="email" readonly />
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
                </div>

                <p v-if="error" class="auth-form__error">{{ error }}</p>

                <button type="submit" class="btn btn--primary auth-form__submit" :disabled="submitting">
                    {{ submitting ? 'Création…' : 'Rejoindre les plumes' }}
                </button>

                <p class="auth-form__switch">
                    Déjà un compte ?
                    <RouterLink :to="{ name: 'Login' }" class="auth-form__switch-link">Se connecter</RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>
