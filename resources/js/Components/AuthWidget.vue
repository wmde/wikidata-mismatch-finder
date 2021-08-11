<template>
    <div v-if="user" class="auth">
        <!-- Stuff to show when user is logged in -->
        <p>Hello, {{user.name}}!</p>
    </div>
    <div v-else class="guest">
        <!-- Stuff to Show otherwise -->
        <p>Hello, Guest!</p>
    </div>
</template>

<script lang="ts">
interface User {
    name: string,
    id: string
}

import Vue, { PropType } from 'vue';

export default Vue.extend({
    props: {
        user: Object as PropType<User>
    }
});
</script>

<style lang="scss">

</style>
