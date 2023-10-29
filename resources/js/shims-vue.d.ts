declare module "*.vue" {
    import Vue from 'vue'

    module 'vue/types/vue' {
        interface Vue {
            $i18n: ( msg: string, ...args: unknown[] ) => string
        }
    }

    export default Vue
}

declare module 'vue' {
    import { CompatVue } from '@vue/runtime-dom'
    const Vue: CompatVue
    export default Vue
    export * from '@vue/runtime-dom'
    const { configureCompat } = Vue
    export { configureCompat }
}

declare module 'vue-banana-i18n';

declare module '@wikimedia/language-data';

declare module '*.svg' {
    const content: any;
    export default content;
}