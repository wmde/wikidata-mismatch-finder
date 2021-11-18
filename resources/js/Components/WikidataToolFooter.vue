<template>
    <div class="footer-container">
        <footer class="content-wrap">
            <section>
                <h2 class="h5">About Mismatch Finder</h2>
                <p>Licensed under <a href="#">BSD 3-Clause License</a></p>
                <p><a href="#">View Source</a></p>
                <p><a href="#">Report an issue</a></p>
            </section>
            <section>
                <h2 class="h5">About us</h2>
                <p><a href="#">Privacy Policy</a></p>
                <p><a href="#">Wikimedia Deutschland</a></p>
                <p><a href="#">Made by the Wikidata team</a></p>
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
