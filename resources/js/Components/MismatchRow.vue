<template>
    <tr>
        <td :data-header="$i18n('column-property')">
            <wikit-link
              class="break-line-link"
              target="_blank"
              :href="`https://www.wikidata.org/wiki/Property:${mismatch.property_id}`">
                {{mismatch.property_label}}
            </wikit-link>
        </td>
        <td :data-header="$i18n('column-wikidata-value')">
            <wikit-link class="break-line-link" :href="statementUrl" target="_blank">
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
        <td :data-header="$i18n('column-review-status')">
            <dropdown
                :menuItems="Object.values(statusOptions)"
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
              :href="`${mismatch.import_meta.external_source_url}`" target="_blank"
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
            <wikit-dialog class="full-description-dialog"
              :title="$i18n('column-upload-info')"
              ref="fullDescriptionDialog"
              :actions="[{
                  label: $i18n('confirm-dialog-button'),
                  namespace: 'next-steps-confirm'
              }]"
              @action="(_, dialog) => dialog.hide()"
              dismiss-button
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
          </wikit-dialog>
        </td>
    </tr>
</template>

<script lang="ts">
import { formatISO } from 'date-fns';

import Vue, { PropType } from 'vue';
import { Button as WikitButton, Dropdown, Link as WikitLink } from '@wmde/wikit-vue-components';
import { MenuItem } from '@wmde/wikit-vue-components/dist/components/MenuItem';
import WikitDialog from '../Components/Dialog.vue';

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
}

export default Vue.extend({
    components: {
    WikitButton,
    WikitLink,
    WikitDialog,
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
    };
  },
  methods: {
    showDialog(e: Event) {
      e.preventDefault();
      /* eslint-disable-next-line @typescript-eslint/no-explicit-any, @typescript-eslint/no-non-null-assertion */
      const descriptionDialog = this.$refs.fullDescriptionDialog! as any;
      descriptionDialog.show();
    } 
  }
});
</script>

<style lang="scss">
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
</style>