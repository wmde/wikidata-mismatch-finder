<template>
  <main>
    <header>
      <img src="../../img/wikidata-logo.svg" alt="Wikidata-logo" />
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

.auth-widget div {
    display: flex;
}
</style>
