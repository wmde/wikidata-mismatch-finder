<template>
    <div class="page-container results-page">
        <div class="progressbar" v-if="submitting" role="progressbar" />
        <div class="overlay" v-if="submitting" />
        <Head title="Mismatch Finder - Results" />
        <wikit-button class="back-button" @click.native="() => $inertia.get('/', {} ,{ replace: true })">
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
        <section id="error-section" v-if="unexpectedError || requestError">
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
    import { Head } from '@inertiajs/inertia-vue';
    import {
        Link as WikitLink,
        Button as WikitButton,
        Checkbox,
        Dialog as WikitDialog,
        Icon,
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
        decisions: DecisionMap,
        disableConfirmation: boolean,
        pageDirection: string,
        requestError: boolean,
        lastSubmitted: string,
        submitting: boolean
    }

    const SUBMITTING_DELAY_TIME = 1000;

    export default defineComponent({
        components: {
            Head,
            Icon,
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
        mounted(){
            if(!this.$store.state.lastSearchedIds) {
                this.$store.commit('saveSearchedIds', this.item_ids.join('\n'));
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
                lastSubmitted: '',
                submitting: false
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
                if(!this.decisions[item]){
                    return;
                }

                this.clearSubmitConfirmation();

                // Casting to `any` since TS cannot understand $refs as
                // component instances and complains about the usage of `show`
                // See: https://github.com/vuejs/vue-class-component/issues/94
                // Defaulting to any, as the alternative presents us with
                // convoluted and unnecessary syntax.
                // eslint-disable-next-line @typescript-eslint/no-explicit-any
                const confirmationDialog = this.$refs.confirmation as any;

                this.submitting = true;

                this.showSubmitConfirmation(item);

                // remove decision from this.decisions after it has been
                // sent to the server successfully, to avoid sending them twice
                delete this.decisions[item];
                // we can't access the body tag from inside vue, because the inertia instance
                // is declared inside it, so we call it from the DOM directly
                document.body.classList.add('noscroll');

                // use axios in order to preserve saved mismatches
                try {
                    await axios.put('/mismatch-review', this.decisions[item]);
                    // remove decision from this.decisions after it has been
                    // sent to the server successfully, to avoid sending them twice
                    delete this.decisions[item];

                    if(!this.disableConfirmation){
                        setTimeout(() => { 
                            confirmationDialog.show();
                        // the transition between the loading state 
                        // and the dialog looks better with a small delay between them    
                        }, SUBMITTING_DELAY_TIME + 100)}

                } catch(e) {
                    this.requestError = true;
                    console.error("saving review decisions has failed", e);
                }

                // adding this delay because when the response from the request happens 
                // too fast the overlay and progressbar flash
                setTimeout(() => { 
                    this.submitting = false;
                    document.body.classList.remove('noscroll');
                }, SUBMITTING_DELAY_TIME);
            },
            clearSubmitConfirmation() {
                this.lastSubmitted = '';
            },
            showSubmitConfirmation( item: string ) {
                this.lastSubmitted = item;
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
            }
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

.noscroll {
    overflow: hidden;
}

.overlay {
    /**
    * Layout
    */
    width: $wikit-Dialog-overlay-width;
    height: $wikit-Dialog-overlay-height;
    position: fixed;
    top:0;
    left:0;

    z-index: 100;

    /**
    * Colors
    */
    background-color: $wikit-Dialog-overlay-background-color;
    opacity: $wikit-Dialog-overlay-opacity;
}

.progressbar {
    // Currently the inline progress bar only supports indeterminate loading mode.
    // For a proof of concept on how this can include also determinate loading, see:
    // https://codepen.io/xumium/pen/LYLZbva?editors=1100
    // We ensure semantic usage by only targeting generic elements that set the
    // correct role 
    &[role=progressbar] {
        position: fixed;
        top: 0;
        left: 0;
        width: $wikit-Progress-inline-track-width;
        height: $wikit-Progress-inline-track-height;

        &::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            display: block;
            height: 100%;
            background: $wikit-Progress-inline-background-color;
        }

        // Indeterminate progress bars should not set the `aria-valuenow` 
        // attribute
        &:not([aria-valuenow])::before {
            width: 30%;
            border-radius: $wikit-Progress-inline-indeterminate-border-radius;
            animation-name: load-indeterminate;
            animation-duration: $wikit-Progress-inline-animation-duration;
            animation-timing-function: ease;
            animation-iteration-count: infinite;
            animation-delay: 0s;
        }
    }

    @keyframes load-indeterminate {
        0% { left: 0; }
        50% { left: 70%; }
        100% { left: 0; }
    }
    z-index: 101;
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
