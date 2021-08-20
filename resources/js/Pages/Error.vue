<template>
  <div>
    <h1 class="h4">{{ title }}</h1>
    <div>{{ description }}</div>
  </div>
</template>

<script>
import Vue from 'vue';

export default Vue.extend({
  props: {
    title: String,
    description: String
  }
});
</script>
