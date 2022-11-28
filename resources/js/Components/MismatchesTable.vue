<template>
    <wikit-table>
        <thead>
            <tr>
                <th class="column-property">{{$i18n('column-property')}}</th>
                <th class="column-wikidata-value">{{$i18n('column-wikidata-value')}}</th>
                <th class="column-external-value">{{$i18n('column-external-value')}}</th>
                <th class="column-external-source">{{$i18n('column-external-source')}}</th>
                <th class="column-review-status">{{$i18n('column-review-status')}}</th>
                <th class="column-upload-info">{{$i18n('column-upload-info')}}</th>
            </tr>
        </thead>
        <tbody>
            <mismatch-row v-for="mismatch in mismatches"
                :disabled="disabled"
                :key="mismatch.id"
                :mismatch="mismatch"
                :id="`mismatch-${mismatch.id}`"
            />
        </tbody>
    </wikit-table>
</template>

<script lang="ts">
import Vue, { PropType } from 'vue';
import { Table as WikitTable } from '@wmde/wikit-vue-components';

import MismatchRow from './MismatchRow.vue';

import { LabelledMismatch } from '../types/Mismatch';

export default Vue.extend({
    components: {
        MismatchRow,
        WikitTable,
    },
    props: {
        mismatches: Array as PropType<LabelledMismatch[]>,
        disabled: {
            type: Boolean,
            default: false
        }
    }
});
</script>

<style lang="scss">
    .column-property { width: 12.5%; }
    .column-wikidata-value { width: 12.5%; }
    .column-external-value { width: 13.5%; }
    .column-external-source { width: 11.5%; }
    .column-review-status { width: 25%; }
    .column-upload-info { width: 25%; }
</style>
