<script setup lang="ts">
import { ref } from 'vue';
import { useLogin } from '@/API/useAuth';
import { useRouter, RouterLink } from 'vue-router';
import AuthHero from '@/components/organisms/AuthHero.vue';

const router = useRouter();
const showPassword = ref(false);
const username = ref('');
const password = ref('');
const error = ref('');

const onSubmit = async () => {
    error.value = '';

    if (!username.value.trim() || !password.value.trim()) {
        error.value = 'Veuillez renseigner votre identifiant et votre mot de passe.';
        return;
    }

    const result = await useLogin({ username: username.value, password: password.value });

    if (result && 'errors' in result && result.errors) {
        error.value = 'Identifiant ou mot de passe incorrect.';
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
            <form class="auth-form" @submit.prevent="onSubmit">
                <p class="auth-form__eyebrow">Heureux de vous revoir</p>
                <h1 class="auth-form__title">Se connecter</h1>

                <div class="auth-form__fields">
                    <label class="field-label">
                        Email ou nom d'utilisateur
                        <input v-model="username" class="field" type="text" placeholder="jeanne.doe@plumes.fr" />
                    </label>

                    <label class="field-label auth-form__password-wrap">
                        Mot de passe
                        <input
                            v-model="password"
                            class="field"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="••••••••"
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
                </div>

                <div class="auth-form__forgot">
                    <RouterLink :to="{ name: 'Reset Password' }" class="btn btn--ghost btn--small">
                        Mot de passe oublié ?
                    </RouterLink>
                </div>

                <p v-if="error" class="auth-form__error">{{ error }}</p>

                <button type="submit" class="btn btn--primary auth-form__submit">Se connecter</button>

                <p class="auth-form__switch">
                    Pas encore membre ?
                    <RouterLink :to="{ name: 'Signup' }" class="auth-form__switch-link">S'inscrire</RouterLink>
                </p>
            </form>
        </div>
    </div>
</template>
