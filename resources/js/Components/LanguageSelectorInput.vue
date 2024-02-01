<template>
  <div class="languageSelector__input__wrapper">
    <div class="languageSelector__input-left-side">
      <div class="languageSelector__input__search-icon">
        <img
          :src="searchUrl"
          alt=""
        >
      </div>
      <input
        ref="input"
        type="text"
        class="languageSelector__input"
        :value="value"
        :placeholder="placeholder"
        @input="onInput"
        @keydown.tab="$emit('tab')"
        @keydown.down.prevent="$emit('arrowDown')"
        @keydown.up.prevent="$emit('arrowUp')"
        @keydown.enter="$emit('enter')"
        @keydown.esc.prevent="$emit('escape')"
      >
    </div>
    <button
      class="languageSelector__input__clear-button"
      :class="clearBtnVisible ? 'languageSelector__input__clear-button--visible' : ''"
      @click="onClearInputValue"
    >
      <img
        :src="clearUrl"
        :alt="$i18n( 'language-selector-clear-button-label' )"
      >
    </button>
  </div>
</template>

<script setup lang="ts">
import {ref, computed} from 'vue';
import searchUrlSvg from '../../img/search.svg';
import clearUrlSvg from '../../img/clear.svg';

const props = defineProps<{
	value: string,
	placeholder: string
}>();

const emit = defineEmits(['clear', 'arrowDown', 'arrowUp', 'enter', 'escape', 'input', 'tab'])

const searchUrl = ref(searchUrlSvg);
const clearUrl = ref(clearUrlSvg);

const input = ref<HTMLInputElement | null>(null);

const clearBtnVisible = computed<boolean>(() => {
	return props.value.length > 0;
})

function focus(): void {
	(input.value as HTMLInputElement).focus();
}

function onClearInputValue(): void {
	emit('clear');
	focus();
}

function onInput(event: Event) {
	emit('input', (event.target as HTMLInputElement).value);
}

defineExpose({focus});
</script>

<style lang="scss">
.languageSelector__input {
	color: #202122;
	font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Lato, Helvetica, Arial, sans-serif;
	font-size: 1em;
	font-weight: 400;
	box-sizing: border-box;
	flex-grow: 1;
	border-color: #36c;
	height: 20px;

	&:focus {
		outline: none;
	}

	&::placeholder {
		color: #72777d;
	}

	&__wrapper {
		background-color: #fff;
		border-style: solid;
		border-width: 1px;
		border-radius: 2px 2px 0 0;
		padding-inline: 16px;
		padding-block: 16px;
		width: 100%;
		display: flex;
		justify-content: space-between;
		box-shadow: 0 1px 2px #00000040, inset 0 0 0 1px #36c;
		border-color: #36c;
		align-items: center;
	}

	&-left-side {
		display: flex;
		flex-grow: 1;
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
