<template>
    <Layout :user="user">
        <div class="page-container">
            <Head title="Mismatch Finder" />
            <section id="intro-section">
                <h2 class="h4">{{ $i18n('about-mismatch-finder-title') }}</h2>
                <p id="about-description" >{{ $i18n('about-mismatch-finder-description') }}</p>
            </section>
            <section id="querying-section">
                <h2 class="h5">{{ $i18n('item-form-title') }}</h2>
                <form id="items-form" @submit.prevent="send">
                    <text-area
                        :label="$i18n('item-form-id-input-label')"
                        :placeholder="$i18n('item-form-id-input-placeholder')"
                        :rows="8"
                        :error="error"
                        v-model="form.itemsInput"
                    />
                    <div class="form-buttons">
                        <wikit-button native-type="submit">
                            {{ $i18n('item-form-submit') }}
                        </wikit-button>
                    </div>
                </form>
            </section>
        </div>
    </Layout>
</template>

<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue';
    import {
        Button as WikitButton,
        TextArea
    } from '@wmde/wikit-vue-components';

    import Layout from './Layout';

    export default Vue.extend({
        components: {
            Head,
            TextArea,
            WikitButton,
            Layout
        },
        props: {
            user: Object,
        },
        methods: {
            splitInput: function() {
                return this.form.itemsInput.split( '\n' );
            },
            serializeInput: function() {
                return this.splitInput().join('|');
            },
            checkEmpty() {
                if( !this.form.itemsInput ) {
                    this.error = {
                        type: 'warning',
                        message: this.$i18n('item-form-error-message-empty')
                    };
                }
            },
            validate() {
                this.error = null;
                this.checkEmpty();

                let valid = this.splitInput().every( function( currentValue ) {
                    let trimmedLine = currentValue.trim();
                    return trimmedLine == '' || trimmedLine.match( /^Q[0-9]*$/ );
                });

                if( !valid ) {
                    this.error = {
                        type: 'error',
                        message: this.$i18n('item-form-error-message-invalid')
                    };
                }
            },
            send() {
                this.validate();

                if(this.error) {
                    return;
                }

                this.$inertia.get( '/results?ids=' + this.serializeInput() );
            },
        },
        data(){
            return {
                form: {
                    itemsInput: ''
                },
                error: null
            }
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

#about-description {
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
