<template>
  <Table>
    <thead>
      <tr>
        <th class="column-mismatch">
          {{ $i18n('column-mismatch') }}
        </th>
        <th class="column-type">
          {{ $i18n('column-type') }}
        </th>
        <th class="column-wikidata-value">
          {{ $i18n('column-wikidata-value') }}
        </th>
        <th class="column-external-value">
          {{ $i18n('column-external-value') }}
        </th>
        <th class="column-external-source">
          {{ $i18n('column-external-source') }}
        </th>
        <th class="column-review-status">
          {{ $i18n('column-review-status') }}
        </th>
        <th class="column-upload-info">
          {{ $i18n('column-upload-info') }}
        </th>
      </tr>
    </thead>
    <tbody>
      <mismatch-row
        v-for="mismatch in mismatches"
        :disabled="disabled"
        :key="mismatch.id"
        :mismatch="mismatch"
        :id="`mismatch-${mismatch.id}`"
      />
    </tbody>
  </Table>
</template>

<script setup lang="ts">
import Table from './Table.vue';
import MismatchRow from './MismatchRow.vue';

import type { LabelledMismatch } from '../types/Mismatch';

withDefaults(defineProps<{
	mismatches: LabelledMismatch[],
    disabled: boolean
}>(), {
	disabled: false
});
</script>

<style lang="scss">
    .column-mismatch { width: 12%; }
    .column-type { width: 11.5%; }
    .column-wikidata-value { width: 12.5%; }
    .column-external-value { width: 12.5%; }
    .column-external-source { width: 11.5%; }
    .column-review-status { width: 20%; }
    .column-upload-info { width: 20%; }
</style>
