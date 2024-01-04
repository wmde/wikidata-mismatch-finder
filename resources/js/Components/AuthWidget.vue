<template>
    <div class="auth-widget_wrapper"> 
        <div class="auth-widget" v-if="user">
            <a :href="`https://www.wikidata.org/wiki/User:${user.name}`">
                <img src="images/user.svg" class="icon-user" /><span class="username">{{ user.name }}</span>
            </a>
            <a  href="/auth/logout">{{ $i18n('log-out') }}</a>
        </div>
        <a class="auth-widget" v-else href="/auth/login">{{ $i18n('log-in') }}</a>
    </div>

</template>

<script lang="ts">

import type { PropType } from 'vue';
import { defineComponent } from 'vue';
import User from '../types/User';

export default defineComponent({
    props: {
        user: Object as PropType<User>
    }
});
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';
@import '~@wikimedia/codex-design-tokens/theme-wikimedia-ui.scss';

.auth-widget {
    display: flex;
    color: $color-base-50;
    justify-content: space-between;
    gap: $dimension-spacing-xlarge;
    align-items: center;

    .icon-user {
        margin-inline-end: $dimension-spacing-small;
        vertical-align: middle;
    }

&_wrapper {
        a {
            white-space: nowrap;
            &:visited {
                color: $color-progressive;
            }
        }
    }
}

</style>
