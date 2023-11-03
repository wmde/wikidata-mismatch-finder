<template>
	<div class="languageSelector__input__wrapper">
		<div class="languageSelector__input-left-side">
			<div class="languageSelector__input__search-icon">
				<img :src="searchUrl" alt="">
			</div>
			<input
				ref="input"
				type="text"
				class="languageSelector__input"
				:value="value"
				:placeholder="placeholder"
				@input="onInput"
				@keydown.tab="onTab"
				@keydown.down.prevent="onArrowDown"
				@keydown.up.prevent="onArrowUp"
				@keydown.enter="onEnter"
				@keydown.esc.prevent="onEscape"
			>
		</div>
		<button
			class="languageSelector__input__clear-button"
			:class="clearBtnVisible ? 'languageSelector__input__clear-button--visible' : ''"
			@click="onClearInputValue"
		>
			<img :src="clearUrl" :alt="$i18n( 'language-selector-clear-button-label' )">
		</button>
	</div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import searchUrl from '../../img/search.svg';
import clearUrl from '../../img/clear.svg';

export default defineComponent( {
	name: 'LanguageSelectorInput',
	props: {
		value: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			default: '',
		},
	},
	data() {
		return {
			searchUrl,
			clearUrl,
		};
	},
	computed: {
		clearBtnVisible(): boolean {
			return this.value.length > 0;
		},
	},
	methods: {
		onClearInputValue(): void {
			this.$emit( 'clear' );
			this.focus();
		},

		focus(): void {
			( this.$refs.input as HTMLInputElement ).focus();
		},
		onArrowDown() {
			this.$emit( 'arrowDown' );
		},
		onArrowUp() {
			this.$emit( 'arrowUp' );
		},
		onEnter() {
			this.$emit( 'enter' );
		},
		onEscape() {
			this.$emit( 'escape' );
		},
		onInput( event : Event ) {
			this.$emit( 'input', (event.target as HTMLInputElement).value );
		},
		onTab() {
			this.$emit( 'tab' );
		},
	},
} );
</script>

<style lang="scss">
.languageSelector__input {
	color: #202122;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Lato, Helvetica, Arial, sans-serif;
	font-size: 1em;
	font-weight: 400;
	box-sizing: border-box;
	flex-grow: 1;
	border-color: #3366cc;
	height: 20px;

	&:focus {
		outline: none;
	}

	&::placeholder {
		color: #72777d;
	}

	&__wrapper {
		background-color: #ffffff;
		border-color: #a2a9b1;
		border-style: solid;
		border-width: 1px;
		border-radius: 2px 2px 0px 0px;
		padding-inline: 16px;
		padding-block: 16px;
		width: 100%;
		display: flex;
		justify-content: space-between;
		box-shadow: 0 1px 2px #00000040, inset 0 0 0 1px #36c;
		border-color: #3366cc;
		align-items: center;
	}

	&-left-side {
		display:flex;
		flex-grow:1;
	}

	&__search-icon {
		display: flex;
		padding-inline-end: 8px;
		height: fit-content;
	}

	&__clear-button {

		visibility: hidden;
		display: flex;

		&--visible {
			visibility: visible;
		}
	}

}
</style>
