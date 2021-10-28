<template>
    <div class="page-container results-page">
        <Head title="Mismatch Finder - Results" />
        <section id="description-section">
            <h2 class="h4">{{ $i18n('results-page-title') }}</h2>
            <p id="about-description" >{{ $i18n('results-page-description') }}</p>
        </section>
        <section id="error-section" v-if="unexpectedError">
            <Message type="error">{{ $i18n('server-error') }}</Message>
        </section>
        <section id="message-section">
            <Message type="notice" v-if="notFoundItemIds.length">
                <span>{{ $i18n('no-mismatches-found-message') }}</span>
                <span class="message-link" v-for="item_id in notFoundItemIds" :key="item_id">
                    <wikit-link
                        :href="`http://www.wikidata.org/wiki/${item_id}`">{{labels[item_id]}} ({{item_id}})
                    </wikit-link>
                </span>
            </Message>
            <Message type="warning" v-if="!user">
                <span v-i18n-html:log-in-message="['/auth/login']"></span>
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
                        :disabled="!user"
                        @decision="recordDecision"
                    />
                    <div class="form-buttons">
                        <wikit-button
                            :disabled="!user"
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
    import axios from 'axios';

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
            async send( item: string ): Promise<void> {
                if(this.decisions[item]){
                    // use axios in order to preserve saved mismatches
                    try {
                        await axios.put('/mismatch-review', this.decisions[item]);

                        // remove decision from this.decisions after it has been
                        // sent to the server successfully, to avoid sending them twice
                        delete this.decisions[item];
                    } catch(e) {
                        console.error("saving review decisions has failed", e);
                    }
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

#about-description {
    max-width: 705px;
    margin-top: 8px;
}

</style>
