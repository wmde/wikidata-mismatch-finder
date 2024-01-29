<template>
    <wikit-table>
        <thead>
            <tr>
                <th class="column-mismatch">{{$i18n('column-mismatch')}}</th>
                <th class="column-type">{{$i18n('column-type')}}</th>
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

  <table class="mismatches-table">
    <thead>
    <tr>
      <th class="column-mismatch">{{$i18n('column-mismatch')}}</th>
      <th class="column-type">{{$i18n('column-type')}}</th>
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
  </table>
</template>

<script setup lang="ts">
import { Table as WikitTable } from '@wmde/wikit-vue-components';

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
    @import '~@wmde/wikit-tokens/dist/_variables.scss';
    .column-mismatch { width: 12%; }
    .column-type { width: 11.5%; }
    .column-wikidata-value { width: 12.5%; }
    .column-external-value { width: 12.5%; }
    .column-external-source { width: 11.5%; }
    .column-review-status { width: 20%; }
    .column-upload-info { width: 20%; }


    .mismatches-table {
      /**
      * Layout
      */
      // As the specificationn state that the table columns should gain their
      // width from cell content (instead of just header content / width) we
      // revert to using the default table layout algorithm. This is done in
      // order to avoid changing the table's display proterty and thus
      // oblitirating it's inehrit accesibility:
      // See: https://www.tpgi.com/short-note-on-what-css-display-properties-do-to-table-semantics/
      table-layout: auto;
      width: 100%;

      /**
      * Borders
      */
      border-collapse: collapse;

      /**
      * Colors
      */
      background-color: $wikit-Table-background-color;
      color: $wikit-Table-cell-color;

      /**
      * Typography
      */
      font-family: $wikit-Table-cell-font-family;
      font-size: $wikit-Table-cell-font-size;
      font-weight: $wikit-Table-cell-font-weight;

      tbody tr:hover {
        background-color: $background-color-base-hover;
        transition-duration: $transition-duration-medium;
        transition-timing-function: $transition-timing-function-ease;
        transition-property: $transition-property-background-color;
      }

      tr {
        /**
        * Layout
        */

        /**
        * Borders
        */
        border-bottom-style: $wikit-Table-border-style;
        border-bottom-width: $wikit-Table-border-width;
        border-radius: $wikit-Table-border-radius;
        border-bottom-color: $wikit-Table-border-color;
      }

      th,
      td {
        /**
        * Layout
        */
        padding-inline: $wikit-Table-cell-spacing-horizontal;

        /**
        * Typography
        */
        line-height: $wikit-Table-cell-line-height;
        text-align: start;
        overflow-wrap: break-word;
        hyphens: auto;
      }

      td {
        /**
        * Layout
        */
        height: $wikit-Table-cell-height;
        padding-block: $wikit-Table-cell-spacing-vertical;

        /**
        * Typography
        */
        vertical-align: middle;
      }

      th {
        /**
        * Layout
        */
        padding-block: $wikit-Table-cell-header-spacing-vertical;
        /**
        * Typography
        */
        font-weight: $wikit-Table-cell-header-font-weight;
        vertical-align: top;
      }
    }
</style>
