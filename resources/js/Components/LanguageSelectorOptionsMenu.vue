<template>
	<div
		ref="container"
		class="languageSelector__options-menu"
		role="listbox"
		:aria-label="$i18n( 'language-selector-options-menu-aria-label' )"
	>
		<div
			ref="languagesList"
			class="languageSelector__options-menu__languages-list"
			tabindex="-1">
			<div
				v-for="( language, index ) in languages"
				:key="language.code"
				:aria-selected="language.code === selectedLanguageCode || null"
				class="languageSelector__options-menu__languages-list__item"
				:class="{
					'language--selected': language.code === selectedLanguageCode,
					highlight: highlightedIndex === index
				}"
				role="option"
				@click="onSelect( language.code )"
			>
				{{ language.autonym }}
			</div>
		</div>
		<div
			v-if="languages.length === 0"
			class="languageSelector__options-menu__no-results"
			role="option">
			<slot name="no-results" />
		</div>
	</div>
</template>

<script lang="ts">
import Language from '../types/Language';
import Vue, { PropType } from 'vue';

export default Vue.extend( {
	name: 'LanguageSelectorOptionsMenu',
	props: {
		languages: {
			type: Array as PropType<Language[]>,
			default: (): [] => [],
		},
		highlightedIndex: {
			type: Number,
			default: -1,
		},
	},
	data: () => ( {
		selectedLanguageCode: '',
	} ),
	methods: {
		onSelect( selectedLanguageCode: string ): void {
			this.selectedLanguageCode = selectedLanguageCode;
			this.$emit( 'select', selectedLanguageCode );
		},
		scrollTo( element: Element, childIdx: number ): void {
			const child = element.children.item( childIdx );

			if ( child === null ) {
				return;
			}

			const container = ( this.$refs.container as Element ).getBoundingClientRect(),
				item = child.getBoundingClientRect(),
				above = Math.floor( item.top ) < container.top,
				below = Math.ceil( item.bottom ) > container.bottom;

			if ( !above && !below ) {
				return;
			}
			child.scrollIntoView( { behavior: 'smooth', block: 'nearest', inline: 'start' } );
		},
	},
	watch: {
		highlightedIndex: function ( newIdx ) {
			const languageList = this.$refs.languagesList;

			this.scrollTo( languageList as Element, newIdx );
		},
	},
} );
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';
$base: '.languageSelector__options-menu';
$tinyViewportWidth: 38em;

#{$base} {
	background-color: #ffffff;
	border-radius: 0px 0px 1px 1px;
	border: 1px solid #a2a9b1;
	box-shadow: $wikit-OptionsMenu-box-shadow;
	box-sizing: border-box;
	z-index: 1;
	padding-block: 8px;
	padding-inline: 12px;
	height: 15.25rem;
	overflow-y: scroll;

	@media (max-width: $tinyViewportWidth) {
		flex-grow: 1;
	}

	&__languages-list {

		&__item {
			position: relative;
			padding-block: 8px;
			padding-inline: 32px 0;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Lato, Helvetica, Arial, sans-serif;
			transition-property: background-color;
			transition-duration: 100ms;
			transition-timing-function: ease;

			&:hover, &:active, &.highlight {
				background-color: #EAECF0;
				cursor: pointer;
			}

			&--selected, &--selected:hover {
				background-color: #EAECF0;
			}
		}
	}

	&__no-results {
		font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Lato, Helvetica, Arial, sans-serif;
		font-size: 1em;
		font-weight: normal;
		color: #202122;
		line-height: 1.25;
		padding-block: 8px;
		padding-inline: 8px;
	}
}
</style>
