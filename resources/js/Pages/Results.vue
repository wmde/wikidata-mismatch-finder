<template>
    <div>
        <Head title="Mismatch Finder - Results" />
        <h2>Results</h2>
        <table>
            <tbody>
                <tr v-for="result in results" :key="result.id">
                    <td>{{result.id}}</td>
                    <td>{{result.item_id}}</td>
                    <td>{{result.statement_guid}}</td>
                    <td>{{result.property_id}}</td>
                    <td>{{result.wikidata_value}}</td>
                    <td>{{result.external_value}}</td>
                    <td>{{result.external_url}}</td>
                    <td>{{result.review_status}}</td>
                    <td>{{result.reviewer}}</td>
                    <td>{{result.import}}</td>
                    <td>{{result.updated_at}}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script lang="ts">
    import { Head } from '@inertiajs/inertia-vue'

    import defineComponent from '../types/defineComponent';

    export default defineComponent({
        components: {
            Head
        },
        props: {
            results: Array
        }
    });
</script>

<style lang="scss">
</style>
