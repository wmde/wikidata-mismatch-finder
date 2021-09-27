<template>
    <div class="page-container results-page">
        <Head title="Mismatch Finder - Results" />
        <section id="results" v-if="Object.keys(results).length">
            <section class="item-mismatches" v-for="(mismatches, item, idx) in results" :key="idx">
                <h2 class="h4">{{item}}</h2>
                <mismatches-table :mismatches="mismatches" />
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
    import { PropType } from 'vue';

    import { Head } from '@inertiajs/inertia-vue';

    import MismatchesTable from '../Components/MismatchesTable.vue';
    import Mismatch from '../types/Mismatch';
    import defineComponent from '../types/defineComponent';

    interface Result {
        [qid: string]: Mismatch[]
    }

    export default defineComponent({
        components: {
            Head,
            MismatchesTable
        },
        props: {
            item_ids: Array as PropType<string[]>,
            results: Object as PropType<Result>
        }
    });
</script>

<style lang="scss">
</style>
