<template>
    <div class="website">
        <main class="content-wrap">
            <header>
                <img src="/images/wikidata-logo.svg" class="wikidata-logo" alt="Wikidata-logo" width="160" />
                <div class="auth-widget">
                    <AuthWidget :user="user" />
                </div>
            </header>
            <h1>{{ $i18n('mismatch-finder-title') }}</h1>
            <slot />
        </main>
        <wikidata-tool-footer
            content-class="content-wrap"
            :labels="{
                tool: 'Mismatch Finder',
                license: 'BSD 3-Clause License'
            }"
            :urls="{
                license: 'https://github.com/wmde/wikidata-mismatch-finder/blob/93e692f6310595f75dcb971d7fb42a7ed7479af0/LICENSE',
                source: 'https://github.com/wmde/wikidata-mismatch-finder',
                issues: 'https://phabricator.wikimedia.org/project/board/5385/'
            }"
        >
            <section>
                <h2 class="h5">More Data Quality Tools</h2>
                <p><a href="#">Query Builder</a></p>
                <p><a href="#">Item Quality Evaluator</a></p>
                <p><a href="#">Curious Facts</a></p>
                <p><a href="#">Constraint Violation Checker</a></p>
            </section>
        </wikidata-tool-footer>
    </div>
</template>

<script lang="ts">
    import { PropType } from 'vue';
    import AuthWidget from '../Components/AuthWidget.vue';
    import WikidataToolFooter from '../Components/WikidataToolFooter.vue';

    import defineComponent from '../types/defineComponent';
    import User from '../types/User';

    export default defineComponent({
        components: {
            AuthWidget,
            WikidataToolFooter
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
