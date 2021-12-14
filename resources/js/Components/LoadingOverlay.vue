<template>
    <div class="loading-indicator" v-if="shown">
        <div class="progressbar" role="progressbar" />
        <div class="overlay" />
    </div>
</template>

<script lang="ts">
import Vue from 'vue';

export default Vue.extend({
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
    data() {
        return {
            shown: this.visible,
            document: {
                overflow: 'auto'
            }
        };
    },
    methods: {
        show(): void {
            if (this.shown) {
                return;
            }

            const bodyStyles = window.getComputedStyle(document.body);
            this.shown = true;

            this.document.overflow = bodyStyles.overflow;
            document.body.style.overflow = 'hidden';
        },
        hide(): Promise<void> {
            return new Promise((resolve) => {
                setTimeout(() => {
                    this.shown = false;
                    document.body.style.overflow = this.document.overflow;
                    resolve();
                }, this.delay);
            });
        },
    },
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

$base: '.loading-indicator';

#{$base} .overlay {
    /**
    * Layout
    */
    width: $wikit-Dialog-overlay-width;
    height: $wikit-Dialog-overlay-height;
    position: fixed;
    top:0;
    left:0;

    z-index: 100;

    /**
    * Colors
    */
    background-color: $wikit-Dialog-overlay-background-color;
    opacity: $wikit-Dialog-overlay-opacity;
}

#{$base} .progressbar {
    // Currently the inline progress bar only supports indeterminate loading mode.
    // For a proof of concept on how this can include also determinate loading, see:
    // https://codepen.io/xumium/pen/LYLZbva?editors=1100
    // We ensure semantic usage by only targeting generic elements that set the
    // correct role
    &[role=progressbar] {
        position: fixed;
        top: 0;
        left: 0;
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
</style>
