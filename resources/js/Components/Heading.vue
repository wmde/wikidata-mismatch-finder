<template>
    <h1 v-text="message"></h1>
</template>

<script type="ts">
export default {
    data() {
        return {
            message: 'Mismatch Finder'
        };
    }
};
</script>

<style lang="scss">
@import url('https://fonts.googleapis.com/css2?family=Lato&family=Source+Serif+Pro&display=swap');

h1 {
        font-family: 'Source Serif Pro', serif;
        font-size: 32px;
        line-height: 40px;
        margin: 0;
        padding: 0 0 28px 0;
        font-weight: 400;
    }
</style>
