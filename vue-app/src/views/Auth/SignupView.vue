<script setup lang="ts">
import { ref } from 'vue';
import { useRegister } from '@/API/useAuth';
import { useRouter, RouterLink } from 'vue-router';
import BrandWordmark from '@/components/molecules/BrandWordmark.vue';

const router = useRouter();
const showPassword = ref(false);

const firstName = ref('');
const lastName = ref('');
const userName = ref('');
const email = ref('');
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
        <!-- Hero panel -->
        <div class="auth-hero" style="background: linear-gradient(155deg, #5B34B0 0%, #201D3B 100%);">
            <div class="auth-hero__wordmark">
                <BrandWordmark on-dark />
            </div>

            <div class="auth-hero__body">
                <p class="auth-hero__eyebrow">Rejoignez le cercle</p>
                <h2 class="auth-hero__headline">
                    Écrire<br>ensemble,<br><em>autrement.</em>
                </h2>
            </div>
        </div>

        <!-- Form panel -->
        <div class="auth-form-panel">
            <form class="auth-form" @submit.prevent="onSubmit">
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
                        <input v-model="email" class="field" type="email" placeholder="jeanne@plumes.fr" />
                    </label>

                    <label class="field-label auth-form__password-wrap">
                        Mot de passe
                        <input
                            v-model="password"
                            class="field"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="8 caractères minimum"
                            style="padding-right: 48px;"
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
