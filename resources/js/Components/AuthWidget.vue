<template>
    <div>
        <div class="top-right-nav" v-if="user">
            <Link :href="`https://www.wikidata.org/wiki/User:${user.name}`">
                <img src="../../img/user.svg" class="icon-user" /><span class="username">{{ user.name }}</span>
            </Link>
            <Link href="/auth/logout">{{ $i18n('log-out') }}</Link>      
        </div>
        <Link v-else href="/auth/login">{{ $i18n('log-in') }}</Link>
    </div>
</template>

<script lang="ts">

import Vue, { PropType } from 'vue';
import User from '../types/User';
import { Link } from '@wmde/wikit-vue-components';

export default Vue.extend({
    props: {
        user: Object as PropType<User>
    },
    components: {
        Link
    }
});
</script>

<style lang="scss">

.top-right-nav {
    display: flex;
}
</style>
