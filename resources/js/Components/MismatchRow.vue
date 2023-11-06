<template>
    <tr>
        <td :data-header="$i18n('column-mismatch')">
            <wikit-link
              class="break-line-link"
              target="_blank"
              :href="`https://www.wikidata.org/wiki/Property:${mismatch.property_id}`">
                {{mismatch.property_label}}
            </wikit-link>
        </td>
        <td :data-header="$i18n('column-type')">
            <span v-if="mismatch.type !== ''">
              {{mismatch.type}}
            </span>
            <span v-else>
              {{ this.$i18n('statement') }}
            </span>
        </td>
        <td :data-header="$i18n('column-wikidata-value')">
            <span class="empty-value" v-if="mismatch.wikidata_value === ''">
              {{ this.$i18n('empty-value') }}
            </span> 
            <wikit-link 
              v-else class="break-line-link" :href="statementUrl" target="_blank">
                {{mismatch.value_label || mismatch.wikidata_value}}
            </wikit-link>
        </td>
        <td :data-header="$i18n('column-external-value')">
          <wikit-link v-if="mismatch.external_url" class="break-line-link"
              :href="`${mismatch.external_url}`" target="_blank"
            >
              {{mismatch.external_value}}
            </wikit-link>
            <span v-else>
              {{mismatch.external_value}}
            </span>
        </td>
        <td :data-header="$i18n('column-external-source')">
            <wikit-link v-if="mismatch.import_meta.external_source_url" class="break-line-link"
              :href="`${mismatch.import_meta.external_source_url}`" target="_blank"
            >
              {{mismatch.import_meta.external_source}}
            </wikit-link>
            <span v-else>
              {{mismatch.import_meta.external_source}}
            </span>
        </td>
        <td :data-header="$i18n('column-review-status')">
            <cdx-select
                :menu-items="Object.values(statusOptions)"
                :disabled="disabled"
                v-model:selected="reviewStatus"
                @update:selected="$bubble('decision', {
                    id: mismatch.id,
                    item_id: mismatch.item_id,
                    review_status: $event
                })"
            />
        </td>
        <td :data-header="$i18n('column-upload-info')">
            <div class="upload-details">
                <wikit-link class="uploader"
                    :href="`https://www.wikidata.org/wiki/User:${mismatch.import_meta.user.username}`"
                    target="_blank"
                >
                    {{mismatch.import_meta.user.username}}
                </wikit-link>
                <span class="upload-date">{{uploadDate}}</span>
                <div class="description">
                  {{uploadInfoDescription}}
                  <wikit-button
                      v-if="shouldTruncate"
                      class="full-description-button"
                      variant="quiet"
                      type="progressive"
                      @click.native="showDialog"
                  >
                      {{$i18n('results-full-description-button')}}
                  </wikit-button>
                </div>
            </div>
            <cdx-dialog class="full-description-dialog"
              :title="$i18n('column-upload-info')"
              :open="fullDescriptionDialog"
              :primary-action="{
                  label: $i18n('confirm-dialog-button'),
                  namespace: 'next-steps-confirm',
                  actionType: 'progressive'
              }"
              @primary="() => fullDescriptionDialog = false"
              close-button-label="X"
            >
            <wikit-link class="uploader"
                    :href="`https://www.wikidata.org/wiki/User:${mismatch.import_meta.user.username}`"
                    target="_blank"
            >
                {{mismatch.import_meta.user.username}}
            </wikit-link>
            <span class="upload-date">{{uploadDate}}</span>
            <div class="description">
              {{this.mismatch.import_meta.description}}
            </div>
          </cdx-dialog>
        </td>
    </tr>
</template>

<script lang="ts">
import { formatISO } from 'date-fns';

import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import {
    Button as WikitButton,
    Link as WikitLink
} from '@wmde/wikit-vue-components';
import { CdxDialog, CdxSelect } from "@wikimedia/codex";
import { MenuItem } from '@wmde/wikit-vue-components/dist/components/MenuItem';

import { LabelledMismatch, ReviewDecision } from "../types/Mismatch";

const truncateLength = 100;

interface ReviewMenuItem extends MenuItem {
  value: ReviewDecision;
}

type ReviewOptionMap = {
  [key: string]: ReviewMenuItem;
};

interface MismatchRowState {
  statusOptions: ReviewOptionMap;
  decision: ReviewMenuItem;
  reviewStatus: string;
  fullDescriptionDialog: boolean;
}

export default defineComponent({
    components: {
    WikitButton,
    WikitLink,
    CdxDialog,
    CdxSelect
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
      shouldTruncate(): boolean {
        const text = this.mismatch.import_meta.description;
        return text ? text.length > truncateLength : false;
      },
      uploadInfoDescription(): string {
        const text = this.mismatch.import_meta.description;
        return this.shouldTruncate ?
          text.substring(0, truncateLength) + '...' : text;
      }
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
      reviewStatus: String(this.mismatch.review_status),
      fullDescriptionDialog: false
    };
  },
  methods: {
    showDialog(e: Event) {
      e.preventDefault();
      this.fullDescriptionDialog = true;
    }
  },
  watch: {
      reviewStatus(newValue, oldValue) {
        console.log(`Review status ${newValue} ${oldValue}`);
      }
  }
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

    .wikit-Link.break-line-link {
      width: 100%;
    }
    .wikit-Link__content {
      word-break: break-word;
    }
    .wikit-Button.full-description-button {
      padding: 0px 2px;
      font-weight: 400;
    }
    .empty-value {
      color: $font-color-disabled;
    }
</style>
