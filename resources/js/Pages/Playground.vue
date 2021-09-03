<template>
  <div>
    <Head title="Mismatch Finder - Component Playground" />
    <h1>Mismatch Finder - Playground!</h1>
    <p>Feel free to throw any component you want to try, in here</p>
    <auth-widget v-bind:user="user" />
    <wikit-button type="progressive" variant="primary" @click.native="console">Test Wikit Component</wikit-button>
    <p/>
    <form id="idsForm" action="/results">
        <text-area v-model="itemIds" :rows="10" label="Enter Item IDs here:" @input="printVal"/>
        <input name="ids" type="hidden"/>
        <wikit-button type="progressive" variant="primary" @click.native="submitForm">Send Item IDs</wikit-button>
    </form>
  </div>
</template>

<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue'
    import AuthWidget from '../Components/AuthWidget.vue';
    import { Button as WikitButton, TextArea } from '@wmde/wikit-vue-components';

    export default Vue.extend({
        components: {
            Head,
            AuthWidget,
            TextArea,
            WikitButton
        },
        props: {
            user: Object,
        },
        methods: {
            console() {
                console.log('testing wikit component');
            },
            submitForm() {
                var form = document.getElementById('idsForm');
                form.ids.value = this.itemIds;
                form.submit();
            },
            printVal(v) {
                console.log(v);
            }
        },
        data: () => ({
            itemIds: ''
        })
    });
</script>

<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Lato&family=Source+Serif+Pro&display=swap');

h1 {
    font-family: 'Source Serif Pro', serif;
    font-size: 32px;
    line-height: 40px;
    margin: 0;
    padding: 0 0 28px 0;
    font-weight: 400;
}
</style>
