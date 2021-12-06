<template>
    <div class="website">
        <div class="progressbar" v-if="submitting" role="progressbar" />
        <div class="overlay" v-if="submitting" />
        <main class="content-wrap" :class="submitting ? 'noscroll' : ''">
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
    import { mapState } from 'vuex';
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
        },
        computed: {
            // spread to combine with local computed props
            ...mapState(['submitting']),
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.website {
    .content-wrap {
        max-width: 1168px;
    }

    .overlay {
        /**
        * Layout
        */
        width: $wikit-Dialog-overlay-width;
        height: $wikit-Dialog-overlay-height;
        position: fixed;

        z-index: 100;

        /**
        * Colors
        */
        background-color: $wikit-Dialog-overlay-background-color;
        opacity: $wikit-Dialog-overlay-opacity;
    }

    .progressbar {
        // Currently the inline progress bar only supports indeterminate loading mode.
        // For a proof of concept on how this can include also determinate loading, see:
        // https://codepen.io/xumium/pen/LYLZbva?editors=1100
        // We ensure semantic usage by only targeting generic elements that set the
        // correct role 
        &[role=progressbar] {
            position: fixed;
            top: 0;
            width: $wikit-Progress-inline-track-width;
            height: $wikit-Progress-inline-track-height;

            &::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                display: block;
                height: 100%;
                background: $wikit-Progress-inline-background-color;
            }

            // Indeterminate progress bars should not set the `aria-valuenow` 
            // attribute
            &:not([aria-valuenow])::before {
                width: 30%;
                border-radius: $wikit-Progress-inline-indeterminate-border-radius;
                animation-name: load-indeterminate;
                animation-duration: $wikit-Progress-inline-animation-duration;
                animation-timing-function: ease;
                animation-iteration-count: infinite;
                animation-delay: 0s;
            }
        }

        @keyframes load-indeterminate {
            0% { left: 0; }
            50% { left: 70%; }
            100% { left: 0; }
        }
        z-index: 101;
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
