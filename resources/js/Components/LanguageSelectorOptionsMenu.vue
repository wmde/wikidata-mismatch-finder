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
      tabindex="-1"
    >
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
      role="option"
    >
      <slot name="no-results" />
    </div>
  </div>
</template>

<script setup lang="ts">
import {ref, watch} from 'vue';
import type Language from '../types/Language';
import type {Ref} from 'vue';

const props = withDefaults(defineProps<{
	languages: Language[]
	highlightedIndex: number
}>(), {
	languages: () => [],
	highlightedIndex: -1
});

const selectedLanguageCode: Ref<string> = ref('');

const container = ref<HTMLElement | null>(null);
const languagesList = ref<HTMLElement | null>(null);

const emit = defineEmits(['select']);

function onSelect(selectedLanguageCodeInput: string): void {
	selectedLanguageCode.value = selectedLanguageCodeInput
	emit('select', selectedLanguageCodeInput)
}

function scrollTo(element: Element, childIdx: number): void {
	const child = element.children.item(childIdx);

	if (child === null) {
		return;
	}

	const containerRect = container.value.getBoundingClientRect(),
		item = child.getBoundingClientRect(),
		above = Math.floor(item.top) < containerRect.top,
		below = Math.ceil(item.bottom) > containerRect.bottom;

	if (!above && !below) {
		return;
	}
	child.scrollIntoView({behavior: 'smooth', block: 'nearest', inline: 'start'});
}

watch(() => props.highlightedIndex,
	(newIndex) => {
		scrollTo(languagesList.value, newIndex);
	})

</script>

<style lang="scss">
@import '~@wikimedia/codex-design-tokens/theme-wikimedia-ui';

$base: '.languageSelector__options-menu';
$tiny-viewport-width: 38em;

#{$base} {
	background-color: #fff;
	border-radius: 0 0 1px 1px;
	border: 1px solid #a2a9b1;
	box-shadow: $box-shadow-drop-medium;
	box-sizing: border-box;
	z-index: 1;
	padding-block: 8px;
	padding-inline: 12px;
	height: 15.25rem;
	overflow-y: scroll;

	@media (max-width: $tiny-viewport-width) {
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
