import {mount} from '@vue/test-utils';
import AuthWidget from '@/Components/AuthWidget.vue';
import { createI18n } from 'vue-banana-i18n'

const messages = {
    en: {
        'log-in': 'Log in',
    },
};
const i18nPlugin = createI18n({
    locale: "en",
    messages: messages,
    wikilinks: true,
});

describe('AuthWidget.vue', () => {
    it('displays a the username when provided', () => {
        const username = "TinkyWinky"
        const wrapper = mount(AuthWidget, {
            props: {
                user: {name: username}
            },
            global: {
                plugins: [i18nPlugin]
            }
        });

        expect(wrapper.text()).toContain(username);
    });

    it('displays a "Guest" when username is missing', () => {
        const wrapper = mount(AuthWidget,
            {
                global: {
                    plugins: [i18nPlugin]
                }
            });

        expect(wrapper.text()).toContain("Log in");
    });
});
