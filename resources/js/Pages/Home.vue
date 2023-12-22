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
                <item-id-search-textarea 
                    :loading="loading"
                    ref="textarea"
                />
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
    import { CdxDialog, CdxButton, CdxIcon, CdxMessage } from "@wikimedia/codex";
    import ItemIdSearchTextarea from '../Components/ItemIdSearchTextarea.vue';
    import { cdxIconDie, cdxIconInfo } from '@wikimedia/codex-icons';
    import { defineComponent, ref } from 'vue';
    import ValidationError from '../types/ValidationError';

    interface HomeState {
        validationError: null|ValidationError,
        faqDialog: boolean
    }

    interface ErrorMessages {
        [ key : string ] : string
    }

    interface FlashMessages {
        errors : { [ key : string ] : string }
    }

    export default defineComponent({
        components: {
          CdxDialog,
          CdxButton,
          CdxIcon,
          CdxMessage,
          ItemIdSearchTextarea,
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
            send(): void {
                (this.$refs.textarea as InstanceType<typeof ItemIdSearchTextarea>).validate();

                if((this.$refs.textarea as InstanceType<typeof ItemIdSearchTextarea>).validationError) {
                    return;
                }
                const store = useStore();
                store.saveSearchedIds( this.textareaInputValue );
                this.$inertia.get( '/results', 
                    { ids: (this.$refs.textarea as InstanceType<typeof ItemIdSearchTextarea>).serializeInput() }
                );
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
            return {
                validationError: null,
                faqDialog: false
            }
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';
@import '../../sass/_typography.scss';

#about-description {
    @include body-M
}

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
    background-color: $background-color-interactive-subtle;

    /**
    * Border
    */
    border-style: $border-style-base;
    border-width: $border-width-base;
    border-color: $border-color-subtle;
    border-radius: $border-radius-base;

    /**
    * Layout
    */
    padding: var(--dimension-spacing-large);
    margin: var(--dimension-layout-xsmall) 0;

    // Any direct decendent of this form that has a predecessor element will
    // get a top margin, this creates the even gutter between elements or "stack"
    // See https://every-layout.dev/layouts/stack/#the-solution
    & > * + * {
        margin-top: var(--dimension-spacing-large);
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
