<script setup lang="ts">
defineProps<{
    type?: string;
    placeholder?: string;
    modelValue?: string;
    label?: string;
}>();

defineEmits<{ 'update:modelValue': [value: string] }>();
</script>

<template>
    <label v-if="label" class="field-label">
        {{ label }}
        <input
            class="field"
            :type="type ?? 'text'"
            :placeholder="placeholder"
            :value="modelValue"
            @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
        />
    </label>
    <input
        v-else
        class="field"
        :type="type ?? 'text'"
        :placeholder="placeholder"
        :value="modelValue"
        @input="$emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
</template>
