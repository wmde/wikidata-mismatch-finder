<template>
    <div :class="[
            'wikit',
            'wikit-Dialog'
        ]"
        v-show="open"
        role="dialog"
        aria-modal="true"
        aria-labelledby="dialog-title"
    >
        <div class="wikit-Dialog__overlay" @click="hide"></div>
        <div class="wikit-Dialog__modal">
            <header class="wikit-Dialog__header">
                <span id="dialog-title" class="wikit-Dialog__title">{{title}}</span>
                <wikit-button v-if="dismissible"
                    ref="closeButton"
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
            <section :class="[
                    'wikit-Dialog__content',
                    scrolled ? 'wikit-Dialog__content--scrolled' : ''
                ]"
                ref="content"
                @scroll="_handleScroll"
            >
                <slot></slot>
            </section>
            <footer class="wikit-Dialog__footer">
                <wikit-button v-for="(action, i) in actions"
                    ref="actionButtons"
                    :key="i"
                    :class="[
                        'wikit-Dialog__action',
                        action.namespace
                    ]"
                    :variant="i === 0 ? 'primary' : 'normal'"
                    :type="i === 0 ? 'progressive' : 'neutral'"
                    @click.native="_dispatch(action.namespace)"
                >
                    {{action.label}}
                </wikit-button>
            </footer>
        </div>
    </div>
</template>

<script lang="ts">
import { PropType } from 'vue';
import throttle from 'lodash/throttle';
import defineComponent from '../types/defineComponent';

import { getScrollbarDimensions, getInteractiveDescendants } from '../lib/dom';
import { Button as WikitButton, Icon } from '@wmde/wikit-vue-components';

interface DialogAction {
    label: string,
    namespace: string
}

interface DocumentData {
    cache: {
        activeElement: Element | null,
        overflow: string,
        padding: {
            x: string,
            y: string
        }
    },
    scrollbars: {
        width: number,
        height: number
    }
}

interface DialogState {
    document: DocumentData,
    focusable: Element[],
    open: boolean,
    scrolled: boolean
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
    data(): DialogState {
        return {
            focusable: [],
            document: {
                cache: {
                    activeElement: null,
                    overflow: 'auto',
                    padding: {
                        x: 'auto',
                        y: 'auto'
                    }
                },
                scrollbars: {
                    width: 0,
                    height: 0
                }
            },
            open: this.visible,
            scrolled: false
        }
    },
    mounted(){
        this.document.scrollbars = getScrollbarDimensions();

        if (this.visible) {
            this.show()
        }
    },
    beforeUpdate(){
        this.focusable = this._collectFocusable();
    },
    beforeDestroy(){
        if (this.open) {
            this.hide()
        }
    },
    watch: {
        visible(open: boolean){
            open ? this.show() : this.hide();
        }
    },
    methods: {
        hide(){
            document.removeEventListener('keydown', this._handleKeydown);
            this.open = false;
            this.$emit('update:visible', this.open);

            this._restoreFocus();
            this._restoreScroll();
        },
        show(){
            document.addEventListener('keydown', this._handleKeydown);
            this.open = true;
            this.$emit('update:visible', this.open);

            this.$nextTick(() => {
                this._trapScroll();
                this._trapFocus();
            });
        },
        _dispatch(namespace: string){
            this.$emit('action', namespace, this)
        },
        _handleKeydown(event : KeyboardEvent){
            switch (event.key) {
                case 'Escape':
                    this.hide();
                    break;
                case 'Tab':
                    this._cycleFocus(event.shiftKey)
                    event.preventDefault();
                    break;
            }
        },
        // In the following function we have to annotate `this` as TS doesn't
        // understand the context of higher order functions such as throttle.
        // The actual first argument of the function is `event`
        _handleScroll: throttle(function (this: DialogState, event: Event) {
            const target = event.target as HTMLElement;
            this.scrolled = target.scrollTop > 0;
        }, 300),
        _collectFocusable(): Element[] {
            const content = this.$refs.content as HTMLElement;
            const actions = this.$refs.actionButtons as WikitButton[];
            const dismiss = this.$refs.closeButton as WikitButton;

            const focusable = getInteractiveDescendants(content);

            const buttonElements = [
                ...actions.map(component => component.$el)
            ];

            if(dismiss) {
                buttonElements.push(dismiss.$el);
            }

            return [
                ...focusable,
                ...buttonElements
            ];
        },
        _cycleFocus(shifted: boolean): void {
            const focusable = this.focusable;
            const indices = {
                current: focusable.indexOf(document.activeElement!),
                offset: shifted ? -1 : 1,
                last: focusable.length - 1,
                next(){ return this.current + this.offset }
            };

            if (indices.next() > indices.last){
                (focusable[0] as HTMLElement).focus();

                return;
            }

            if (indices.next() < 0){
                (focusable[indices.last] as HTMLElement).focus();

                return;
            }

            (focusable[indices.next()] as HTMLElement).focus();
        },
        _trapFocus(){
            const content = this.$refs.content as HTMLElement;
            const target: HTMLElement = content.querySelector('[autofocus]') ?? content;

            this.document.cache.activeElement = document.activeElement;
            if(target !== null) {
                target.focus();
            }
        },
        _trapScroll(){
            const documentStyles = window.getComputedStyle(document.documentElement);

            this.document.cache.overflow = documentStyles.overflow;
            this.document.cache.padding = {
                x: documentStyles.paddingInlineEnd,
                y: documentStyles.paddingBlockEnd
            };

            document.documentElement.style.overflow = 'hidden';
            document.documentElement.style.paddingInlineEnd = `${this.document.scrollbars.width}px`;
            document.documentElement.style.paddingBlockEnd = `${this.document.scrollbars.height}px`;
        },
        _restoreFocus(){
            const lastFocused = this.document.cache.activeElement as HTMLElement;
            if( lastFocused !== null ){
                lastFocused.focus();
            }
        },
        _restoreScroll(){
            document.documentElement.style.overflow = this.document.cache.overflow;
            document.documentElement.style.paddingInlineEnd = this.document.cache.padding.x;
            document.documentElement.style.paddingInlineEnd = this.document.cache.padding.y;
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
        display: flex;
        flex-direction: column;

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
        align-items: baseline;

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

        #{$base}__close.wikit.wikit-Button.wikit-Button--iconOnly {
            line-height: 0;
        }
    }

    #{$base}__content {
        overflow-y: auto;
        padding-block-start: $wikit-Dialog-body-spacing-top-complex;
        padding-block-end: $wikit-Dialog-body-spacing-bottom-complex;
        padding-inline-start: $wikit-Dialog-body-spacing-left;
        padding-inline-end: $wikit-Dialog-body-spacing-left;

        &--scrolled {
            box-shadow: $wikit-Dialog-header-box-shadow;
        }
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
