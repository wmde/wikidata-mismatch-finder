<template>
    <div class="page-container results-page">
        <Head title="Mismatch Finder - Results" />
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
        <div v-else class="not-found">
            
            <div id="top-button">
                <wikit-button
                    variant="normal"
                    type="neutral"
                    native-type="button"
                    @click.native="returnHome"
                >
                    &#x2190; {{ $i18n('refine-items-selection') }}
                </wikit-button>
            </div>
            <section id="description-section">
                <h2 class="h4">{{ $i18n('results-page-title') }}</h2>
                <p id="about-description" >{{ $i18n('results-page-description') }}</p>
            </section>
            <section id="message-section">
                <Message type="notice">
                    <span>{{ $i18n('no-mismatches-found-message') }} {{item_ids}}</span>
                </Message>
            </section>
        </div>
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
     import {
        Button as WikitButton,
        Message
    } from '@wmde/wikit-vue-components';

    export default defineComponent({
        components: {
            Head,
            MismatchesTable,
            WikitLink,
            WikitButton,
            Message
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
            },
            returnHome(): void {
                this.$inertia.get( '/' );
            },
        },
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

h2 {
    .wikit-Link.wikit {
        font-weight: bold;
    }
}

#about-description {
    max-width: 705px;
    margin-top: 8px;
}

#top-button {
    margin-bottom: 15px;
}

#message-section {
    max-width: 675px;

    .wikit-Message {
        border-radius: $border-radius-base;
    }
}

</style>
