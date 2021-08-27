<template>
  <div class="page-container">
    <Head title="Mismatch Finder" />
    <section id="intro-section">
        <h2 class="h4">{{ $i18n('about-mismatch-finder-title') }}</h2>
        <p id="about-description" >{{ $i18n('about-mismatch-finder-description') }}</p>
    </section>
    <section id="querying-section">
        <h2 class="h5">{{ $i18n('item-form-title') }}</h2>
        <form id="item-form" @submit.prevent="send">
            <text-area
                :label="$i18n('item-form-id-input-label')"
                :placeholder="$i18n('item-form-id-input-placeholder')"
                :rows="8"
                v-model="form.itemsInput"
            />
            <wikit-button native-type="submit" disabled>
                {{ $i18n('item-form-submit') }}
            </wikit-button>
        </form>
    </section>
  </div>
</template>

<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue';
    import {
        Button as WikitButton,
        TextArea
    } from '@wmde/wikit-vue-components';

    export default Vue.extend({
        components: {
            Head,
            TextArea,
            WikitButton
        },
        props: {
            user: Object,
        },
        methods: {
            send(){
                // TODO: Implement validation and form sending logic (See: https://inertiajs.com/forms)
            }
        },
        data(){
            return {
                form: {
                    itemsInput: ''
                }
            }
        }
    });
</script>

<style lang="scss">
#about-description {
    max-width: 705px;
}
</style>
