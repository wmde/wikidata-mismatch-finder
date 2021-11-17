<template>
    <div class="website">
        <main class="content-wrap">
            <header>
                <img src="/images/wikidata-logo.svg" class="wikidata-logo" alt="Wikidata-logo" width="160" />
                <div class="auth-widget">
                    <AuthWidget :user="user" />
                </div>
            </header>
            <h1>{{ $i18n('mismatch-finder-title') }}</h1>
            <slot />
        </main>
        <div class="footer-container">
            <footer class="content-wrap">
                <section>
                    <h2 class="h5">About Mismatch Finder</h2>
                    <p>Licensed under <a href="#">BSD 3-Clause License</a></p>
                    <p><a href="#">View Source</a></p>
                    <p><a href="#">Report an issue</a></p>
                </section>
                <section>
                    <h2 class="h5">About us</h2>
                    <p><a href="#">Privacy Policy</a></p>
                    <p><a href="#">Wikimedia Deutschland</a></p>
                    <p><a href="#">Made by the Wikidata team</a></p>
                </section>
                <section>
                    <h2 class="h5">More Data Quality Tools</h2>
                    <p><a href="#">Query Builder</a></p>
                    <p><a href="#">Item Quality Evaluator</a></p>
                    <p><a href="#">Curious Facts</a></p>
                    <p><a href="#">Constraint Violation Checker</a></p>
                </section>
            </footer>
        </div>
    </div>
</template>

<script lang="ts">
    import { PropType } from 'vue';
    import AuthWidget from '../Components/AuthWidget.vue';

    import defineComponent from '../types/defineComponent';
    import User from '../types/User';

    export default defineComponent({
        components: { AuthWidget },
        props: {
            user: Object as PropType<User>
        }
    });
</script>

<style lang="scss">
@import '~@wmde/wikit-tokens/dist/_variables.scss';

.website {
    .content-wrap {
        max-width: 1168px;
    }

    .wikidata-logo {
        margin-block-end: $dimension-layout-small;
    }

    .footer-container {
        background-color: $color-base-90;

        & > footer {
            margin: auto;
            padding: $dimension-layout-xsmall;
            display: flex;

            .h5 {
                margin: 0 0 $dimension-layout-xsmall 0;
            }

            section {
                margin-block-end: $dimension-layout-small;
            }

            p {
                margin-block-end: 0;
            }

            p + p {
                margin-block-start: $dimension-layout-xxsmall;
            }
        }
    }

    main > header,
    .footer-container > footer {
        flex-direction: column;
    }

    @media (min-width: $width-breakpoint-tablet) {
        main > header,
        .footer-container > footer {
            flex-direction: row;
        }

        .wikidata-logo {
            margin-block-end: 0;
        }

        .footer-container > footer {
            padding: $dimension-layout-small;

            section {
                margin-inline-end: $dimension-layout-large;
            }
        }
    }
}
</style>
