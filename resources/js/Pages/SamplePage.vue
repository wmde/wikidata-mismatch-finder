<template>
  <div>
    <Head title="Welcome" />
    <h1>Mismatch Finder</h1>
    <p>Welcome, {{ user.name }}. This page is coming soon!</p>
    <Alert />
  </div>
</template>

<script>
  import { Head } from '@inertiajs/inertia-vue'
  import Heading from '../Components/Heading.vue'

  export default {
    components: {
      Head,
      Heading
    },
    props: {
      user: Object,
    },
  }
</script>