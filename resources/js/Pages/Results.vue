<template>
    <div class="page-container results-page">
        <Head title="Mismatch Finder - Results" />
        <section id="error-section" v-if="unexpectedError">
            <Message type="error">{{ $i18n('server-error') }}</Message>
        </section>
        <section id="message-section" v-if="notFoundItemIds.length">
            <Message type="notice">
                <span>{{ $i18n('no-mismatches-found-message') }}</span>
                <span class="message-link" v-for="item_id in notFoundItemIds" :key="item_id">
                    <wikit-link
                        :href="`http://www.wikidata.org/wiki/${item_id}`">{{labels[item_id]}} ({{item_id}})
                    </wikit-link>
                </span>
            </Message>
        </section>
        <section id="results" v-if="Object.keys(results).length">
            <section class="item-mismatches"
                v-for="(mismatches, item, idx) in results"
                :id="`item-mismatches-${item}`"
                :key="idx">
                <h2 class="h4">
                    <wikit-link :href="`http://www.wikidata.org/wiki/${item}`">{{labels[item]}} ({{item}})</wikit-link>
                </h2>
                <form @submit.prevent="send(item)">
                    <mismatches-table :mismatches="addLabels(mismatches)"
                        @decision="recordDecision"
                    />
                    <div class="form-buttons">
                        <wikit-button
                            variant="primary"
                            type="progressive"
                            native-type="submit"
                        >
                            {{ $i18n('result-form-submit') }}
                        </wikit-button>
                    </div>
                </form>
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
    import Mismatch, {ReviewDecision, LabelledMismatch} from '../types/Mismatch';
    import User from '../types/User';
    import defineComponent from '../types/defineComponent';
    import { RequestPayload } from '@inertiajs/inertia';

    interface MismatchDecision {
        id: number,
        item_id: string,
        review_status: ReviewDecision
    }

    interface Result {
        [qid: string]: Mismatch[]
    }

    interface LabelMap {
        [entityId: string]: string
    }

    interface FlashMessages {
        errors : { [ key : string ] : string }
    }

    interface DecisionMap {
        [entityId: string]: {
            [id: number]: MismatchDecision
        }
    }

    interface ResultsState {
        decisions: DecisionMap
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
            user: {
                type: Object as PropType<User|null>,
                default: null
            },
            item_ids: {
                type: Array as PropType<string[]>,
                default: () => []
            },
            results: {
                type: Object as PropType<Result>,
                default: () => ({})
            },
            labels: {
                type: Object as PropType<LabelMap>,
                default: () => ({})
            }
        },
        computed: {
            notFoundItemIds() {
                return this.item_ids.filter( id => !this.results[id] )
            },
            unexpectedError() {
                const flashMessages = this.$page.props.flash as FlashMessages;
                return (flashMessages.errors && flashMessages.errors.unexpected);
            }
        },
        data(): ResultsState {
            return {
                decisions: {}
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
            recordDecision( decision: MismatchDecision ): void {
                const itemDecisions = this.decisions[decision.item_id]
                this.decisions[decision.item_id] = {
                    ...itemDecisions,
                    [decision.id]: decision
                };
            },
            send( item: string ): void {

                if(this.decisions && Object.prototype.hasOwnProperty.call(this.decisions, item)){

                    this.$inertia.put( '/mismatch-review', this.decisions[item] as unknown as RequestPayload );
                    // remove decision from this.decisions after it has been sent to the server to avoid sending
                    // them twice
                    delete this.decisions[item];
                }
            },
        }
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
        display: inline-block;
    }

    &::after {
        content: ", ";
    }

    &:last-child::after {
        content: "";
    }
}

.form-buttons {
    text-align: end;
    margin-top: $dimension-layout-xsmall;
}
</style>
