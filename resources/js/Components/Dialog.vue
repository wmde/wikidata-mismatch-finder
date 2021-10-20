<template>
    <div :class="[
        'wikit',
        'wikit-Dialog'
    ]">
        <div class="wikit-Dialog__overlay"></div>
        <div class="wikit-Dialog__modal">
            <header class="wikit-Dialog__header">{{title}}</header>
            <footer class="wikit-Dialog__footer">
                <wikit-button v-for="(action, i) in actions"
                    :key="i"
                    :class="[
                        'wikit-Dialog__action',
                        action.namespace
                    ]"
                    :variant="i === 0 ? 'primary' : 'normal'"
                    :type="i === 0 ? 'progressive' : 'neutral'"
                >
                    {{action.label}}
                </wikit-button>
            </footer>
        </div>
    </div>
</template>

<script lang="ts">
import { PropType } from 'vue';
import defineComponent from '../types/defineComponent';

import { Button as WikitButton } from '@wmde/wikit-vue-components';

interface DialogAction {
    label: string,
    namespace: string
}

export default defineComponent({
    components: {
        WikitButton
    },
    props: {
        title: {
            type: String,
            required: true
        },
        actions: {
            type: Array as PropType<DialogAction[]>,
            required: true
        }
    }
});
</script>

<style lang="scss">
    @import '~@wmde/wikit-tokens/dist/_variables.scss';
    $base: '.wikit-Dialog';

    #{$base} {
        /**
        * Layout
        */
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
    }

    #{$base}__overlay {
        /**
        * Layout
        */
        width: $wikit-Dialog-overlay-width;
        height: $wikit-Dialog-overlay-height;
        position: absolute;
        top: 0;
        left: 0;

        /**
        * Colors
        */
        background-color: $wikit-Dialog-overlay-background-color;
        opacity: $wikit-Dialog-overlay-opacity;

        // $wikit-Dialog-overlay-transition-duration: 250ms;
    }

    #{$base}__modal {
        /**
        * Layout
        */
        width: $wikit-Dialog-width-complex;
        max-width: 75%;
        max-height: 90%;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);

        /**
        * Colors
        */
        color: $wikit-Dialog-body-color;
        background-color: $wikit-Dialog-background-color;

        /**
        * Typography
        */
        font-family: $wikit-Dialog-body-font-family;
        font-size: $wikit-Dialog-body-font-size;
        font-weight: $wikit-Dialog-body-font-weight;
        line-height: $wikit-Dialog-body-line-height;

        /**
        * Borders
        */
        border-style: $wikit-Dialog-border-style;
        border-width: $wikit-Dialog-border-width;
        border-radius: $wikit-Dialog-border-radius;
        box-shadow: $wikit-Dialog-elevation;

        // $wikit-Dialog-transition-duration: 250ms;
    }

    #{$base}__header {
        color: $wikit-Dialog-header-color;

        font-family: $wikit-Dialog-header-font-family;
        font-size: $wikit-Dialog-header-font-size;
        font-weight: $wikit-Dialog-header-font-weight;
        line-height: $wikit-Dialog-header-line-height;

        padding-block-start: $wikit-Dialog-header-spacing-top;
        padding-block-end: $wikit-Dialog-header-spacing-bottom-complex;
        padding-inline-start: $wikit-Dialog-header-spacing-left;
        padding-inline-end: $wikit-Dialog-header-spacing-left;

        // $wikit-Dialog-header-box-shadow: inset 0 1px 0 0 #c8ccd1; // only for complex dialogs: divider to be displayed when scroll is activated
    }

    #{$base}__content {
        padding-block-start: $wikit-Dialog-body-spacing-top-complex;
        padding-block-end: $wikit-Dialog-body-spacing-bottom-complex;
        padding-inline-start: $wikit-Dialog-body-spacing-left;
        padding-inline-end: $wikit-Dialog-body-spacing-left;
    }

    #{$base}__footer {
        box-shadow: $wikit-Dialog-footer-box-shadow;

        padding-block-start: $wikit-Dialog-footer-spacing-top-complex;
        padding-inline-start: $wikit-Dialog-footer-spacing-left;
        padding-inline-end: $wikit-Dialog-footer-spacing-left;

        display: flex;
        flex-direction: row-reverse;
        flex-wrap: wrap;

        #{$base}__action {
            margin-block-end: $wikit-Dialog-footer-spacing-bottom-complex;
            margin-inline-end: $wikit-Dialog-footer-spacing;
        }
    }
</style>
