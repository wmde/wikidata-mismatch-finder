<template>
    <div class="website">
        <main class="content-wrap">
            <header>
                <InertiaLink href="/">
                    <div class="mismatch-finder-logo" />
                    <h1 class="visually-hidden">{{ $i18n('mismatch-finder-title') }}</h1>
                </InertiaLink>
                <div class="auth-widget">
                    <auth-widget :user="user" />
                </div>
            </header>
            <slot />
        </main>
        <wikidata-tool-footer
            content-class="content-wrap"
            :labels="{
                tool: $i18n('mismatch-finder-title'),
                license: $i18n('mismatch-finder-license'),
            }"
            :urls="{
                license: 'https://github.com/wmde/wikidata-mismatch-finder/blob/license/bsd-3-clause/LICENSE',
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
    import { Link as InertiaLink } from '@inertiajs/inertia-vue';
    import { Link as WikitLink } from '@wmde/wikit-vue-components';

    import AuthWidget from '../Components/AuthWidget.vue';
    import WikidataToolFooter from '../Components/WikidataToolFooter.vue';

    import defineComponent from '../types/defineComponent';
    import User from '../types/User';

    export default defineComponent({
        components: {
            AuthWidget,
            InertiaLink,
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

    .mismatch-finder-logo {
        margin-block-end: $dimension-layout-small;
        background-image: url('/images/mismatch-finder-logo.svg');
        width: 384px;
        height: 24px;
        @media (max-width: $width-breakpoint-tablet) {
            background-image: url('/images/mismatch-finder-logo_mobile.svg');
            width: 268px;
            height: 24px;
        }
    }

    .visually-hidden:not(:focus):not(:active) {
        clip: rect(0 0 0 0);
        clip-path: inset(100%);
        height: 1px;
        overflow: hidden;
        position: absolute;
        white-space: nowrap;
        width: 1px;
    }

    main > header {
        flex-direction: column;
    }

    @media (min-width: $width-breakpoint-tablet) {
        main > header {
            flex-direction: row;
        }

        .mismatch-finder-logo {
            margin-block-end: 0;
        }
    }
}
</style>
