<template>
    <section :class="[
        'wikit',
        'wikit-Dialog'
    ]">
        <header>{{title}}</header>
        <footer>
            <wikit-button v-for="(action, i) in actions"
                :key="i"
                :class="[action.namespace]"
                :variant="i === 0 ? 'primary' : 'normal'"
                :type="i === 0 ? 'progressive' : 'neutral'"
            >
                {{action.label}}
            </wikit-button>
        </footer>
    </section>
</template>

<script lang="ts">
import { PropType } from 'vue';
import defineComponent from '../types/defineComponent';

import { Button as WikitButton } from '@wmde/wikit-vue-components';

interface DialogAction {
    label: string,
    namespace: string
}

export default defineComponent({
    components: {
        WikitButton
    },
    props: {
        title: {
            type: String,
            required: true
        },
        actions: {
            type: Object as PropType<DialogAction[]>,
            required: true
        }
    }
});
</script>

<style lang="scss">

</style>
