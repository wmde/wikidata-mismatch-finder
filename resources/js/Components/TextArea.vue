<template>
    <textarea :class="[
        'wikit-TextArea',
        checkValidLimit() ? `wikit-TextArea--${resize}` : 'wikit-TextArea--vertical'
    ]" :rows="rows"></textarea>
</template>

<script lang="ts">
import Vue from 'vue';
import ResizeLimit from '../types/ResizeLimit';

function isValidLimit(limit: string): boolean {
    return Object.values( ResizeLimit ).includes( limit as ResizeLimit );
}

export default Vue.extend({
    props: {
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
    methods: {
        checkValidLimit(){
            return isValidLimit(this.resize);
        }
    }
});
</script>

<style lang="scss">
    // TODO: Add scrollbars
    // TODO: Make component full width
    // TODO: Add styles from tokens
    .wikit-TextArea {
        display: block;
        width: 100%;
        // The default resizing behaviour should be on the y axis only
        resize: vertical;

        &--horizontal {
            resize: horizontal;
        }

        &--none {
            resize: none;
        }
    }
</style>
