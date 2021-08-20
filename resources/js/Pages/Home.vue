<template>
  <div>
    <Head title="Mismatch Finder" />
    <h1 class="h4">{{ $i18n('about-mismatch-finder-title') }}</h1>
    <p id="about-description" >{{ $i18n('about-mismatch-finder-description') }}</p>
  </div>
</template>

<script>
    import Vue from 'vue';
    import { Head } from '@inertiajs/inertia-vue';

    export default Vue.extend({
        components: {
            Head
        },
        props: {
            user: Object,
        }
    });
</script>

<style lang="scss">
#about-description {
    max-width: 705px;
}
</style>
