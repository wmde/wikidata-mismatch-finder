<template>
  <div lang="en">
    <Head title="Mismatch Finder - Component Playground" />
    <h2>Mismatch Finder - Playground!</h2>
    <p>Feel free to throw any component you want to try, in here</p>

    <wikit-table id="some-table" linearize="desktop">
        <thead>
            <tr>
                <th scope="col">Name</th>
                <th scope="col">Calories</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="dessert in desserts" :key="dessert.name">
                <!-- When using the table make sure to include the
                    data-header for linearization and accesibility -->
                <td data-header="Name">
                    <Link v-if="dessert.url" :href="dessert.url">{{dessert.name}}</Link>
                    <div v-else>{{dessert.name}}</div>
                </td>
                <td data-header="Calories">{{dessert.calories}}</td>
            </tr>
        </tbody>
    </wikit-table>
  </div>
</template>

<script lang="ts">
    import { Head } from '@inertiajs/inertia-vue'
    import defineComponent from '../types/defineComponent';
    import { Link, Table as WikitTable } from '@wmde/wikit-vue-components';

    export default defineComponent({
        components: {
            Head,
            Link,
            WikitTable
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
