<script setup lang="ts">

import FormComponent from '@/components/form/FormComponent.vue';
import InputButton from '@/components/form/InputButton.vue';
import InputText from '@/components/form/InputText.vue'

const applicationName = import.meta.env.VITE_APPLICATION_NAME;

const onSubmit = () => {
    console.log('coucou')
}

</script>

<template>
    <main class="main reset-password">
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
            <FormComponent :onSubmit="onSubmit">
                <h1>Mot de passe oublié</h1>
                <InputText name="email" placeholder="Email" :required=true />
                <template #submit>
                    <InputButton title="Réinitialiser mon mot de passe" :disabled=true shape="primary" size="m" />
                </template>
            </FormComponent>
            <small class="reset-link">
                <RouterLink :to="{ name: 'Login' }">
                    Page de connexion
                </RouterLink>
            </small>
        </div>
    </main>
</template>