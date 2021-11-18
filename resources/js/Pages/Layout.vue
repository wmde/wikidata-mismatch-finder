<template>
    <div class="website">
        <main class="content-wrap">
            <header>
                <img src="/images/wikidata-logo.svg" class="wikidata-logo" alt="Wikidata-logo" width="160" />
                <div class="auth-widget">
                    <auth-widget :user="user" />
                </div>
            </header>
            <h1>{{ $i18n('mismatch-finder-title') }}</h1>
            <slot />
        </main>
        <wikidata-tool-footer
            content-class="content-wrap"
            :labels="{
                tool: $i18n('mismatch-finder-title'),
                license: $i18n('mismatch-finder-license'),
            }"
            :urls="{
                // eslint-disable-next-line max-len
                license: 'https://github.com/wmde/wikidata-mismatch-finder/blob/93e692f6310595f75dcb971d7fb42a7ed7479af0/LICENSE',
                source: 'https://github.com/wmde/wikidata-mismatch-finder',
                issues: 'https://phabricator.wikimedia.org/project/board/5385/'
            }"
        >
            <section>
                <h2 class="h5">{{ $i18n('mismatch-finder-footer-more-tools') }}</h2>
                <p>
                    <wikit-link href="https://query.wikidata.org/querybuilder/">
                        {{ $i18n('tool-query-builder') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://item-quality-evaluator.toolforge.org/">
                        {{ $i18n('tool-item-quality-evaluator') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://wikidata-analytics.wmcloud.org/app/CuriousFacts">
                        {{ $i18n('tool-curious-facts') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://github.com/wmde/wikidata-constraints-violation-checker">
                        {{ $i18n('tool-constraints-violation-checker') }}
                    </wikit-link>
                </p>
            </section>
        </wikidata-tool-footer>
    </div>
</template>

<script lang="ts">
    import { PropType } from 'vue';
    import { Link as WikitLink } from '@wmde/wikit-vue-components';

    import AuthWidget from '../Components/AuthWidget.vue';
    import WikidataToolFooter from '../Components/WikidataToolFooter.vue';

    import defineComponent from '../types/defineComponent';
    import User from '../types/User';

    export default defineComponent({
        components: {
            AuthWidget,
            WikidataToolFooter,
            WikitLink
        },
        props: {
            user: Object as PropType<User>
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.website {
    .content-wrap {
        max-width: 1168px;
    }

    .wikidata-logo {
        margin-block-end: $dimension-layout-small;
    }

    main > header {
        flex-direction: column;
    }

    @media (min-width: $width-breakpoint-tablet) {
        main > header {
            flex-direction: row;
        }

        .wikidata-logo {
            margin-block-end: 0;
        }
    }
}
</style>
