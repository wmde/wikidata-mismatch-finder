<template>
  <cdx-field 
      :status="validationError ? validationError.type : 'default'" 
      :messages="validationError ? validationError.message : null"
  >
      <div class="progress-bar-wrapper">
          <cdx-progress-bar v-if="loading" :aria-label="$i18n('item-form-progress-bar-aria-label')" />
      </div>
      <cdx-text-area
          :label="$i18n('item-form-id-input-label')"
          :placeholder="$i18n('item-form-id-input-placeholder')"
          :rows="8"
          :status="validationError ? validationError.type : 'default'"
          v-model="textareaInputValue"
      />
      <template #label>
			{{$i18n('item-form-id-input-label')}}
		</template>
  </cdx-field>
</template>

<script setup lang="ts">
import { ref, inject } from 'vue';
import { Ref } from 'vue';
import { useI18n } from 'vue-banana-i18n';
import { useStore } from '../store';
import { CdxTextArea, CdxField, CdxProgressBar } from "@wikimedia/codex";
import ValidationError from '../types/ValidationError';

// Run it with compat mode
// https://v3-migration.vuejs.org/breaking-changes/v-model.html
CdxTextArea.compatConfig = {
    ...CdxTextArea.compatConfig,
    COMPONENT_V_MODEL: false,
};

const validationError: Ref<ValidationError> = ref(null);

const messages = useI18n();

const store = useStore();
const textareaInputValue = ref(store.lastSearchedIds);

const MAX_NUM_IDS = inject('MAX_NUM_IDS');

defineProps<{loading: boolean}>();

function splitInput(): Array<string> {
    return textareaInputValue.value.split( '\n' );
}

function sanitizeArray(): Array<string> {
    // this filter function removes all falsy values
    // see: https://stackoverflow.com/a/281335/1619792
    return splitInput().filter(x => x);
}

function serializeInput(): string {
    return sanitizeArray().join('|');
}

function validate(): void {
    validationError.value = null;

    const typeError = 'error';

    const rules = [{
        check: (ids: Array<string>) => ids.length < 1,
        type: typeError,
        message: { [typeError]: messages.i18n('item-form-error-message-empty') as string }
    },
    {
        check: (ids: Array<string>) => ids.length > (MAX_NUM_IDS as number),
        type: typeError,
        message: { [typeError]: messages.i18n('item-form-error-message-max', MAX_NUM_IDS) as string }
    },
    {
        check: (ids: Array<string>) => !ids.every(value => /^[Qq]\d+$/.test( value.trim() )),
        type: typeError,
        message: { [typeError]: messages.i18n('item-form-error-message-invalid') as string }
    }];

    const sanitized = sanitizeArray();

    for(const {check, type, message} of rules){
        if(check(sanitized)){
            validationError.value = { type, message };
            return;
        }
    }
}

defineExpose({validate, serializeInput, validationError});

</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.cdx-field__control {
    position: relative;
    width: 100%;

    .progress-bar-wrapper {
        position: absolute;
        top: 50%;
        width: 100%;

        .cdx-progress-bar {
            width: 50%;
            margin: auto;

            @media (max-width: $width-breakpoint-tablet) {
                width: 80%;
            }
        }
    }
}

</style>
