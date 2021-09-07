<template>
    <div>
        <div v-if="user">
            <a :href="`https://www.wikidata.org/wiki/User:${user.name}`">
                <img src="../../img/user.svg" class="icon-user" /><span class="username">{{ user.name }}</span>
                </a>
            <a href="/auth/logout">Logout</a>
            
        </div>
        <a v-else href="/auth/login">Log in</a>
    </div>
</template>

<script lang="ts">

import Vue, { PropType } from 'vue';
import User from '../types/User';

export default Vue.extend({
    props: {
        user: Object as PropType<User>
    }
});
</script>

<style lang="scss">

</style>
