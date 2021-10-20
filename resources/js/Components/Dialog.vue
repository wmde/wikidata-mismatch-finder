<template>
    <section :class="[
        'wikit',
        'wikit-Dialog'
    ]">
        <header>{{title}}</header>
    </section>
</template>

<script lang="ts">
import defineComponent from '../types/defineComponent';

export default defineComponent({
    props: {
        title: {
            type: String,
            required: true
        }
    }
});
</script>
