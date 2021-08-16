<template>
  <div>
    <Head title="Mismatch Finder" />
    <h1>Mismatch Finder</h1>
    <p>This page is coming soon!</p>
    <auth-widget v-bind:user="user" />
    <Button type="progressive" variant="primary" @click.native="console">Test Wikit Component</Button>
  </div>
</template>


<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue'
    import AuthWidget from '../Components/AuthWidget.vue';
    import { Button } from '@wmde/wikit-vue-components';

    export default Vue.extend({
        components: {
            Head,
            AuthWidget,
            Button
        },
        props: {
            user: Object,
        },
        methods: {
            console() {
                console.log('testing wikit component');
            }
        }
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
