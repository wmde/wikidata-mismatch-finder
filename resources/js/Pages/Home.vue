<template>
    <div class="page-container home-page">
        <inertia-head title="Mismatch Finder" />
        <section id="description-section">
            <header class="description-header">
                <h2 class="h4">{{ $i18n('about-mismatch-finder-title') }}</h2>
                <cdx-button
                    id="faq-button"
                    weight="quiet"
                    action="progressive"
                    @click="faqDialog = true"
                >
                    <cdx-icon :icon="cdxIconInfo" />
                    {{ $i18n('faq-button') }}
                </cdx-button>
            </header>
            <cdx-dialog id="faq-dialog"
                        v-model:open="faqDialog"
                        :title="$i18n('faq-dialog-title')"
                        :primary-action="{
                            label: $i18n('confirm-dialog-button'),
                            namespace: 'faq-confirm',
                            actionType: 'progressive'
                        }"
                        @primary="() => faqDialog = false"
                        close-button-label="X"
            >
                <section>
                    <h3 class="h5">{{ $i18n('faq-dialog-question-finding-mismatches' )}}</h3>
                    <p>{{ $i18n('faq-dialog-answer-finding-mismatches') }}</p>
                    <ul>
                        <li>{{ $i18n('faq-dialog-answer-finding-mismatches-sources-1') }}</li>
                        <li>{{ $i18n('faq-dialog-answer-finding-mismatches-sources-2') }}</li>
                        <li>{{ $i18n('faq-dialog-answer-finding-mismatches-sources-3') }}</li>
                    </ul>
                </section>
                <section>
                    <h3 class="h5">{{ $i18n('faq-dialog-question-relevance') }}</h3>
                    <p>{{ $i18n('faq-dialog-answer-relevance') }}</p>
                </section>
                <section>
                    <h3 class="h5">{{ $i18n('faq-dialog-question-contributing') }}</h3>
                    <p v-i18n-html:faq-dialog-answer-contributing="[
                        'https://phabricator.wikimedia.org/'
                    ]"></p>
                </section>
                <section>
                    <h3 class="h5">{{ $i18n('faq-dialog-question-more-info') }}</h3>
                    <p v-i18n-html:faq-dialog-answer-more-info="[
                        'https://github.com/wmde/wikidata-mismatch-finder',
                        'https://www.wikidata.org/wiki/Wikidata:Mismatch_Finder',
                        'https://www.wikidata.org/wiki/Wikidata_talk:Mismatch_Finder'
                    ]"></p>
                </section>
            </cdx-dialog>
            <p id="about-description" >
                {{ $i18n('about-mismatch-finder-description') }}
            </p>
        </section>

        <section id="message-section">
            <cdx-message v-if="unexpectedError || serversideValidationError" type="error">
                <span>{{ $i18n('server-error') }}</span>
            </cdx-message>
        </section>

        <section id="querying-section">
            <div class="heading">
                <h2 class="h5">{{ $i18n('item-form-title') }}</h2>
                <cdx-button
                    class="random-mismatches"
                    weight="normal"
                    @click="showRandom()"
                    :disabled="loading"
                >
                    <cdx-icon :icon="cdxIconDie" />
                    {{ $i18n('random-mismatches') }}
                </cdx-button>
            </div>
            <form id="items-form" @submit.prevent="send">
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
                <div class="form-buttons">
                    <cdx-button
                        class="submit-ids"
                        weight="primary"
                        action="progressive"
                        native-type="submit"
                        :disabled="loading"
                    >
                        {{ $i18n('item-form-submit') }}
                    </cdx-button>
                </div>
            </form>
        </section>
    </div>
</template>

<script lang="ts">
    import { Head as InertiaHead } from '@inertiajs/inertia-vue3';
    import { mapState } from 'pinia';
    import { useStore } from '../store';
    import { CdxDialog, CdxButton, CdxIcon, CdxMessage, CdxTextArea, CdxField, CdxProgressBar } from "@wikimedia/codex";
    import { cdxIconDie, cdxIconInfo } from '@wikimedia/codex-icons';
    import { defineComponent, ref } from 'vue';

    interface HomeState {
        form: {
            itemsInput: string
        },
        validationError: null|{
            type: string,
            message: object
        },
      faqDialog: boolean
    }

    interface ErrorMessages {
        [ key : string ] : string
    }

    interface FlashMessages {
        errors : { [ key : string ] : string }
    }

    export const MAX_NUM_IDS = 600;

    // Run it with compat mode
    // https://v3-migration.vuejs.org/breaking-changes/v-model.html
     CdxTextArea.compatConfig = {
        ...CdxTextArea.compatConfig,
        COMPONENT_V_MODEL: false,
    };

    export default defineComponent({
        components: {
          CdxDialog,
          CdxButton,
          CdxField,
          CdxIcon,
          CdxMessage,
          CdxProgressBar,
          CdxTextArea,
          InertiaHead
        },
        setup() {
            
            const store = useStore();
            const textareaInputValue = ref(store.lastSearchedIds);
            return {
                cdxIconDie,
                cdxIconInfo,
                textareaInputValue
            };
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
            send(): void {
                this.validate();

                if(this.validationError) {
                    return;
                }
                const store = useStore();
                store.saveSearchedIds( this.textareaInputValue );
                this.$inertia.get( '/results', { ids: this.serializeInput() } );
            },
            showRandom(): void {
                this.$inertia.get( '/random' );
            },
        },
        computed: {
            serversideValidationError() {
                const errors = this.$page.props.errors as ErrorMessages;
                return errors && Object.keys(errors).length > 0;
            },
            unexpectedError() {
                const flashMessages = this.$page.props.flash as FlashMessages;
                return (flashMessages.errors && flashMessages.errors.unexpected);
            },
            // spread to combine with local computed props
            // only mapping 'loading' and not 'lastSearchedIds' because computed
            //properties are not available when data is processed in vue's lifecycle
            ...mapState(useStore, ['loading']),
        },
        data(): HomeState {
            const store = useStore();
            return {
                form: {
                    itemsInput: store.lastSearchedIds
                },
                validationError: null,
                faqDialog: false,
            }
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

#querying-section .heading {
    display: flex;
    justify-content: space-between;
    align-items: center;

    h2 {
        // the previous section already has enough margin-bottom
        margin-top: 0;
    }
}

#items-form {
    /**
    * Colors
    */
    background-color: $background-color-neutral-default;

    /**
    * Border
    */
    border-style: $border-style-base;
    border-width: $border-width-thin;
    border-color: $border-color-base-subtle;
    border-radius: $border-radius-base;

    /**
    * Layout
    */
    padding: $dimension-spacing-large;
    margin: $dimension-layout-xsmall 0;

    // Any direct decendent of this form that has a predecessor element will
    // get a top margin, this creates the even gutter between elements or "stack"
    // See https://every-layout.dev/layouts/stack/#the-solution
    & > * + * {
        margin-top: $dimension-spacing-large;
    }

    .form-buttons {
        text-align: end;
    }

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
}
</style>
