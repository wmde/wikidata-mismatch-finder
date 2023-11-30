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
  </cdx-field>
</template>

<script lang="ts">
import { defineComponent, ref } from 'vue';
import { useStore } from '../store';
import { CdxIcon, CdxMessage, CdxTextArea, CdxField, CdxProgressBar } from "@wikimedia/codex";

// Run it with compat mode
    // https://v3-migration.vuejs.org/breaking-changes/v-model.html
    CdxTextArea.compatConfig = {
    ...CdxTextArea.compatConfig,
    COMPONENT_V_MODEL: false,
};

const MAX_NUM_IDS = 600;

export default defineComponent({
    components: {
      CdxField,
      CdxIcon,
      CdxMessage,
      CdxProgressBar,
      CdxTextArea,
    },
    setup() {
        const store = useStore();
        const textareaInputValue = ref(store.lastSearchedIds);
        
        return {
            textareaInputValue
        };
    },
    props: {
      loading: {
        type: Boolean,
        default: false
      }
    },
    methods: {
      splitInput: function(): Array<string> {
          return this.textareaInputValue.split( '\n' );
      },
      sanitizeArray: function(): Array<string> {
          // this filter function removes all falsy values
          // see: https://stackoverflow.com/a/281335/1619792
          return this.splitInput().filter(x => x);
      },
      serializeInput: function(): string {
          return this.sanitizeArray().join('|');
      },
      validate(): void {
          this.validationError = null;

          const typeError = 'error';

          const rules = [{
              check: (ids: Array<string>) => ids.length < 1,
              type: typeError,
              message: { [typeError]: this.$i18n('item-form-error-message-empty') }
          },
          {
              check: (ids: Array<string>) => ids.length > MAX_NUM_IDS,
              type: 'error',
              message: { [typeError]: this.$i18n('item-form-error-message-max', MAX_NUM_IDS) }
          },
          {
              check: (ids: Array<string>) => !ids.every(value => /^[Qq]\d+$/.test( value.trim() )),
              type: 'error',
              message: { [typeError]: this.$i18n('item-form-error-message-invalid') }
          }];

          const sanitized = this.sanitizeArray();

          for(const {check, type, message} of rules){
              if(check(sanitized)){
                  this.validationError = { type, message };
                  return;
              }
          }
      },
    },
  data() {
    return {
     validationError: null
    }
  },
});
</script>

<style lang="scss">

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
        }
    }
}

</style>
