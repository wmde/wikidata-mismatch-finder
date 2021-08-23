<template>
  <div>
    <H1>{{ title }}</H1>
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
