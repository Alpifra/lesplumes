<script setup lang="ts">

import { ref, shallowRef } from 'vue';
import IconPasswordNotVisible from '../icons/IconPasswordNotVisible.vue';
import IconPasswordVisible from '../icons/IconPasswordVisible.vue';

const props = defineProps<{
    name: string,
    value?: string,
    placeholder?: string,
    password?: boolean,
    label?: string,
}>();

const showPassword = ref(false)
const icon = shallowRef(IconPasswordVisible)
const togglePassword = function() {
    showPassword.value = !showPassword.value
    icon.value = showPassword.value ? IconPasswordNotVisible : IconPasswordVisible
}

</script>

<template>
    <div class="input input-text">
        <div v-if="label">
            <label :for="name">{{ label }}</label>
        </div>
        <div>
            <input
                :type="password && !showPassword ? 'password' : 'text'"
                :name="name"
                :value="value"
                :placeholder="placeholder"
                :id="name"
            />
            <icon v-if="password" @click="togglePassword" class="input-text-passwordIcon"></icon>
        </div>
    </div>
</template>