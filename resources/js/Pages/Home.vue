<template>
    <div class="page-container home-page">
        <Head title="Mismatch Finder" />
        <section id="intro-section">
            <h2 class="h4">{{ $i18n('about-mismatch-finder-title') }}</h2>
            <p id="about-description" >{{ $i18n('about-mismatch-finder-description') }}</p>
        </section>

        <section id="error-section" v-if="unexpectedError">
            <message-box type="error">{{ $i18n('server-error') }}</message-box>
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
        TextArea
    } from '@wmde/wikit-vue-components';
    import MessageBox from '../Components/MessageBox.vue';
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
            MessageBox,
            TextArea,
            WikitButton
        },
        methods: {
            splitInput: function(): Array<string> {
                return this.form.itemsInput.split( '\n' );
            },
            serializeInput: function(): string {
                return this.splitInput().join('|');
            },
            checkEmpty(): void {
                if( !this.form.itemsInput ) {
                    this.validationError = {
                        type: 'warning',
                        message: this.$i18n('item-form-error-message-empty')
                    };
                }
            },
            validate(): void {
                this.validationError = null;
                this.checkEmpty();

                let valid = this.splitInput().every( function( currentValue: string ) {
                    let trimmedLine = currentValue.trim();
                    return trimmedLine == '' || trimmedLine.match( /^[Qq]\d+$/ );
                });

                if( !valid ) {
                    this.validationError = {
                        type: 'error',
                        message: this.$i18n('item-form-error-message-invalid')
                    };
                }
            },
            send(): void {
                this.validate();

                if(this.validationError) {
                    return;
                }

                this.$inertia.get( '/results?ids=' + this.serializeInput() );
            },
        },
        computed: {
            unexpectedError() {
                const flashMessages = this.$page.props.flash as FlashMessages;
                return (flashMessages.errors && flashMessages.errors.unexpected);
            },
            // spread to combine with local computed props
            ...mapState({
                loading: 'loading'
            }),
        },
        data(): HomeState {
            return {
                form: {
                    itemsInput: ''
                },
                validationError: null
            }
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

#intro-section{
    max-width: 705px;
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
}
</style>
