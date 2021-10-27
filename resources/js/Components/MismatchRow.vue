<template>
    <tr>
        <td :data-header="$i18n('column-property')">
            <wikit-link
              class="break-line-link"
              :href="`https://www.wikidata.org/wiki/Property:${mismatch.property_id}`">
                {{mismatch.property_label}}
            </wikit-link>
        </td>
        <td :data-header="$i18n('column-wikidata-value')">
            <wikit-link class="break-line-link" :href="statementUrl">
                {{mismatch.value_label || mismatch.wikidata_value}}
            </wikit-link>
        </td>
        <td :data-header="$i18n('column-external-value')">
            {{mismatch.external_value}}
        </td>
        <td :data-header="$i18n('column-review-status')">
            <dropdown
                :menuItems="Object.values(statusOptions)"
                :placeholder="$i18n('review-status-pending')"
                :disabled="disabled"
                v-model="decision"
                @input="$bubble('decision', {
                    id: mismatch.id,
                    item_id: mismatch.item_id,
                    review_status: $event.value
                })"
            />
        </td>
        <td :data-header="$i18n('column-external-source')">
            <wikit-link v-if="mismatch.import_meta.external_source_url" class="break-line-link"
              :href="`${mismatch.import_meta.external_source_url}`"
            >
              {{mismatch.import_meta.external_source}}
            </wikit-link>
            <span v-else>
              {{mismatch.import_meta.external_source}}
            </span>
        </td>
        <td :data-header="$i18n('column-upload-info')">
            <div class="upload-details">
                <wikit-link class="uploader"
                    :href="`https://www.wikidata.org/wiki/User:${mismatch.import_meta.user.username}`"
                >
                    {{mismatch.import_meta.user.username}}
                </wikit-link>
                <span class="upload-date">{{uploadDate}}</span>
            </div>
        </td>
    </tr>
</template>

<script lang="ts">
import { formatISO } from 'date-fns';

import Vue, { PropType } from 'vue';
import { Dropdown, Link as WikitLink } from '@wmde/wikit-vue-components';
import { MenuItem } from '@wmde/wikit-vue-components/dist/components/MenuItem';

import { LabelledMismatch, ReviewDecision } from "../types/Mismatch";

interface ReviewMenuItem extends MenuItem {
  value: ReviewDecision;
}

type ReviewOptionMap = {
  [key: string]: ReviewMenuItem;
};

interface MismatchRowState {
  statusOptions: ReviewOptionMap;
  decision: ReviewMenuItem;
}

export default Vue.extend({
    components: {
    WikitLink,
    Dropdown,
    },
    props: {
        mismatch: Object as PropType<LabelledMismatch>,
        disabled: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        uploadDate(): string {
            return formatISO(new Date(this.mismatch.import_meta.created_at), {
                representation: 'date'
            });
        },
        statementUrl(): string {
      return `https://www.wikidata.org/wiki/${this.mismatch.item_id}#${this.mismatch.statement_guid}`;
    },
  },
  data(): MismatchRowState {
    // The following reducer generates the list of dropdown options based on a list of allowed status values
    const statusOptions: ReviewOptionMap = Object.values(ReviewDecision).reduce(
      (options: ReviewOptionMap, decision: ReviewDecision) => ({
        ...options,
        [decision]: {
          value: decision,
          label: this.$i18n(`review-status-${decision}`),
          description: "",
        },
      }),
      {}
    );

    return {
      statusOptions,
      decision: statusOptions[this.mismatch.review_status],
    };
  },
});
</script>

<style lang="scss">
    .wikit-Link.break-line-link {
      width: 100%;
    }
    .wikit-Link__content {
      word-break: break-word;
    }
</style>