<template>
    <div class="loading-indicator" v-if="shown">
        <div class="overlay" >
            <cdx-progress-bar aria-label="Indeterminate progress bar"/>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { CdxProgressBar } from "@wikimedia/codex";

export default defineComponent({
    name: 'LoadingOverlay',
    props: {
        delay: {
            type: Number,
            default: 250,
        },
        visible: {
            type: Boolean,
            default: false,
        }
    },
    components: {
        CdxProgressBar
    },
    data() {
        return {
            shown: this.visible,
            cachedStyles: {
                overflow: 'auto'
            }
        };
    },
    methods: {
        show(): void {
            if (this.shown) {
                return;
            }

            // Determine the current styles for body, in order to cache the overflow
            const bodyStyles = window.getComputedStyle(document.body);
            this.shown = true;

            this.cachedStyles.overflow = bodyStyles.overflow;
            document.body.style.overflow = 'hidden';
        },
        hide(): Promise<void> {
            return new Promise((resolve) => {
                setTimeout(() => {
                    this.shown = false;
                    // Restore previous overflow value
                    document.body.style.overflow = this.cachedStyles.overflow;
                    resolve();
                }, this.delay);
            });
        },
    },
});
</script>

<style lang="scss">
@import "@wikimedia/codex-design-tokens/theme-wikimedia-ui";

$base: '.loading-indicator';

#{$base} {

    .overlay {
        /**
        * Layout
        */
        display: flex;
        align-items: center;
        justify-content: center;
        position: fixed;
        top: 0;
        left: 0;
        z-index: $z-index-overlay-backdrop;
        min-height: $size-full;
        width: $size-viewport-width-full;
        height: $size-viewport-height-full;
        // Support Safari/iOS: Make `100vh` work with Safari's address bar.
        // See https://stackoverflow.com/questions/37112218/css3-100vh-not-constant-in-mobile-browser
        /* stylelint-disable-next-line plugin/no-unsupported-browser-features,
            scale-unlimited/declaration-strict-value */
        height: -webkit-fill-available;
        /**
        * Colors
        */
        background-color: $background-color-backdrop-light;

    }

    .cdx-progress-bar {
        min-width: 432px;
        
        @media (max-width: $width-breakpoint-tablet) {
            min-width: 90vw;
        }
    }
}
</style>
