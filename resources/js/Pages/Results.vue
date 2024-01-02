<template>
    <div class="page-container results-page">
        <loading-overlay ref="overlayRef" />
        <inertia-head title="Mismatch Finder - Results" />
        <cdx-button class="back-button" @click="() => $inertia.get('/', {})">
            <cdx-icon :icon="cdxIconArrowPrevious" />
            {{ $i18n('results-back-button') }}
        </cdx-button>
        <section id="description-section">
            <header class="description-header">
                <h2 class="h4">{{ $i18n('results-page-title') }}</h2>
                <cdx-button
                    id="instructions-button"
                    weight="quiet"
                    action="progressive"
                    @click="instructionsDialog = true"
                >
                    <cdx-icon :icon="cdxIconInfo" />
                    {{$i18n('results-instructions-button')}}
                </cdx-button>
            </header>

            <cdx-dialog id="instructions-dialog"
                :title="$i18n('instructions-dialog-title')"
                v-model:open="instructionsDialog"
                :primary-action="{
                    label: $i18n('confirm-dialog-button'),
                    namespace: 'instructions-confirm',
                    actionType: 'progressive'
                }"
                @primary="() => instructionsDialog = false"
                close-button-label="X"
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
            </cdx-dialog>
            <p id="about-description" >
                {{ $i18n('results-page-description') }}
            </p>
        </section>
        <section id="error-section" v-if="requestError">
            <cdx-message type="error" class="generic-error">{{ $i18n('server-error') }}</cdx-message>
        </section>
        <section id="message-section">
            <cdx-message type="notice" v-if="notFoundItemIds.length">
                <span>{{ $i18n('no-mismatches-found-message') }}</span>
                <span class="message-link" v-for="item_id in notFoundItemIds" :key="item_id">
                    <a :href="`https://www.wikidata.org/wiki/${String(item_id)}`" target="_blank">
                        {{labels[item_id]}} ({{item_id}})
                    </a>
                </span>
            </cdx-message>
            <!-- The Results page without item_ids is used by RandomizeController. -->
            <cdx-message type="notice" v-if="item_ids.length === 0">
                <span>{{ $i18n('no-mismatches-available-for-review') }}</span>
            </cdx-message>
            <cdx-message type="warning" v-if="!user">
                <span v-i18n-html:log-in-message="['/auth/login']"></span>
            </cdx-message>
        </section>
        <section id="results" v-if="Object.keys(results).length">
            <section class="item-mismatches"
                v-for="(mismatches, item, idx) in results"
                :id="`item-mismatches-${String(item)}`"
                :key="idx">
                <h2 class="h4">
                    <a :href="`https://www.wikidata.org/wiki/${String(item)}`" target="_blank">
                        {{labels[item]}} ({{item}})
                    </a>
                </h2>
                <form @submit.prevent="send(String(item))">
                    <mismatches-table :mismatches="addLabels(mismatches)"
                        :disabled="!user"
                        @decision="recordDecision"
                    />
                    <footer class="mismatches-form-footer">
                        <cdx-message class="form-success-message" type="success" v-if="lastSubmitted === item">
                            <span>{{ $i18n('changes-submitted-message') }}</span>
                            <span class="message-link">
                                <a :href="`https://www.wikidata.org/wiki/${String(item)}`" target="_blank">
                                    {{labels[item]}} ({{item}})
                                </a>
                            </span>
                        </cdx-message>
                        <div class="form-buttons">
                            <cdx-button
                                :disabled="!user"
                                weight="primary"
                                action="progressive"
                            >
                                {{ $i18n('result-form-submit') }}
                            </cdx-button>
                        </div>
                    </footer>
                </form>
            </section>
        </section>
        <cdx-dialog id="results-confirmation-dialog"
            :title="$i18n('confirmation-dialog-title')"
            v-model:open="confirmationDialog"
            @update:open="disableConfirmation = false"
            close-button-label="X"
        >
            <p>{{ $i18n('confirmation-dialog-message-intro') }}</p>
            <ul>
                <li>{{ $i18n('confirmation-dialog-message-tip-1') }}</li>
                <li>{{ $i18n('confirmation-dialog-message-tip-2') }}</li>
                <li>{{ $i18n('confirmation-dialog-message-tip-3') }}</li>
            </ul>

			<template #footer>
				<cdx-checkbox class="disable-confirmation" v-model="disableConfirmation" inline
				>
					{{ $i18n('confirmation-dialog-option-label') }}
				</cdx-checkbox>

				<cdx-button
					weight="primary"
					action="progressive"
					:aria-label="$i18n('confirmation-dialog-button')"
					@click="_handleConfirmation"
				>
					{{ $i18n('confirmation-dialog-button') }}
				</cdx-button>
			</template>

		</cdx-dialog>
    </div>
</template>

<script setup lang="ts">
import { useStore } from '../store';
import isEmpty from 'lodash/isEmpty';
import { Head as InertiaHead } from '@inertiajs/inertia-vue3';
import { CdxButton, CdxIcon, CdxDialog, CdxMessage, CdxCheckbox } from "@wikimedia/codex";
import { cdxIconInfo, cdxIconArrowPrevious } from '@wikimedia/codex-icons';
import LoadingOverlay from '../Components/LoadingOverlay.vue';
import MismatchesTable from '../Components/MismatchesTable.vue';
import Mismatch, {ReviewDecision, LabelledMismatch} from '../types/Mismatch';
import User from '../types/User';
import { Ref, computed, onMounted, ref } from 'vue';
import axios from 'axios';

// Run it with compat mode
// https://v3-migration.vuejs.org/breaking-changes/v-model.html
CdxCheckbox.compatConfig = {
    ...CdxCheckbox.compatConfig,
    COMPONENT_V_MODEL: false,
};
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

const decisions: Ref<DecisionMap> = ref({});
const disableConfirmation = ref(false);
const pageDirection = ref('ltr');
const requestError = ref(false);
const lastSubmitted = ref('');
const instructionsDialog = ref(false);
const confirmationDialog = ref(false);

const overlayRef = ref(null);

const props = withDefaults(defineProps<{
    user: User
    item_ids: Array<string>
    results: Result
    labels: LabelMap
    formatted_values: FormattedValueMap 
}>(), {
    user: null,
    item_ids: () => [],
    results: () => ({}),
    labels: () => ({}),
    formatted_values: () => ({})
});

const notFoundItemIds = computed<string[]>(() => {
    return props.item_ids.filter( id => !props.results[id as keyof typeof props.results] )
});

onMounted(() => {
    const store = useStore();
    if(!store.lastSearchedIds) {
        store.saveSearchedIds( props.item_ids.join('\n') );
    }

    pageDirection.value = window.getComputedStyle(document.body).direction;
    const storageData = props.user
        ? window.localStorage.getItem(`mismatch-finder_user-settings_${props.user.id}`)
        : null;

    if (!storageData) {
        return;
    }

    try {
        const userSettings = JSON.parse(storageData);
        disableConfirmation.value = userSettings.disableConfirmation;
    } catch (e) {
        console.error("failed to parse saved user settings", e);
    }
});

function addLabels(mismatches: Mismatch[]): LabelledMismatch[]{
    // The following callback maps existing mismatches to extended
    // mismatch objects which include labels, by looking up any
    // potential entity ids within the labels object.
    return mismatches.map(mismatch => {
        const labelled = {
            property_label: props.labels[mismatch.property_id as keyof typeof props.labels],
            value_label: props.labels[mismatch.wikidata_value as keyof typeof props.labels] || null,
            ...mismatch
        };
        if (mismatch.property_id in props.formatted_values) {
            // eslint-disable-next-line max-len
            const formattedValues = props.formatted_values[mismatch.property_id as keyof typeof props.formatted_values];
            const key = mismatch.meta_wikidata_value + '|' + mismatch.wikidata_value;
            if (key in formattedValues) {
                labelled.value_label = formattedValues[key];
            }
        }
        return labelled;
    });
}

function recordDecision( decision: MismatchDecision): void {
    const itemDecisions = decisions.value[decision.item_id];
    decision.previous_status = itemDecisions && itemDecisions[decision.id]
        ? itemDecisions[decision.id].previous_status // keep previous status if we have one
        : ReviewDecision.Pending;                    // assign 'pending' otherwise

    decisions.value[decision.item_id] = {
        ...itemDecisions,
        [decision.id]: decision
    };
}
    
async function send( item: string ): Promise<void> {
    clearSubmitConfirmation();

    if( !decisions.value[item] || isEmpty(decisions.value[item]) || !hasChanged(item) ){
        return;
    }

    const overlay = overlayRef.value;
    overlay.show();

    // use axios in order to preserve saved mismatches
    try {
        await axios.put('/mismatch-review', decisions.value[item]);

        requestError.value = false;
        await overlay.hide();
        storePreviousDecisions(item);
        showSubmitConfirmation(item);

        if(!disableConfirmation.value){
            confirmationDialog.value = true;
        }
    } catch(e) {
        requestError.value = true;
        console.error("saving review decisions has failed", e);
        await overlay.hide();
    }
}

function clearSubmitConfirmation() {
    lastSubmitted.value = '';
}

function showSubmitConfirmation( item: string ) {
    lastSubmitted.value = item;
}

function hasChanged(entityId: string) {
    for (const decisionId in decisions.value[entityId]) {
        const decision = decisions.value[entityId][decisionId];
        if(decision.review_status !== decision.previous_status) {
            return true;
        }
    }

    return false;
}

function storePreviousDecisions(item: string) {
    for (const decisionId in decisions.value[item]) {
        const decision = decisions.value[item][decisionId];
        decision.previous_status = decision.review_status;
    }
}

function _handleConfirmation(){

    // Do nothing if there is no user
    if ( !props.user ){
        return;
    }

    const disableConfirmationValue = disableConfirmation.value;
    if(disableConfirmationValue){
        const storageData = JSON.stringify({ disableConfirmation: disableConfirmationValue });
        window.localStorage.setItem(`mismatch-finder_user-settings_${props.user.id}`, storageData);
    }

    confirmationDialog.value = false;
}
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.back-button {
    // to match the first heading on the home page
    margin-top: $dimension-layout-xsmall;
    // to avoid visual grouping with .description-section
    margin-bottom: $dimension-layout-xsmall;
}

h2 {
    a {
        font-weight: bold;
        display: inline;
    }
}

.message-link {
    a {
        display: inline-block;
    }
    &::after {
        content: ", ";
    }

    &:last-child::after {
        content: "";
    }
}
#results-confirmation-dialog {
	footer {
		display: flex;
		align-items: baseline;
		justify-content: space-between;
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
        flex-grow: 1;
        order: 1;
    }
}
</style>
