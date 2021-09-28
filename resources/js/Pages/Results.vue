<template>
    <div class="page-container results-page">
        <Head title="Mismatch Finder - Results" />
        <div v-if="Object.keys(results).length < item_ids.length" class="not-found">
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
                    <span>{{ $i18n('no-mismatches-found-message') }} 
                        <span class="message-link" v-for="(label, item_id) in labelsFromNotFound" :key="item_id">
                            <wikit-link 
                                :href="`http://www.wikidata.org/wiki/${item_id}`">{{label}} ({{item_id}})
                            </wikit-link>
                        </span>
                    </span>
                </Message>
            </section>
        </div>
        <section class="results" v-if="Object.keys(results).length">
            <section v-for="(mismatches, item, idx) in results" :key="idx">
                <h2 class="h4">
                    <wikit-link :href="`http://www.wikidata.org/wiki/${item}`">{{labels[item]}} ({{item}})</wikit-link>
                </h2>
                <mismatches-table :mismatches="addLabels(mismatches)" />
            </section>
        </section>
    </div>
</template>

<script lang="ts">
    import { PropType } from 'vue';
    import { Head } from '@inertiajs/inertia-vue';
    import { 
        Link as WikitLink,
        Button as WikitButton,
        Message } from '@wmde/wikit-vue-components';
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
            WikitLink,
            WikitButton,
            Message
        },
        props: {
            item_ids: Array as PropType<string[]>,
            results: Object as PropType<Result>,
            labels: Object as PropType<LabelMap>
        },
        computed: {
            labelsFromNotFound() {
                const notFoundItems = this.item_ids.filter( element => !Object.keys( this.results ).includes(element) );
                
                const notFoundLabels = Object.entries(this.labels)
                    .filter(([key]) => notFoundItems.includes(key))
                    .reduce((obj, [key, val]) => Object.assign(obj, { [key]: val }), {});
                
                return notFoundLabels;
                }
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

.message-link {
    .wikit-Link.wikit {
        display: inline;
    }

    &::after {
        content: ", ";
    }

    &:last-child::after {
        content: "";
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
    max-width: 705px;

    .wikit-Message {
        border-radius: $border-radius-base;
    }
}

</style>
