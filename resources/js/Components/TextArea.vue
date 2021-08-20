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
