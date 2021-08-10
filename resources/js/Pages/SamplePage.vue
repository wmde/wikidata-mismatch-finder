<template>
  <div>
    <Head title="Welcome" />
    <H1>Welcome</H1>
    <p>Hello {{ user.name }}, welcome to your first Inertia app!</p>
    <Alert />
  </div>
</template>

<script>
  import { Head } from '@inertiajs/inertia-vue'
  import Alert from '../Components/Alert.vue'

  export default {
    components: {
      Head,
      Alert
    },
    props: {
      user: Object,
    },
  }
</script>