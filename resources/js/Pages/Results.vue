<template>
    <div>
        <Head title="Mismatch Finder - Results" />
        <h2>Results</h2>
        <table v-if="results.length">
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
        <p v-else class="results">
            Thank you for sending IDs {{item_ids}}. 
            The requested item ids didn't match any entries in our database. 
            Please try with a different set of ids.
        </p>
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
            item_ids: Array,
            results: Array
        }
    });
</script>

<style lang="scss">
</style>
