<template>
  <div class="mismatchfinder__language-selector">
    <div class="languageSelector__mobile-header">
      <span>{{ $i18n('language-selector-mobile-header') }}</span>
      <button @click="onCloseMenu">
        <img
          :src="closeUrl"
          :alt="$i18n( 'language-selector-close-button-label' )"
        >
      </button>
    </div>
    <LanguageSelectorInput
      ref="input"
      :value="searchInput"
      :placeholder="$i18n( 'language-selector-input-placeholder' )"
      @input="onInput"
      @clear="onClearInputValue"
      @tab="onCloseMenu"
      @arrow-down="onArrowDown"
      @arrow-up="onArrowUp"
      @enter="onEnter"
      @escape="onCloseMenu"
    />
    <LanguageSelectorOptionsMenu
      tabindex="-1"
      :languages="shownLanguages"
      :highlighted-index="highlightedIndex"
      @select="onSelect"
    >
      <template #no-results>
        <slot name="no-results" />
      </template>
    </LanguageSelectorOptionsMenu>
  </div>
</template>

<script setup lang="ts">
import LanguageSelectorOptionsMenu from "./LanguageSelectorOptionsMenu.vue";
import LanguageSelectorInput from "./LanguageSelectorInput.vue";
import Language from '../types/Language';
import closeUrlSvg from '../../img/close.svg';
import axios from 'axios';
import {ref, computed} from "vue";
import type {Ref} from 'vue';
import languageData from "@wikimedia/language-data";

const searchInput: Ref<string> = ref('');
const highlightedIndex: Ref<number> = ref(-1);
const closeUrl = ref(closeUrlSvg);
const apiLanguageCodes = ref(['']);

const input = ref<InstanceType<typeof LanguageSelectorInput> | null>(null);

const emit = defineEmits(['select', 'close']);

const languages = computed<Language[]>(() => {
	const autonyms = languageData.getAutonyms();
	const languageCodes = Object.keys(autonyms);
	languageCodes.sort(languageData.sortByAutonym);
	return languageCodes.map((code) => ({
		code,
		autonym: autonyms[code],
	}));
});

const shownLanguages = computed<Language[]>(() => {
    return languages.value.filter((language) =>
        language.code.startsWith(searchInput.value.toLowerCase()) ||
        language.autonym.toLowerCase().includes(searchInput.value.toLowerCase()) ||
        apiLanguageCodes.value.includes(language.code)
    )
});

function onInput(searchedLanguage: string): void {
	searchInput.value = searchedLanguage;
    getApiLanguageCodes(searchInput.value);
	highlightedIndex.value = 0;
}

async function getApiLanguageCodes(inputValue: string) {
    return await axios.get(
        'https://www.wikidata.org/w/api.php?action=languagesearch&format=json&formatversion=2',
        {
            params: {
                search: inputValue,
                origin: '*' // avoid CORS console errors
            }
        }).then((response) => {
            apiLanguageCodes.value = Object.keys(response.data.languagesearch);
        });
}

function onSelect(languageCode: string): void {
	emit('select', languageCode);
}

function onClearInputValue(): void {
	searchInput.value = '';
}

function onCloseMenu(): void {
	emit('close');
}

// eslint-disable-next-line @typescript-eslint/no-unused-vars
function focus(): void {
	input.value?.focus();
}

function onArrowDown(): void {
	highlightedIndex.value = (highlightedIndex.value + 1) % shownLanguages.value.length;
}

function onArrowUp(): void {
	const length = shownLanguages.value.length;
	highlightedIndex.value = (highlightedIndex.value + length - 1) % length;
}

function onEnter(): void {
	onSelect(shownLanguages.value[highlightedIndex.value].code)
}

defineExpose({focus})

</script>

<style lang="scss">
$tiny-viewport-width: 38em;

.mismatchfinder__language-selector {
	position: absolute;
	inset-inline-end: 0;
	width: 384px;
	z-index: 1;

	@media (max-width: $tiny-viewport-width) {
		width: 100%;
		position: fixed;
		top: 0;
		display: flex;
		flex-direction: column;
		height: 100%;
	}

	.languageSelector__mobile-header {
		display: none;
		padding-block: 12px;
		padding-inline: 16px;
		justify-content: space-between;
		background-color: #fff;

		span {
			color: #202122;
			font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Lato, Helvetica, Arial, sans-serif;
			font-size: 1em;
			font-weight: bold;
		}

		@media (max-width: $tiny-viewport-width) {
			display: flex;
		}
	}
}
</style>
