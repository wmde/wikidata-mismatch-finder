<template>
    <Message :type="type">
        <span>
            <slot />
        </span>
    </Message>
</template>

<script lang="ts">
    import { Message } from '@wmde/wikit-vue-components';

    import defineComponent from '../types/defineComponent';

    export default defineComponent({
        props: {
            type: String
        },
        components: {
            Message
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.wikit.wikit-Message {
    max-width: 705px;
    border-radius: $border-radius-base;
}
</style>
