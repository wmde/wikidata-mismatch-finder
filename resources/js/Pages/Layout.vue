<template>
    <div class="website">
        <main class="content-wrap">
            <header>
                <InertiaLink href="/">
                    <div class="mismatch-finder-logo" />
                    <h1 class="visually-hidden">{{ $i18n('mismatch-finder-title') }}</h1>
                </InertiaLink>
                <div v-detect-click-outside="onClickOutsideLanguageSelector" class="languageSelector">
                    <LanguageSelectorButton type="neutral" :aria-label="$i18n('toggle-language-selector-button')"
                        @click.native="onToggleLanguageSelector">
                        <template #prefix>
                            <Icon type="language-selector" />
                        </template>
                        {{ currentLanguageAutonym }}
                    </LanguageSelectorButton>
                    <LanguageSelector
                        v-show="showLanguageSelector"
                        ref="languageSelector"
                        @close="onCloseLanguageSelector"
                        @select="onChangeLanguage">
                        <template #no-results>
                            {{ $i18n('language-selector-no-results') }}
                        </template>
                    </LanguageSelector>
                </div>
                <div class="auth-widget">
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
                <h2 class="h5">{{ $i18n('mismatch-finder-footer-more-tools') }}</h2>
                <p>
                    <wikit-link href="https://query.wikidata.org/querybuilder/">
                        {{ $i18n('tool-query-builder') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://item-quality-evaluator.toolforge.org/">
                        {{ $i18n('tool-item-quality-evaluator') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://wikidata-analytics.wmcloud.org/app/CuriousFacts">
                        {{ $i18n('tool-curious-facts') }}
                    </wikit-link>
                </p>
                <p>
                    <wikit-link href="https://github.com/wmde/wikidata-constraints-violation-checker">
                        {{ $i18n('tool-constraints-violation-checker') }}
                    </wikit-link>
                </p>
            </section>
        </wikidata-tool-footer>
    </div>
</template>

<script lang="ts">
import { PropType } from 'vue';
import { Link as InertiaLink } from '@inertiajs/inertia-vue';
import { Link as WikitLink } from '@wmde/wikit-vue-components';
import { Button as LanguageSelectorButton, Icon } from '@wmde/wikit-vue-components';
import AuthWidget from '../Components/AuthWidget.vue';
import LanguageSelector from '../Components/LanguageSelector.vue';
import WikidataToolFooter from '../Components/WikidataToolFooter.vue';
import { DirectiveBinding } from 'vue/types/options';
import defineComponent from '../types/defineComponent';
import User from '../types/User';
import languagedata from '@wikimedia/language-data';

let handleOutsideClick: (event: MouseEvent | TouchEvent) => void;

export default defineComponent({
    components: {
        AuthWidget,
        LanguageSelectorButton,
        Icon,
        InertiaLink,
        LanguageSelector,
        WikidataToolFooter,
        WikitLink
    },
    data() {
        return {
            showLanguageSelector: false,
        };
    },
    directives: {
        detectClickOutside: {
            inserted(element: HTMLElement, binding: DirectiveBinding): void {
                handleOutsideClick = (event: MouseEvent | TouchEvent): void => {
                    const callback = binding.value;
                    if (!element.contains(event.target as Node)) {
                        callback();
                    }
                };

                document.addEventListener('click', handleOutsideClick);
                document.addEventListener('touchstart', handleOutsideClick);
            },
            unbind(): void {
                document.removeEventListener('click', handleOutsideClick);
                document.removeEventListener('touchstart', handleOutsideClick);
            },
        },
    },
    computed: {
        currentLanguageAutonym(): string {
            return languagedata.getAutonym(document.documentElement.lang);
        },
    },
    props: {
        user: Object as PropType<User>,
    },
    methods: {
        onChangeLanguage(newLanguage: string): void {
           document.location.href = '/?uselang=' + newLanguage;
        },
        onCloseLanguageSelector(): void {
            this.showLanguageSelector = false;
        },
        onToggleLanguageSelector(): void {
            this.showLanguageSelector = !this.showLanguageSelector;
            if (this.showLanguageSelector === true) {
                const languageSelectorRefs = this.$refs.languageSelector as any;
                this.$nextTick(() => {
                    languageSelectorRefs.focus();
                });
            }
        },
        onClickOutsideLanguageSelector(): void {
            this.showLanguageSelector = false;
        },
    },
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

$tinyViewportWidth: 38em;

.website {
    .content-wrap {
        max-width: 1168px;
    }

    .mismatch-finder-logo {
        margin-block-end: $dimension-layout-small;
        background-image: url('/images/mismatch-finder-logo_mobile.svg');
        width: 268px;
        height: 24px;

        @media (min-width: $width-breakpoint-tablet) {
            background-image: url('/images/mismatch-finder-logo.svg');
            width: 384px;
            height: 24px;
        }
    }

    .visually-hidden:not(:focus):not(:active) {
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
    }

    @media (min-width: $width-breakpoint-tablet) {
        main>header {
            flex-direction: row;

            .languageSelector {
                >button {
                    margin-bottom: 2px;
                }

                @media (min-width: $tinyViewportWidth) {
                    position: relative;
                }
            }
        }

        .mismatch-finder-logo {
            margin-block-end: 0;
        }
    }
}
</style>
