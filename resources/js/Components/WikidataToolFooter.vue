<template>
    <div class="footer-container">
        <footer :class="contentClass">
            <section>
                <h2 class="h5">
                    {{ $i18n('wikidata-tool-footer-about-tool', labels.tool) }}
                </h2>
                <p v-i18n-html:wikidata-tool-footer-license="[urls.license, labels.license ]"/>
                <p><a :href="urls.source">{{ $i18n('wikidata-tool-footer-source') }}</a></p>
                <p><a :href="urls.issues">{{ $i18n('wikidata-tool-footer-issues') }}</a></p>
            </section>
            <section>
                <h2 class="h5">{{ $i18n('wikidata-tool-footer-about-us') }}</h2>
                <p><a href="#">{{ $i18n('wikidata-tool-footer-privacy') }}</a></p>
                <p><a href="#">{{ $i18n('wikidata-tool-footer-wmde') }}</a></p>
                <p v-i18n-html:wikidata-tool-footer-team="['#', 'Wikidata Team']" />
            </section>
            <slot />
        </footer>
    </div>
</template>

<script lang="ts">
import Vue, { PropType } from 'vue';
import { Link as WikitLink } from '@wmde/wikit-vue-components';

interface FooterLabels {
    tool: string;
    license: string;
}

interface FooterUrls {
    license: string;
    source: string;
    issues: string;
}

export default Vue.extend({
    name: 'WikidataToolFooter',
    components: {
        WikitLink,
    },
    props: {
        contentClass: {
            type: String,
            default: '',
        },
        labels: {
            type: Object as PropType<FooterLabels>,
            required: true,
        },
        urls: {
            type: Object as PropType<FooterUrls>,
            required: true,
        },
    }
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.footer-container {
    background-color: $color-base-90;

    & > footer {
        margin: auto;
        padding: $dimension-layout-xsmall;
        display: flex;
        flex-direction: column;

        .h5 {
            margin: 0 0 $dimension-layout-xsmall 0;
        }

        section {
            margin-block-end: $dimension-layout-small;
        }

        p {
            margin-block-end: 0;
        }

        p + p {
            margin-block-start: $dimension-layout-xxsmall;
        }

        @media (min-width: $width-breakpoint-tablet) {
            flex-direction: row;
            padding: $dimension-layout-small;

            & section {
                margin-inline-end: $dimension-layout-large;
            }
        }
    }
}
</style>
