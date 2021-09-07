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

import Vue, { PropType } from 'vue';
import AuthWidget from '../Components/AuthWidget.vue';
import User from '../types/User';

export default Vue.extend({
  components: { AuthWidget },
    props: {
        user: Object as PropType<User>
    }
});
</script>