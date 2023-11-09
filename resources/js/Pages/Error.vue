<template>
  <div>
    <h1 class="h4">{{ title }}</h1>
    <div>{{ description }}</div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  props: {
    title: String,
    description: String
  }
});
</script>
