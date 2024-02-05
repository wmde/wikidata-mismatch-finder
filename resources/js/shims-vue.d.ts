declare module "*.vue" {
    module 'vue/types/vue' {
        interface Vue {
            $i18n: ( msg: string, ...args: unknown[] ) => string
        }
    }
    import { defineComponent } from "vue";
    const component: ReturnType<typeof defineComponent>;
    export default component;
}

declare module 'vue-banana-i18n';

declare module '@wikimedia/language-data';

declare module '*.svg' {
    const content: any;
    export default content;
}
