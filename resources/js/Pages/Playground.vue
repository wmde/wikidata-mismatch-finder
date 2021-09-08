<template>
  <div>
    <Head title="Mismatch Finder - Component Playground" />
    <h1>Mismatch Finder - Playground!</h1>
    <p>Feel free to throw any component you want to try, in here</p>
    <auth-widget v-bind:user="user" />
    <text-area :rows="10" label="I am from Wikit" resize="horizontal" value="With a default value" @input="printVal" />
    <wikit-button type="progressive" variant="primary" @click.native="console">Test Wikit Component</wikit-button>
  </div>
</template>

<script lang="ts">
    import { Head } from '@inertiajs/inertia-vue'
    import AuthWidget from '../Components/AuthWidget.vue';
    import { Button as WikitButton, TextArea } from '@wmde/wikit-vue-components';

    import defineComponent from '../types/defineComponent';

    export default defineComponent({
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
            printVal(v: string) {
                console.log(v);
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
