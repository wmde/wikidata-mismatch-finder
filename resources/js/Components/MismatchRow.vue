<template>
    <tr>
        <td :data-header="$i18n('column-property')">
            <wikit-link :href="`https://www.wikidata.org/wiki/Property:${mismatch.property_id}`">
                {{mismatch.property_label}}
            </wikit-link>
        </td>
        <td :data-header="$i18n('column-wikidata-value')">{{mismatch.value_label || mismatch.wikidata_value}}</td>
        <td :data-header="$i18n('column-external-value')">{{mismatch.external_value}}</td>
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
import { Link as WikitLink } from '@wmde/wikit-vue-components';

import { LabelledMismatch } from '../types/Mismatch';

export default Vue.extend({
    components: {
        WikitLink
    },
    props: {
        mismatch: Object as PropType<LabelledMismatch>
    },
    computed: {
        uploadDate(): string {
            return formatISO(new Date(this.mismatch.import_meta.created_at), {
                representation: 'date'
            });
        }
    }
});
</script>
