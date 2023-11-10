<template>
  <div lang="en">
    <inertia-head title="Mismatch Finder - Component Playground" />
    <h2>Mismatch Finder - Playground!</h2>
    <p>Feel free to throw any component you want to try, in here</p>
  </div>
</template>

<script lang="ts">
    import { defineComponent } from 'vue';
    import { Head as InertiaHead } from '@inertiajs/inertia-vue3'

    export default defineComponent({
        components: { InertiaHead },
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
        },
        data(){
            return {
                controlled: true,
                standalone: false
            }
        }
    });
</script>

<style lang="scss">
    button.wikit {
        margin-bottom: 1rem;
    }

    .fancy-dialog {
        h2 {
            margin-bottom: 0.5em;
        }

        p {
            margin-bottom: 1.25em;
        }

        .wikit-Dialog__close {
            margin-bottom: 0;
        }
    }
</style>
