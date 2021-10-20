<template>
  <div lang="en">
    <Head title="Mismatch Finder - Component Playground" />
    <h2>Mismatch Finder - Playground!</h2>
    <p>Feel free to throw any component you want to try, in here</p>

    <dialog>I am a dialog!</dialog>
  </div>
</template>

<script lang="ts">
    import { Head } from '@inertiajs/inertia-vue'
    import defineComponent from '../types/defineComponent';
    import Dialog from '../Components/Dialog.vue';
    import { Link, Table as WikitTable } from '@wmde/wikit-vue-components';

    export default defineComponent({
        components: {
            Head,
            Dialog
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
        },
        data(){
            return {
                desserts: [
                    {
                        name: 'Frozen Yogurt',
                        url: 'https://commons.wikimedia.org/wiki/File:Blueberry_frozen_yoghurt_(4937999312).jpg',
                        calories: 159,
                    },
                    {
                        name: 'Ice cream sandwich',
                        calories: 237,
                    },
                    {
                        name: 'Eclair',
                        calories: 262,
                    },
                    {
                        name: 'Cupcake',
                        calories: 305,
                    },
                    {
                        name: 'Gingerbread',
                        calories: 356,
                    },
                    {
                        name: 'Jelly bean',
                        calories: 375,
                    },
                    {
                        name: 'Lollipop',
                        calories: 392,
                    },
                    {
                        name: 'Honeycomb',
                        calories: 408,
                    },
                    {
                        name: 'Donut',
                        calories: 452,
                    },
                    {
                        name: 'KitKat',
                        calories: 518,
                    },
                ],
            }
        }
    });
</script>

<style lang="scss">

</style>
