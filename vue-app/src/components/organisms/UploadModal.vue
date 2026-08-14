<script setup lang="ts">
import { ref } from 'vue';
import { useCreateStory } from '@/API/useStory';

const props = defineProps<{
    roundId: number;
    roundWord: string;
    initialTitle?: string;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
    (e: 'deposited'): void;
}>();

const title = ref(props.initialTitle ?? '');
const uploadedFile = ref<File | null>(null);
const isDragging = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const uploadError = ref('');
const submitting = ref(false);

const onFileSelected = (file: File) => {
    if (file.type !== 'application/pdf') {
        uploadError.value = 'Seuls les fichiers PDF sont acceptés.';
        return;
    }
    if (file.size > 2 * 1024 * 1024) {
        uploadError.value = 'Le fichier dépasse la limite de 2 Mo.';
        return;
    }
    uploadError.value = '';
    uploadedFile.value = file;
};

const onInputChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) onFileSelected(file);
};

const onDrop = (e: DragEvent) => {
    isDragging.value = false;
    const file = e.dataTransfer?.files?.[0];
    if (file) onFileSelected(file);
};

const formatSize = (bytes: number) =>
    bytes < 1024 * 1024
        ? `${(bytes / 1024).toFixed(0)} Ko`
        : `${(bytes / (1024 * 1024)).toFixed(1)} Mo`;

const submit = async () => {
    if (!uploadedFile.value || submitting.value) return;

    submitting.value = true;
    const response = await useCreateStory(props.roundId, {
        title: title.value.trim() || undefined,
        file: uploadedFile.value,
    });
    submitting.value = false;

    if (response.errors) {
        uploadError.value = response.errors.message || 'Le dépôt a échoué.';
        return;
    }

    emit('deposited');
    emit('close');
};
</script>

<template>
    <Teleport to="body">
        <div class="modal-overlay" @click="emit('close')">
            <div class="modal card" @click.stop>
                <button class="modal__close" @click="emit('close')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#363062" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px;pointer-events:none"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
                <p class="auth-form__eyebrow">Déposer ta plume</p>
                <h2 class="modal__title">Rédaction « {{ roundWord }} »</h2>
                <svg viewBox="0 0 220 24" fill="none" style="margin-left:-6px;pointer-events:none"><path d="M 4 14 Q 40 2 82 12 T 166 14 Q 196 14 214 6" stroke="#9255FD" stroke-width="2.2" stroke-linecap="round"/></svg>

                <label class="field-label" style="margin-top:18px">
                    Titre du texte <span class="data-table__muted">(optionnel)</span>
                    <input v-model="title" class="field" type="text" placeholder="Ex : Un après-midi tarabiscoté" />
                </label>

                <input
                    ref="fileInput"
                    type="file"
                    accept=".pdf,application/pdf"
                    style="display:none"
                    @change="onInputChange"
                />

                <div
                    class="modal__dropzone"
                    :class="{ 'modal__dropzone--active': isDragging }"
                    @dragenter.prevent="isDragging = true"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="onDrop"
                >
                    <template v-if="uploadedFile">
                        <div class="modal__file">
                            <div class="modal__file-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#9255FD" stroke-width="1.8" stroke-linecap="round" style="width:22px;height:22px;pointer-events:none"><path d="M7 3h8l4 4v14H7z"/><path d="M15 3v4h4"/></svg>
                            </div>
                            <div class="modal__file-info">
                                <div class="modal__file-name">{{ uploadedFile.name }}</div>
                                <div class="modal__file-size">{{ formatSize(uploadedFile.size) }} · PDF</div>
                            </div>
                            <button class="modal__file-remove" @click="uploadedFile = null; uploadError = ''">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#89868D" stroke-width="1.8" stroke-linecap="round" style="width:16px;height:16px;pointer-events:none"><path d="M6 6l12 12M18 6 6 18"/></svg>
                            </button>
                        </div>
                    </template>

                    <template v-else>
                        <div class="modal__upload-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#9255FD" stroke-width="1.8" stroke-linecap="round" style="width:28px;height:28px;pointer-events:none"><path d="M12 19V7M6 13l6-6 6 6M4 21h16"/></svg>
                        </div>
                        <div class="modal__dropzone-title">Déposez votre rédaction ici</div>
                        <div class="modal__dropzone-hint">Document au format .PDF · 2&nbsp;Mo maximum</div>
                        <button class="btn btn--outline btn--small" style="margin-top:16px" @click="fileInput?.click()">
                            Parcourir mes fichiers
                        </button>
                    </template>

                    <p v-if="uploadError" class="modal__error">{{ uploadError }}</p>
                </div>

                <div class="modal__actions">
                    <button class="btn btn--outline" @click="emit('close')">Annuler</button>
                    <button class="btn btn--primary" :disabled="!uploadedFile || submitting" @click="submit">
                        {{ submitting ? 'Envoi…' : 'Déposer ma plume' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
