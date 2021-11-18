<template>
    <div class="footer-container">
        <footer class="content-wrap">
            <section>
                <h2 class="h5">
                    {{ $i18n('wikidata-tool-footer-about-tool', "Mismatch Finder") }}
                </h2>
                <p v-i18n-html:wikidata-tool-footer-license="['#', 'BSD 3-Clause License' ]"/>
                <p><a href="#">{{ $i18n('wikidata-tool-footer-source') }}</a></p>
                <p><a href="#">{{ $i18n('wikidata-tool-footer-issues') }}</a></p>
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

export default Vue.extend({
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
