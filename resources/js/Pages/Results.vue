<template>
  <div>
    <Head title="Mismatch Finder - Results" />
    <h1>Mismatch Finder - Results!</h1>
    <p class="results">
        Thank you for sending IDs {{item_ids}}. Mismatch results will be displayed in this page soon.
    </p>
  </div>
</template>

<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue'

    export default Vue.extend({
        components: {
            Head
        },
        props: {
            item_ids: Array
        }
    });
</script>

<style lang="scss">
</style>
