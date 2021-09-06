<template>
  <main>
    <header>
      <img src="../../img/wikidata-logo.svg" alt="Wikidata-logo" /> 
      <div class="top-nav-right">
        <AuthWidget :user="user" />
      </div>
    </header>
     <h1>{{ $i18n('mismatch-finder-title') }}</h1>
    <slot />
  </main>
</template>

<script lang="ts">
interface User {
    username: string,
    mw_userid: string
}

import Vue, { PropType } from 'vue';
import AuthWidget from '../Components/AuthWidget.vue';

export default Vue.extend({
  components: { AuthWidget },
    props: {
        user: Object as PropType<User>
    }
});
</script>