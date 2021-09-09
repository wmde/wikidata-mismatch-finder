<template>
    <div>
        <Head title="Mismatch Finder - Results" />
        <h2>Results</h2>
        <p class="results">
            Thank you for sending IDs {{item_ids}}. Mismatch results will be displayed in this page soon.
        </p>
    </div>
</template>

<script lang="ts">
    import { Head } from '@inertiajs/inertia-vue'

    import defineComponent from '../types/defineComponent';

    export default defineComponent({
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
