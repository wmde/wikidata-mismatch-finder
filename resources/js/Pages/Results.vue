<template>
    <div class="page-container results-page">
        <Head title="Mismatch Finder - Results" />
        <section id="results" v-if="Object.keys(results).length">
            <section class="item-mismatches" v-for="(mismatches, item, idx) in results" :key="idx">
                <h2 class="h4">
                    <wikit-link :href="`http://www.wikidata.org/wiki/${item}`">{{labels[item]}} ({{item}})</wikit-link>
                </h2>
                <mismatches-table :mismatches="addLabels(mismatches)" />
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
    import { Link as WikitLink } from '@wmde/wikit-vue-components';

    import MismatchesTable from '../Components/MismatchesTable.vue';
    import Mismatch, {LabelledMismatch} from '../types/Mismatch';
    import defineComponent from '../types/defineComponent';

    interface Result {
        [qid: string]: Mismatch[]
    }

    interface LabelMap {
        [entityId: string]: string
    }

    export default defineComponent({
        components: {
            Head,
            MismatchesTable,
            WikitLink
        },
        props: {
            item_ids: Array as PropType<string[]>,
            results: Object as PropType<Result>,
            labels: Object as PropType<LabelMap>
        },
        methods: {
            addLabels(mismatches: Mismatch[]): LabelledMismatch[]{
                // The following callback maps existing mismatches to extended
                // mismatch objects which include labels, by looking up any
                // potential entity ids within the labels object.
                return mismatches.map(mismatch => ({
                    property_label: this.labels[mismatch.property_id],
                    value_label: this.labels[mismatch.wikidata_value] || null,
                    ...mismatch
                }));
            }
        }
    });
</script>

<style lang="scss">
    h2 {
        .wikit-Link.wikit {
            font-weight: bold;
        }
    }
</style>
