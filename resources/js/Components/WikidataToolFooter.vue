<template>
    <div class="footer-container">
        <footer :class="contentClass">
            <section>
                <h2 class="h5">
                    {{ $i18n('wikidata-tool-footer-about-tool', labels.tool) }}
                </h2>
                <p v-i18n-html:wikidata-tool-footer-license="[urls.license, labels.license ]"/>
                <p><wikit-link :href="urls.source">{{ $i18n('wikidata-tool-footer-source') }}</wikit-link></p>
                <p><wikit-link :href="urls.issues">{{ $i18n('wikidata-tool-footer-issues') }}</wikit-link></p>
            </section>
            <section>
                <h2 class="h5">{{ $i18n('wikidata-tool-footer-about-us') }}</h2>
                <p>
                    <wikit-link href="https://foundation.wikimedia.org/wiki/Non-wiki_privacy_policy">
                        {{ $i18n('wikidata-tool-footer-privacy') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://www.wikimedia.de/">
                        {{ $i18n('wikidata-tool-footer-wmde') }}
                    </wikit-link>
                </p>
                <p v-i18n-html:wikidata-tool-footer-team="[
                    'https://www.wikidata.org/wiki/Wikidata:Contact_the_development_team'
                ]" />
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
            font-size: $font-size-style-body-s;
        }

        section {
            margin-block-end: 0;
        }

        section + section {
            margin-block-start: $dimension-layout-medium;
        }

        p {
            margin-block-end: 0;
            font-size: $font-size-style-body-s;
        }

        p + p {
            margin-block-start: $dimension-layout-xxsmall;
        }

        @media (min-width: $width-breakpoint-tablet) {
            flex-direction: row;
            padding: $dimension-layout-small;

            section {
                margin-inline-end: $dimension-layout-large;
            }

            section + section {
                margin-block-start: 0;
            }
        }
    }
}
</style>
