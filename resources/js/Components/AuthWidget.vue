<template>
    <div class="auth-widget" v-if="user">
        <WikitLink :href="`https://www.wikidata.org/wiki/User:${user.name}`">
            <img src="images/user.svg" class="icon-user" /><span class="username">{{ user.name }}</span>
        </WikitLink>
        <WikitLink href="/auth/logout">{{ $i18n('log-out') }}</WikitLink>
    </div>
    <WikitLink v-else href="/auth/login">{{ $i18n('log-in') }}</WikitLink>
</template>

<script lang="ts">

import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import User from '../types/User';
import { Link as WikitLink } from '@wmde/wikit-vue-components';

export default defineComponent({
    props: {
        user: Object as PropType<User>
    },
    components: {
        WikitLink
    }
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.auth-widget {
    display: flex;
    color: $color-base-50;

    .username {
        margin-inline-end: $dimension-spacing-xlarge;
    }

    .icon-user {
        margin-inline-end: $dimension-spacing-small;
        vertical-align: middle;
    }

    .wikit-Link {
        white-space: nowrap;
    }
}

</style>
