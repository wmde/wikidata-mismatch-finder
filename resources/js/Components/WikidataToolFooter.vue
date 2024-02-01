<template>
  <div class="footer-container">
    <footer :class="contentClass">
      <section>
        <h2 class="h5">
          {{ $i18n('wikidata-tool-footer-about-tool', labels.tool) }}
        </h2>
        <p v-i18n-html:wikidata-tool-footer-license="[urls.license, labels.license ]" />
        <p><a :href="urls.source">{{ $i18n('wikidata-tool-footer-source') }}</a></p>
        <p><a :href="urls.issues">{{ $i18n('wikidata-tool-footer-issues') }}</a></p>
      </section>
      <section>
        <h2 class="h5">
          {{ $i18n('wikidata-tool-footer-about-us') }}
        </h2>
        <p>
          <a href="https://foundation.wikimedia.org/wiki/Non-wiki_privacy_policy">
            {{ $i18n('wikidata-tool-footer-privacy') }}
          </a>
        </p>
        <p>
          <a href="https://www.wikimedia.de/">
            {{ $i18n('wikidata-tool-footer-wmde') }}
          </a>
        </p>
        <p
          v-i18n-html:wikidata-tool-footer-team="[
            'https://www.wikidata.org/wiki/Wikidata:Contact_the_development_team'
          ]"
        />
      </section>
      <slot />
    </footer>
  </div>
</template>

<script setup lang="ts">
interface FooterLabels {
    tool: string;
    license: string;
}

interface FooterUrls {
    license: string;
    source: string;
    issues: string;
}

withDefaults(defineProps<{
	contentClass: string,
	labels: { type: FooterLabels, required: true },
    urls: { type: FooterUrls, required: true }
}>(), {
	contentClass: ''
});
</script>

<style lang="scss">
@import "@wikimedia/codex-design-tokens/theme-wikimedia-ui";
@import '../../css/custom-variables.css';

.footer-container {
    background-color: $background-color-interactive-subtle;

    & > footer {
        margin: auto;
        padding: var(--dimension-layout-xsmall);
        display: flex;
        flex-direction: column;

        .h5 {
            margin: 0 0 var(--dimension-layout-xsmall) 0;
            font-size: $font-size-small;
        }

        section {
            margin-block-end: 0;
        }

        section + section {
            margin-block-start: var(--dimension-layout-medium);
        }

        p {
            margin-block-end: 0;
            font-size: $font-size-small;
        }

        p + p {
            margin-block-start: var(--dimension-layout-xxsmall);
        }

        @media (min-width: $width-breakpoint-tablet) {
            flex-direction: row;
            padding: var(--dimension-layout-small);

            section {
                margin-inline-end: var(--dimension-layout-large);
            }

            section + section {
                margin-block-start: 0;
            }
        }
    }
}
</style>
