<template>
    <wikit-table>
        <thead>
            <tr>
                <th>{{$i18n('column-property')}}</th>
                <th>{{$i18n('column-wikidata-value')}}</th>
                <th>{{$i18n('column-external-value')}}</th>
                <th>{{$i18n('column-upload-info')}}</th>
            </tr>
        </thead>
        <tbody>
            <mismatch-row v-for="mismatch in mismatches" :key="mismatch.id" :mismatch="mismatch" />
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
        mismatches: Array as PropType<LabelledMismatch[]>
    }
});
</script>
