<template>
  <main
    class="content-wrap"
    ref="contentWrap"
  >
    <header ref="header">
      <InertiaLink
        class="logo-link"
        href="/"
      >
        <div class="mismatch-finder-logo" />
        <h1 class="visually-hidden">
          {{ $i18n('mismatch-finder-title') }}
        </h1>
      </InertiaLink>
      <div
        class="userSection"
        ref="userSection"
      >
        <div
          v-detect-click-outside="onClickOutsideLanguageSelector"
          class="languageSelector"
        >
          <LanguageSelectorButton
            :aria-label="$i18n('toggle-language-selector-button')"
            @click="onToggleLanguageSelector"
          >
            <cdx-icon :icon="cdxIconLanguage" />
            <span class="text-with-icon-button">{{ currentLanguageAutonym }}</span>
          </LanguageSelectorButton>
          <LanguageSelector
            v-show="showLanguageSelector"
            ref="languageSelector"
            @close="onCloseLanguageSelector"
            @select="onChangeLanguage"
          >
            <template #no-results>
              {{ $i18n('language-selector-no-results') }}
            </template>
          </LanguageSelector>
        </div>
        <auth-widget :user="user" />
      </div>
    </header>
    <slot />
  </main>
  <wikidata-tool-footer
    content-class="content-wrap"
    :labels="{
      tool: $i18n('mismatch-finder-title'),
      license: $i18n('mismatch-finder-license'),
    }"
    :urls="{
      license: 'https://github.com/wmde/wikidata-mismatch-finder/blob/license/bsd-3-clause/LICENSE',
      source: 'https://github.com/wmde/wikidata-mismatch-finder',
      issues: 'https://phabricator.wikimedia.org/project/board/5385/'
    }"
  >
    <section>
      <h2 class="h5">
        {{ $i18n('mismatch-finder-footer-more-tools') }}
      </h2>
      <p>
        <a href="https://query.wikidata.org/querybuilder/">
          {{ $i18n('tool-query-builder') }}
        </a>
      </p>
      <p>
        <a href="https://item-quality-evaluator.toolforge.org/">
          {{ $i18n('tool-item-quality-evaluator') }}
        </a>
      </p>
      <p>
        <a href="https://github.com/wmde/wikidata-constraints-violation-checker">
          {{ $i18n('tool-constraints-violation-checker') }}
        </a>
      </p>
    </section>
  </wikidata-tool-footer>
</template>

<script setup lang="ts">
import { Link as InertiaLink } from '@inertiajs/inertia-vue3';
import { CdxButton as LanguageSelectorButton, CdxIcon } from "@wikimedia/codex";
import { cdxIconLanguage } from '@wikimedia/codex-icons';
import AuthWidget from '../Components/AuthWidget.vue';
import LanguageSelector from '../Components/LanguageSelector.vue';
import WikidataToolFooter from '../Components/WikidataToolFooter.vue';
import { DirectiveBinding, ComponentPublicInstance } from 'vue';
import { ref, computed, nextTick, onMounted, onBeforeUnmount } from 'vue';
import type { Ref } from 'vue';
import User from '../types/User';
import languagedata from '@wikimedia/language-data';

let handleOutsideClick: (event: MouseEvent | TouchEvent) => void;

const showLanguageSelector = ref(false); 
const resizeObserver: Ref<ResizeObserver> = ref(null);
const languageSelector: Ref<ComponentPublicInstance> = ref(null);
const header: Ref<HTMLElement> = ref(null);
const userSection: Ref<HTMLElement> = ref(null);
const contentWrap: Ref<Element> = ref(null);

defineProps<{user: User | null}>();

const currentLanguageAutonym = computed<string>(() => {
    return languagedata.getAutonym(document.documentElement.lang);
});

const vDetectClickOutside = {
    mounted: (element: HTMLElement, binding: DirectiveBinding): void => {
        handleOutsideClick = (event: MouseEvent | TouchEvent): void => {
            const callback = binding.value;
            if (!element.contains(event.target as Node)) {
                callback();
            }
        };

        document.addEventListener('click', handleOutsideClick);
        document.addEventListener('touchstart', handleOutsideClick);
    },
    unmounted(): void {
        document.removeEventListener('click', handleOutsideClick);
        document.removeEventListener('touchstart', handleOutsideClick);
    }
};

function onChangeLanguage(newLanguage: string): void {
    /**
     * Manipulate the url to maintain it as the single source of truth
     * and avoid having either to load all language files upfront or
     * request language file reactively.
     */
    const url = new URL(document.URL);
    url.searchParams.set('uselang', newLanguage);
    document.location.assign(url.toString());
}

function onCloseLanguageSelector(): void {
    showLanguageSelector.value = false;
}

async function onToggleLanguageSelector(): Promise<void> {
    showLanguageSelector.value = !showLanguageSelector.value;
    if (showLanguageSelector.value === true) {
        /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
        const languageSelectorRefs = languageSelector.value as any;
        await nextTick(() => {
            languageSelectorRefs.focus();
            changeLanguageSelectorMenuDirection();
        });
    }
}

function onClickOutsideLanguageSelector(): void {
    showLanguageSelector.value = false;
}

function changeLanguageSelectorMenuDirection(): void {
    const headerTop = header.value.getBoundingClientRect().top;
    const userSectionTop = (userSection).value.getBoundingClientRect().top;
    if( userSectionTop > headerTop ){
        (languageSelector.value.$el as HTMLElement).style.insetInlineEnd = 'unset';
        languageSelector.value.$el.style.insetInlineStart = '0';
    } else {
        languageSelector.value.$el.style.insetInlineEnd = '0';
        languageSelector.value.$el.style.insetInlineStart = 'unset';
    }
}

function onWindowResize(): void {
    changeLanguageSelectorMenuDirection();
}

onMounted(() => {
    resizeObserver.value  = new ResizeObserver(onWindowResize);
    resizeObserver.value.observe(contentWrap.value);
});

onBeforeUnmount(() => {
    resizeObserver.value.unobserve(contentWrap.value)
});
</script>

<style lang="scss">
@import "@wikimedia/codex-design-tokens/theme-wikimedia-ui";
@import '../../sass/typography';
@import '../../css/custom-variables.css';

#app {
    box-sizing: border-box;
    min-height: 100%;
    display: flex;
    flex-direction: column;
    
    .content-wrap {
        max-width: 1142px;
        width: 100%;
        flex-grow: 1;
    }

    .mismatch-finder-logo {
        background-image: url('/images/mismatch-finder-logo_mobile.svg');
        width: 268px;
        height: 24px;

        @media (min-width: $min-width-breakpoint-tablet) {
            background-image: url('/images/mismatch-finder-logo.svg');
            width: 384px;
            height: 24px;
        }
    }

    .visually-hidden:not(:focus, :active) {
        clip: rect(0 0 0 0);
        clip-path: inset(100%);
        height: 1px;
        overflow: hidden;
        position: absolute;
        white-space: nowrap;
        width: 1px;
    }

    main>header {
        flex-direction: column;
        gap: 1.5rem;

        .logo-link {
            @media (min-width: $min-width-breakpoint-tablet) {
                width: auto;
            }
        }

        .userSection {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            align-items: center;
        }

        .languageSelector {
            position: relative;
        }
    }

    @media (min-width: $min-width-breakpoint-tablet) {
        main>header {
            flex-flow: row wrap;

            .languageSelector {
                >button {
                    margin-bottom: 2px;
                }
            }
        }
    }

    .footer-title {
        @include body-s;

        font-weight: $font-weight-bold;
    }
}
</style>
