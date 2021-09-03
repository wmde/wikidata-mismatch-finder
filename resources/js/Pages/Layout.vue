<template>
  <main>
    <header>
      <img src="../../img/wikidata-logo.svg" alt="Wikidata-logo" /> 
      <div class="top-nav-right">
        <a v-if="user" href="`https://www.wikidata.org/wiki/User:${user}`">
          <img src="../../img/user.svg" class="icon-user" />
          <span class="username">{{ user }}</span>
        </a>
        <a v-else href="/auth/login">Log in</a>
      </div>
    </header>
    <slot />
  </main>
</template>

<script>

export default {

  props: {
      user: Object,
  },
}
</script>