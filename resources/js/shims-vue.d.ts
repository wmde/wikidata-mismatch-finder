declare module "*.vue" {
    import Vue from 'vue'

    module 'vue/types/vue' {
        interface Vue {
            $i18n: ( msg: string, ...args: unknown[] ) => string
        }
    }

    export default Vue
}

declare module 'vue-banana-i18n';

declare module '@wikimedia/language-data';

declare module '*.svg' {
    const content: any;
    export default content;
  }