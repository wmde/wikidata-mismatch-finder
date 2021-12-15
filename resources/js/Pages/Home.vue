<template>
    <div class="page-container home-page">
        <Head title="Mismatch Finder" />
        <section id="description-section">
            <header class="description-header">
                <h2 class="h4">{{ $i18n('about-mismatch-finder-title') }}</h2>
                <wikit-button
                    id="faq-button"
                    variant="quiet"
                    type="progressive"
                    @click.native="$refs.faq.show()"
                >
                    <template #prefix>
                        <icon type="info-outlined" size="medium" color="inherit"/>
                    </template>
                    {{ $i18n('faq-button') }}
                </wikit-button>
            </header>

            <wikit-dialog id="faq-dialog"
                :title="$i18n('faq-dialog-title')"
                ref="faq"
                :actions="[{
                    label: $i18n('confirm-dialog-button'),
                    namespace: 'faq-confirm'
                }]"
                @action="(_, dialog) => dialog.hide()"
                dismiss-button
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
            </wikit-dialog>
            <p id="about-description" >
                {{ $i18n('about-mismatch-finder-description') }}
            </p>
        </section>

        <section id="message-section">
            <Message v-if="unexpectedError" type="error">
                <span>{{ $i18n('server-error') }}</span>
            </Message>
        </section>

        <section id="querying-section">
            <h2 class="h5">{{ $i18n('item-form-title') }}</h2>
            <form id="items-form" @submit.prevent="send">
                <text-area
                    :label="$i18n('item-form-id-input-label')"
                    :placeholder="$i18n('item-form-id-input-placeholder')"
                    :rows="8"
                    :loading="loading"
                    :error="validationError"
                    v-model="form.itemsInput"
                />
                <div class="form-buttons">
                    <wikit-button
                        class="submit-ids"
                        variant="primary"
                        type="progressive"
                        native-type="submit"
                        :disabled="loading"
                    >
                        {{ $i18n('item-form-submit') }}
                    </wikit-button>
                </div>
            </form>
        </section>
    </div>
</template>

<script lang="ts">
    import { mapState } from 'vuex';
    import { Head } from '@inertiajs/inertia-vue';
    import {
        Button as WikitButton,
        Dialog as WikitDialog,
        Icon,
        Message,
        TextArea
    } from '@wmde/wikit-vue-components';

    import defineComponent from '../types/defineComponent';

    interface HomeState {
        form: {
            itemsInput: string
        },
        validationError: null|{
            type: string,
            message: string
        }
    }

    interface FlashMessages {
        errors : { [ key : string ] : string }
    }

    export default defineComponent({
        components: {
            Head,
            Icon,
            Message,
            TextArea,
            WikitButton,
            WikitDialog
        },
        methods: {
            splitInput: function(): Array<string> {
                return this.form.itemsInput.split( '\n' );
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

                const rules = [{
                    check: (ids: Array<string>) => ids.length < 1,
                    type: 'warning',
                    message: this.$i18n('item-form-error-message-empty')
                },
                {
                    check: (ids: Array<string>) => !ids.every(value => /^[Qq]\d+$/.test( value.trim() )),
                    type: 'error',
                    message: this.$i18n('item-form-error-message-invalid')
                }];

                const sanitized = this.sanitizeArray();

                for(let {check, type, message} of rules){
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

                this.$store.commit('saveSearchedIds', this.form.itemsInput);
                this.$inertia.get( '/results', { ids: this.serializeInput() } );
            },
        },
        computed: {
            unexpectedError() {
                const flashMessages = this.$page.props.flash as FlashMessages;
                return (flashMessages.errors && flashMessages.errors.unexpected);
            },
            // spread to combine with local computed props
            ...mapState(['loading','lastSearchedIds']),
        },
        data(): HomeState {
            return {
                form: {
                    itemsInput: this.$store.state.lastSearchedIds
                },
                validationError: null
            }
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

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
}
</style>
