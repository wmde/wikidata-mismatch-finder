<template>
    <div class="wikit-TextArea">
        <span class="wikit-TextArea__label-wrapper">
			<label
				:class="[
					'wikit-TextArea__label'
				]"
				:for="id"
			>
				{{ label }}
			</label>
		</span>
        <textarea :class="[
            'wikit-TextArea__textarea',
            checkValidLimit() ? `wikit-TextArea__textarea--${resize}` : 'wikit-TextArea__textarea--vertical'
        ]" :rows="rows" placeholder="Potato!!" label=""></textarea>
    </div>
</template>

<script lang="ts">
import Vue from 'vue';
import generateId from '../lib/uid';
import ResizeLimit from '../types/ResizeLimit';

function isValidLimit(limit: string): boolean {
    return Object.values( ResizeLimit ).includes( limit as ResizeLimit );
}

export default Vue.extend({
    props: {
        label: {
            type: String,
            default: ''
        },
        placeholder: {
            type: String,
            default: ''
        },
        rows: {
            type: Number,
            default: 2
        },
        resize: {
			type: String,
			validator( value: string ): boolean {
				return isValidLimit(value);
			},
            default: ResizeLimit.Vertical
        }
    },

    data() {
		return {
			// https://github.com/vuejs/vue/issues/5886
			id: generateId( 'wikit-TextArea' ),
		};
	},

    methods: {
        checkValidLimit(){
            return isValidLimit(this.resize);
        }
    }
});
</script>

<style lang="scss">
    @import '~@wmde/wikit-tokens/dist/_variables.scss';

    @mixin Label($displayType: "inline") {
        color: $wikit-Label-font-color;
        font-family: $wikit-Label-font-family;
        font-size: $wikit-Label-font-size;
        font-weight: $wikit-Label-font-weight;
        line-height: $wikit-Label-line-height;
        display: $displayType;
        overflow-wrap: break-word;
        hyphens: auto;

        &--disabled {
            color: $wikit-Label-font-color-disabled;
        }

        @if $displayType == block {
            padding-block-end: $wikit-Label-padding-block-end;
        }
    }

    .wikit-TextArea {
        &__label-wrapper {
            display: flex;
            align-items: center;
            gap: $dimension-spacing-small;
        }

        &__label {
            @include Label("block");
        }
    }

    .wikit-TextArea__textarea {
        display: block;
        width: 100%;
        // The default resizing behaviour should be on the y axis only
        resize: vertical;
        padding-inline: $wikit-Input-desktop-padding-inline;
        padding-block: $wikit-Input-desktop-padding-block;
        box-shadow: inset 0 0 0 1px transparent; //TODO: replace by token

        @media (max-width: $width-breakpoint-mobile) {
            padding-inline: $wikit-Input-mobile-padding-inline;
            padding-block: $wikit-Input-mobile-padding-block;
        }

        /**
         */
        border-color: $wikit-Input-border-color;
        border-style: $wikit-Input-border-style;
        border-width: $wikit-Input-border-width;
        border-radius: $wikit-Input-border-radius;

        background-color:  $wikit-Input-background-color;
        color: $wikit-Input-color;

        font-family: $wikit-Input-font-family;
        font-size: $wikit-Input-font-size;
        font-weight: $wikit-Input-font-weight;
        line-height: $wikit-Input-line-height;

        &:hover {
            border-color: $wikit-Input-hover-border-color;
        }

        &:focus,
        &:active {
            border-color: $wikit-Input-active-border-color;
            box-shadow: $wikit-Input-active-box-shadow;
        }

        &:focus {
            outline: none;
        }

        &::placeholder {
            font-family: $wikit-Input-placeholder-font-family;
            font-size: $wikit-Input-placeholder-font-size;
            font-weight: $wikit-Input-placeholder-font-weight;
            line-height: $wikit-Input-placeholder-line-height;
            color: $wikit-Input-placeholder-color;
        }

        &--horizontal {
            resize: horizontal;
        }

        &--none {
            resize: none;
        }
    }
</style>
