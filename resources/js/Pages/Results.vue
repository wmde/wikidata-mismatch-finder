<template>
    <div class="page-container results-page">
        <loading-overlay ref="overlay" />
        <inertia-head title="Mismatch Finder - Results" />
        <wikit-button class="back-button" @click.native="() => $inertia.get('/', {})">
            <template #prefix>
                <icon type="arrowprevious" size="medium" color="inherit" :dir="pageDirection"/>
            </template>
            {{ $i18n('results-back-button') }}
        </wikit-button>
        <section id="description-section">
            <header class="description-header">
                <h2 class="h4">{{ $i18n('results-page-title') }}</h2>
                <wikit-button
                    id="instructions-button"
                    variant="quiet"
                    type="progressive"
                    @click.native="showInstructionsDialog"
                >
                    <template #prefix>
                        <icon type="info-outlined" size="medium" color="inherit"/>
                    </template>
                    {{$i18n('results-instructions-button')}}
                </wikit-button>
            </header>

            <wikit-dialog id="instructions-dialog"
                :title="$i18n('instructions-dialog-title')"
                ref="inctructionsDialog"
                :actions="[{
                    label: $i18n('confirm-dialog-button'),
                    namespace: 'instructions-confirm'
                }]"
                @action="(_, dialog) => dialog.hide()"
                dismiss-button
            >
                <p>{{ $i18n('instructions-dialog-message-upload-info-description') }}</p>
                <p>{{ $i18n('instructions-dialog-message-intro') }}</p>
                <ul>
                    <li>{{ $i18n('instructions-dialog-message-instruction-wikidata') }}</li>
                    <li>{{ $i18n('instructions-dialog-message-instruction-missing') }}</li>
                    <li>{{ $i18n('instructions-dialog-message-instruction-external') }}</li>
                    <li>{{ $i18n('instructions-dialog-message-instruction-both') }}</li>
                    <li>{{ $i18n('instructions-dialog-message-instruction-none') }}</li>
                    <li>{{ $i18n('instructions-dialog-message-instruction-pending') }}</li>
                </ul>
            </wikit-dialog>
            <p id="about-description" >
                {{ $i18n('results-page-description') }}
            </p>
        </section>
        <section id="error-section" v-if="requestError">
            <Message type="error" class="generic-error">{{ $i18n('server-error') }}</Message>
        </section>
        <section id="message-section">
            <Message type="notice" v-if="notFoundItemIds.length">
                <span>{{ $i18n('no-mismatches-found-message') }}</span>
                <span class="message-link" v-for="item_id in notFoundItemIds" :key="item_id">
                    <wikit-link
                        :href="`https://www.wikidata.org/wiki/${item_id}`" target="_blank">
                        {{labels[item_id]}} ({{item_id}})
                    </wikit-link>
                </span>
            </Message>
            <!-- The Results page without item_ids is used by RandomizeController. -->
            <Message type="notice" v-if="item_ids.length === 0">
                <span>{{ $i18n('no-mismatches-available-for-review') }}</span>
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
                    <wikit-link :href="`https://www.wikidata.org/wiki/${item}`" target="_blank">
                        {{labels[item]}} ({{item}})
                    </wikit-link>
                </h2>
                <form @submit.prevent="send(item)">
                    <mismatches-table :mismatches="addLabels(mismatches)"
                        :disabled="!user"
                        @decision="recordDecision"
                    />
                    <footer class="mismatches-form-footer">
                        <Message class="form-success-message" type="success" v-if="lastSubmitted === item">
                            <span>{{ $i18n('changes-submitted-message') }}</span>
                            <span class="message-link">
                                <wikit-link :href="`https://www.wikidata.org/wiki/${item}`" target="_blank">
                                    {{labels[item]}} ({{item}})
                                </wikit-link>
                            </span>
                        </Message>
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
                    </footer>
                </form>
            </section>
        </section>
        <wikit-dialog class="confirmation-dialog"
            :title="$i18n('confirmation-dialog-title')"
            ref="confirmation"
            :actions="[{
                label: $i18n('confirmation-dialog-button'),
                namespace: 'next-steps-confirm'
            }]"
            @action="_handleConfirmation"
            @dismissed="disableConfirmation = false"
            dismiss-button
        >
            <p>{{ $i18n('confirmation-dialog-message-intro') }}</p>
            <ul>
                <li>{{ $i18n('confirmation-dialog-message-tip-1') }}</li>
                <li>{{ $i18n('confirmation-dialog-message-tip-2') }}</li>
                <li>{{ $i18n('confirmation-dialog-message-tip-3') }}</li>
            </ul>
            <checkbox class="disable-confirmation"
                :label="$i18n('confirmation-dialog-option-label')"
                :checked.sync="disableConfirmation"
            />
        </wikit-dialog>
    </div>
</template>

<script lang="ts">
    import { PropType } from 'vue';
    import { mapMutations } from 'vuex';
    import isEmpty from 'lodash/isEmpty';
    import { Head as InertiaHead } from '@inertiajs/inertia-vue';
    import {
        Link as WikitLink,
        Button as WikitButton,
        Checkbox,
        Dialog as WikitDialog,
        Icon,
        Message } from '@wmde/wikit-vue-components';

    import LoadingOverlay from '../Components/LoadingOverlay.vue';
    import MismatchesTable from '../Components/MismatchesTable.vue';
    import Mismatch, {ReviewDecision, LabelledMismatch} from '../types/Mismatch';
    import User from '../types/User';
    import defineComponent from '../types/defineComponent';
    import axios from 'axios';

    interface MismatchDecision {
        id: number,
        item_id: string,
        review_status: ReviewDecision,
        previous_status: ReviewDecision
    }

    interface Result {
        [qid: string]: Mismatch[]
    }

    interface LabelMap {
        [entityId: string]: string
    }

    interface FormattedValueMap {
        [propertyId: string]: { [value: string]: string };
    }

    interface DecisionMap {
        [entityId: string]: {
            [id: number]: MismatchDecision
        }
    }

    interface ResultsState {
        decisions: DecisionMap,
        disableConfirmation: boolean,
        pageDirection: string,
        requestError: boolean,
        lastSubmitted: string
    }

    export default defineComponent({
        components: {
            InertiaHead,
            Icon,
            LoadingOverlay,
            MismatchesTable,
            WikitLink,
            WikitButton,
            WikitDialog,
            Checkbox,
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
            },
            formatted_values: {
                type: Object as PropType<FormattedValueMap>,
                default: () => ({}),
            },
        },
        computed: {
            notFoundItemIds() {
                return this.item_ids.filter( id => !this.results[id] )
            },
        },
        mounted(){
            if(!this.$store.state.lastSearchedIds) {
                this.saveSearchedIds( this.item_ids.join('\n') );
            }

            this.pageDirection = window.getComputedStyle(document.body).direction;
            const storageData = this.user
                ? window.localStorage.getItem(`mismatch-finder_user-settings_${this.user.id}`)
                : null;

            if (!storageData) {
                return;
            }

            try {
                const userSettings = JSON.parse(storageData);

                this.disableConfirmation = userSettings.disableConfirmation;
            } catch (e) {
                console.error("failed to parse saved user settings", e);
            }
        },
        data(): ResultsState {
            return {
                decisions: {},
                disableConfirmation: false,
                pageDirection: 'ltr',
                requestError: false,
                lastSubmitted: ''
            }
        },
        methods: {
            showInstructionsDialog() {
                /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
                const dialog = this.$refs.inctructionsDialog as any;
                dialog.show();
            },
            addLabels(mismatches: Mismatch[]): LabelledMismatch[]{
                // The following callback maps existing mismatches to extended
                // mismatch objects which include labels, by looking up any
                // potential entity ids within the labels object.
                return mismatches.map(mismatch => {
                    const labelled = {
                        property_label: this.labels[mismatch.property_id],
                        value_label: this.labels[mismatch.wikidata_value] || null,
                        ...mismatch
                    };
                    if (mismatch.property_id in this.formatted_values) {
                        const formattedValues = this.formatted_values[mismatch.property_id];
                        const key = mismatch.meta_wikidata_value + '|' + mismatch.wikidata_value;
                        if (key in formattedValues) {
                            labelled.value_label = formattedValues[key];
                        }
                    }
                    return labelled;
                });
            },
            recordDecision( decision: MismatchDecision): void {
                const itemDecisions = this.decisions[decision.item_id];
                decision.previous_status = itemDecisions && itemDecisions[decision.id]
                    ? itemDecisions[decision.id].previous_status // keep previous status if we have one
                    : ReviewDecision.Pending;                    // assign 'pending' otherwise

                this.decisions[decision.item_id] = {
                    ...itemDecisions,
                    [decision.id]: decision
                };
            },
            async send( item: string ): Promise<void> {
                this.clearSubmitConfirmation();

                if( !this.decisions[item] || isEmpty(this.decisions[item]) || !this.hasChanged(item) ){
                    return;
                }

                // Casting to `any` since TS cannot understand $refs as
                // component instances and complains about the usage of `show`
                // See: https://github.com/vuejs/vue-class-component/issues/94
                // Defaulting to any, as the alternative presents us with
                // convoluted and unnecessary syntax.
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                const confirmationDialog = this.$refs.confirmation as any;
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                const overlay = this.$refs.overlay as any;

                overlay.show();

                // use axios in order to preserve saved mismatches
                try {
                    await axios.put('/mismatch-review', this.decisions[item]);

                    this.requestError = false;
                    await overlay.hide();
                    this.storePreviousDecisions(item);
                    this.showSubmitConfirmation(item);

                    if(!this.disableConfirmation){
                        confirmationDialog.show();
                    }
                } catch(e) {
                    this.requestError = true;
                    console.error("saving review decisions has failed", e);
                    await overlay.hide();
                }
            },
            clearSubmitConfirmation() {
                this.lastSubmitted = '';
            },
            showSubmitConfirmation( item: string ) {
                this.lastSubmitted = item;
            },
            hasChanged(entityId: string) {
                for (const decisionId in this.decisions[entityId]) {
                    const decision = this.decisions[entityId][decisionId];
                    if(decision.review_status !== decision.previous_status) {
                        return true;
                    }
                }

                return false;
            },
            storePreviousDecisions(item: string) {
                for (const decisionId in this.decisions[item]) {
                    const decision = this.decisions[item][decisionId];
                    decision.previous_status = decision.review_status;
                }
            },
            // Anotating dialog as `any` since typescript doesn't fully
            // understand component instances and complains about usage of the
            // hide method otherwise.
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            _handleConfirmation(_ : string, dialog: any){
                const { disableConfirmation, user } = this;

                // Do nothing if there is no user
                if ( !user ){
                    return;
                }

                if(disableConfirmation){
                    const storageData = JSON.stringify({ disableConfirmation });
                    window.localStorage.setItem(`mismatch-finder_user-settings_${user.id}`, storageData);
                }

                dialog.hide();
            },
            ...mapMutations(['saveSearchedIds'])
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.back-button {
    // to match the first heading on the home page
    margin-top: $dimension-layout-xsmall;
}

h2 {
    .wikit-Link.wikit {
        font-weight: bold;
        display: inline;
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

.mismatches-form-footer {
    margin-top: $dimension-layout-xsmall;
    display: flex;
    flex-direction: row-reverse;
    justify-content: space-between;
    align-items: flex-start;
    gap: $dimension-layout-xsmall;
    // calculate the footer height to reserve space for
    // messages with two lines (1.5 line height plus padding)
    min-height: calc(2*1.5em + 2*$dimension-spacing-large);

    .form-success-message {
        max-width: 705px;
        flex-shrink: 0;
        flex-grow: 1;
        order: 1;
    }
}
</style>
