<template>
  <main class="website">
    <header>
      <img src="/images/wikidata-logo.svg" class="wikidata-logo" alt="Wikidata-logo" width="160" />
      <div class="auth-widget">
        <AuthWidget :user="user" />
      </div>
    </header>
    <h1>{{ $i18n('mismatch-finder-title') }}</h1>
    <slot />
  </main>
</template>

<script lang="ts">
    import { PropType } from 'vue';
    import AuthWidget from '../Components/AuthWidget.vue';

    import defineComponent from '../types/defineComponent';
    import User from '../types/User';

    export default defineComponent({
        components: { AuthWidget },
        props: {
            user: Object as PropType<User>
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.website {
    max-width: 1168px;
}

@media (max-width: $width-breakpoint-tablet) {
    .website > header {
        flex-direction: column;
    }

    .wikidata-logo {
        margin-bottom: $dimension-layout-small;
    }
}
</style>
