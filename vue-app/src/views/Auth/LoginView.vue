<script setup lang="ts">

import { useLogin } from '@/API/useAuth';
import FormComponent from '@/components/form/FormComponent.vue'
import InputButton from '@/components/form/InputButton.vue';
import InputText from '@/components/form/InputText.vue'
import { RouterLink, useRouter } from 'vue-router';

const applicationName = import.meta.env.VITE_APPLICATION_NAME;
const router = useRouter();

const onSubmit = async (event: Event) => {
    const form = event.target;
    const data: { [key: string]: string } = {};

    if (!(form instanceof HTMLElement)) return;

    form.querySelectorAll('input').forEach(input => {
        data[input.name.toString()] = input.value;
    });

    const logged = await useLogin({username: data.username, password: data.password});

    if (logged) {
        router.push({ name: 'Home' })
    } else {
        // TODO display error message
        console.log(logged)
    }
}

</script>

<template>
    <main class="main login">
        <aside>
            <div>
                <h2>
                    {{ applicationName }}
                </h2>
                <p>
                    {{ applicationName }} est un collectif d'écriture formé par des amis.es, qui aiment écrire, partager et surtout se raconter des histoires.
                    Le groupe se retrouve autour de sessions d'écritures partagées avec un thème commun.<br />
                </p>
                <p>Un mot pour point de départ et un point pour caractère final.</p>
            </div>
            <div class="aside-links">
                <a href="#">Mentions légales</a>
            </div>
        </aside>
        <div class="login-container">
            <FormComponent @submit.prevent="onSubmit">
                <h1>Se connecter</h1>
                <InputText name="username" placeholder="Plume" :required=true />
                <InputText name="password" placeholder="Mot de passe" :password=true :required=true />
                <template #submit>
                    <InputButton title="Se connecter" :disabled=false shape="primary" size="m" />
                </template>
            </FormComponent>
            <small class="reset-link">
                <RouterLink :to="{ name: 'Reset Password' }">
                    Mot de passe oublié
                </RouterLink>
            </small>
        </div>
    </main>
</template>