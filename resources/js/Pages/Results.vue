<template>
    <Layout :user="user">
        <div>
            <Head title="Mismatch Finder - Results" />
            <h3>Results</h3>
            <p class="results">
                Thank you for sending IDs {{item_ids}}. Mismatch results will be displayed in this page soon.
            </p>
        </div>
  </Layout>
</template>

<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue'
    import Layout from './Layout';

    export default Vue.extend({
        components: {
            Head,
            Layout
        },
        props: {
            item_ids: Array,
            user: Object
        }
    });
</script>

<style lang="scss">
</style>
