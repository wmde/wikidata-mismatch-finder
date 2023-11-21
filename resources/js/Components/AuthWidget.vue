<template>
    <div class="auth-widget" v-if="user">
        <a class="cdx-docs-link" :href="`https://www.wikidata.org/wiki/User:${user.name}`">
            <img src="images/user.svg" class="icon-user" /><span class="username">{{ user.name }}</span>
        </a>
        <a class="cdx-docs-link"  href="/auth/logout">{{ $i18n('log-out') }}</a>
    </div>
    <a class="cdx-docs-link" v-else href="/auth/login">{{ $i18n('log-in') }}</a>
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
@import '../../sass/link.scss';

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

    .cdx-docs-link {
        @include cdx-mixin-link;
        white-space: nowrap;
    }
}

</style>
