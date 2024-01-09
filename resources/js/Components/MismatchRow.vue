<template>
    <tr>
        <td :data-header="$i18n('column-mismatch')">
            <a class="break-line-link"
              target="_blank"
              :href="`https://www.wikidata.org/wiki/Property:${mismatch.property_id}`">
                {{mismatch.property_label}}
            </a>
        </td>
        <td :data-header="$i18n('column-type')">
            <span v-if="mismatch.type !== ''">
              {{mismatch.type}}
            </span>
            <span v-else>
              {{ $i18n('statement') }}
            </span>
        </td>
        <td :data-header="$i18n('column-wikidata-value')">
            <span class="empty-value" v-if="mismatch.wikidata_value === ''">
              {{ $i18n('empty-value') }}
            </span> 
            <a
              v-else class="break-line-link" :href="statementUrl" target="_blank">
                {{mismatch.value_label || mismatch.wikidata_value}}
          </a>
        </td>
        <td :data-header="$i18n('column-external-value')">
           <a class="break-line-link" v-if="mismatch.external_url"
              :href="`${mismatch.external_url}`" target="_blank"
            >
              {{mismatch.external_value}}
            </a>
            <span v-else>
              {{mismatch.external_value}}
            </span>
        </td>
        <td :data-header="$i18n('column-external-source')">
            <a class="break-line-link" v-if="mismatch.import_meta.external_source_url"
              :href="`${mismatch.import_meta.external_source_url}`" target="_blank"
            >
              {{mismatch.import_meta.external_source}}
            </a>
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
              <a
                class="uploader"
                :href="`https://www.wikidata.org/wiki/User:${mismatch.import_meta.user.username}`"
                target="_blank"
              >
                    {{mismatch.import_meta.user.username}}
              </a>
                <span class="upload-date">{{uploadDate}}</span>
                <div class="description">
                  {{uploadInfoDescription}}
                  <cdx-button
                      v-if="shouldTruncate"
                      class="full-description-button"
                      weight="quiet"
                      action="progressive"
                      @click="showDialog"
                  >
                      {{$i18n('results-full-description-button')}}
                  </cdx-button>
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
            <a
              class="uploader"
              :href="`https://www.wikidata.org/wiki/User:${mismatch.import_meta.user.username}`" 
              target="_blank"
            >
                {{mismatch.import_meta.user.username}}
            </a>
            <span class="upload-date">{{uploadDate}}</span>
            <div class="description">
              {{mismatch.import_meta.description}}
            </div>
          </cdx-dialog>
        </td>
    </tr>
</template>

<script setup lang="ts">
import { formatISO } from 'date-fns';

import type { PropType } from 'vue';
import { computed, ref } from 'vue';
import { CdxButton, CdxDialog, CdxSelect } from "@wikimedia/codex";
import { MenuItem } from '@wmde/wikit-vue-components/dist/components/MenuItem';
import { LabelledMismatch, ReviewDecision } from "../types/Mismatch";
import { useI18n } from 'vue-banana-i18n';

const truncateLength = 100;

interface ReviewMenuItem extends MenuItem {
  value: ReviewDecision;
}

type ReviewOptionMap = {
  [key: string]: ReviewMenuItem;
};

const props = withDefaults(defineProps<{
  mismatch: LabelledMismatch
  disabled: boolean
}>(), {
  disabled: false
});

const messages = useI18n();

const statusOptions: ReviewOptionMap = Object.values(ReviewDecision).reduce(
  (options: ReviewOptionMap, decision: ReviewDecision) => ({
    ...options,
    [decision]: {
      value: decision,
      label: messages.i18n(`review-status-${decision}`),
      description: "",
    },
  }),
  {}
);

const uploadDate = computed<string>(() => {
	return formatISO(new Date(props.mismatch.import_meta.created_at), {
    representation: 'date'
  });
});

const statementUrl = computed<string>(() => {
	return `https://www.wikidata.org/wiki/${props.mismatch.item_id}#${props.mismatch.statement_guid}`;
});

const shouldTruncate = computed<boolean>(() => {
	const text = props.mismatch.import_meta.description;
  return text ? text.length > truncateLength : false;
});

const uploadInfoDescription = computed<string>(() => {
	const text = props.mismatch.import_meta.description;
  return shouldTruncate.value ? text.substring(0, truncateLength) + '...' : text;
});

const decision = statusOptions[props.mismatch.review_status];
const reviewStatus = String(props.mismatch.review_status);
const fullDescriptionDialog = ref(false);

function showDialog(e: Event) {
  e.preventDefault();
  fullDescriptionDialog.value = true;
}
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

  a {
    break-line-link {
      width: 100%
    }
  }

    .wikit-Button.full-description-button {
      padding: 0px 2px;
      font-weight: 400;
    }
    .empty-value {
      color: $font-color-disabled;
    }
</style>
