<template>
    <div :class="[
        'wikit',
        'wikit-Dialog'
    ]" v-show="open">
        <div class="wikit-Dialog__overlay" @click="hide"></div>
        <div class="wikit-Dialog__modal">
            <header class="wikit-Dialog__header">
                <span class="wikit-Dialog__title">{{title}}</span>
                <wikit-button v-if="dismissible"
                    class="wikit-Dialog__close"
                    variant="quiet"
                    type="neutral"
                    aria-label="close"
                    icon-only
                    @click.native="hide"
                >
                    <icon type="clear" size="medium" />
                </wikit-button>
            </header>
            <section class="wikit-Dialog__content">
                <slot></slot>
            </section>
            <footer class="wikit-Dialog__footer">
                <wikit-button v-for="(action, i) in actions"
                    :key="i"
                    :class="[
                        'wikit-Dialog__action',
                        action.namespace
                    ]"
                    :variant="i === 0 ? 'primary' : 'normal'"
                    :type="i === 0 ? 'progressive' : 'neutral'"
                    @click.native="dispatch(action.namespace)"
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

import { Button as WikitButton, Icon } from '@wmde/wikit-vue-components';

interface DialogAction {
    label: string,
    namespace: string
}

export default defineComponent({
    components: {
        WikitButton,
        Icon
    },
    props: {
        title: {
            type: String,
            required: true
        },
        actions: {
            type: Array as PropType<DialogAction[]>,
            required: true
        },
        dismissible: {
            type: Boolean,
            default: false
        },
        visible: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            open: this.visible
        }
    },
    watch: {
        visible(){
            this.open = this.visible
        }
    },
    methods: {
        hide(){
            this.open = false;
            this.$emit('update:visible', this.open);
        },
        show(){
            this.open = true;
            this.$emit('update:visible', this.open);
        },
        dispatch(namespace: string){
            this.$emit('action', namespace, this)
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
        display: flex;
        justify-content: space-between;
        align-items: center;

        color: $wikit-Dialog-header-color;

        padding-block-start: $wikit-Dialog-header-spacing-top;
        padding-block-end: $wikit-Dialog-header-spacing-bottom-complex;
        padding-inline-start: $wikit-Dialog-header-spacing-left;
        padding-inline-end: $wikit-Dialog-header-spacing-right;

        #{$base}__title {
            font-family: $wikit-Dialog-header-font-family;
            font-size: $wikit-Dialog-header-font-size;
            font-weight: $wikit-Dialog-header-font-weight;
            line-height: $wikit-Dialog-header-line-height;
        }

        // $wikit-Dialog-header-box-shadow: inset 0 1px 0 0 #c8ccd1; // only for complex dialogs: divider to be displayed when scroll is activated
    }

    #{$base}__content {
        padding-block-start: $wikit-Dialog-body-spacing-top-complex;
        padding-block-end: $wikit-Dialog-body-spacing-bottom-complex;
        padding-inline-start: $wikit-Dialog-body-spacing-left;
        padding-inline-end: $wikit-Dialog-body-spacing-left;
    }

    #{$base}__footer {
        display: flex;
        flex-direction: row-reverse;
        flex-wrap: wrap;

        box-shadow: $wikit-Dialog-footer-box-shadow;

        padding-block-start: $wikit-Dialog-footer-spacing-top-complex;
        padding-inline-start: $wikit-Dialog-footer-spacing-left;
        padding-inline-end: $wikit-Dialog-footer-spacing-left;

        #{$base}__action {
            margin-block-end: $wikit-Dialog-footer-spacing-bottom-complex;
            margin-inline-start: $wikit-Dialog-footer-spacing;
        }
    }
</style>
