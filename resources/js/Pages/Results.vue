<template>
    <div>
        <Head title="Mismatch Finder - Results" />
        <h2>Results</h2>
        <section class="results" v-if="Object.keys(results).length">
            <section v-for="(mismatches, item, idx) in results" :key="idx">
                <h3>{{item}}</h3>
                <table>
                    <tbody>
                        <tr v-for="mismatch in mismatches" :key="mismatch.id">
                            <td>{{mismatch.property_id}}</td>
                            <td>{{mismatch.wikidata_value}}</td>
                            <td>{{mismatch.external_value}}</td>
                            <td>
                                <span>{{mismatch.import_meta.user.username}}</span>
                                <span>{{mismatch.import_meta.created_at}}</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>
        </section>
        <p v-else class="not-found">
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
            results: Object
        }
    });
</script>

<style lang="scss">
</style>
