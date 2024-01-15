<template>
  <div>
    <h1 class="h4">{{ title }}</h1>
    <div>{{ description }}</div>
  </div>
</template>

<script setup lang="ts">
defineProps<{title: string, description: string}>();
</script>
