<template>
    <div class="website">
        <main class="content-wrap" ref="contentWrap">
            <header ref="header">
                <InertiaLink class="logo-link" href="/">
                    <div class="mismatch-finder-logo" />
                    <h1 class="visually-hidden">{{ $i18n('mismatch-finder-title') }}</h1>
                </InertiaLink>
                <div class="userSection" ref="userSection">
                    <div v-detect-click-outside="onClickOutsideLanguageSelector" class="languageSelector">
                        <LanguageSelectorButton type="neutral" :aria-label="$i18n('toggle-language-selector-button')"
                            @click.native="onToggleLanguageSelector">
                            <template #prefix>
                                <Icon type="language-selector" />
                            </template>
                            {{ currentLanguageAutonym }}
                        </LanguageSelectorButton>
                        <LanguageSelector v-show="showLanguageSelector" ref="languageSelector"
                            @close="onCloseLanguageSelector" @select="onChangeLanguage">
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
import { Link as InertiaLink } from '@inertiajs/inertia-vue3';
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
            resizeObserver: null as unknown as ResizeObserver,
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
    mounted() {
        this.resizeObserver  = new ResizeObserver(this.onWindowResize);
        this.resizeObserver.observe(this.$refs.contentWrap as Element);
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
            /**
             * Manipulate the url to maintain it as the single source of truth
             * and avoid having either to load all language files upfront or
             * request language file reactively.
             */
            const url = new URL(document.URL);
            url.searchParams.set('uselang', newLanguage);
            document.location.assign(url.toString());
        },
        onCloseLanguageSelector(): void {
            this.showLanguageSelector = false;
        },
        onToggleLanguageSelector(): void {
            this.showLanguageSelector = !this.showLanguageSelector;
            if (this.showLanguageSelector === true) {
                /* eslint-disable-next-line @typescript-eslint/no-explicit-any */
                const languageSelectorRefs = this.$refs.languageSelector as any;
                this.$nextTick(() => {
                    languageSelectorRefs.focus();
                    this.changeLanguageSelectorMenuDirection();
                });
            }
        },
        onClickOutsideLanguageSelector(): void {
            this.showLanguageSelector = false;
        },
        changeLanguageSelectorMenuDirection(): void {
            const headerTop = (this.$refs.header as HTMLElement).getBoundingClientRect().top;
            const userSectionTop = (this.$refs.userSection as HTMLElement).getBoundingClientRect().top;
            if( userSectionTop > headerTop ){
                ((this.$refs.languageSelector as Vue).$el as HTMLElement).style.insetInlineEnd = 'unset';
                ((this.$refs.languageSelector as Vue).$el as HTMLElement).style.insetInlineStart = '0';
            } else {
                ((this.$refs.languageSelector as Vue).$el as HTMLElement).style.insetInlineEnd = '0';
                ((this.$refs.languageSelector as Vue).$el as HTMLElement).style.insetInlineStart = 'unset';
            }
        },
        onWindowResize(): void {
            this.changeLanguageSelectorMenuDirection();
        },
    },
    beforeDestroy () {
        this.resizeObserver.unobserve(this.$refs.contentWrap as Element)
    },
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.website {
    box-sizing: border-box;
    min-height: 100%;
    display: flex;
    flex-direction: column;

    .content-wrap {
        max-width: 1168px;
        width: 100%;
        flex-grow: 1;
    }

    .mismatch-finder-logo {
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
        gap: 1.5rem;

        .logo-link {
            @media (min-width: $width-breakpoint-tablet) {
                width: auto;
            }
        }

        .userSection {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .languageSelector {
            position: relative;
        }
    }

    @media (min-width: $width-breakpoint-tablet) {
        main>header {
            flex-direction: row;
            flex-wrap: wrap;

            .languageSelector {
                >button {
                    margin-bottom: 2px;
                }
            }
        }
    }
}
</style>
