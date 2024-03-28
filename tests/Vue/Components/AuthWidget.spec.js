import { mount } from '@vue/test-utils';
import AuthWidget from '@/Components/AuthWidget.vue';
import Vue from 'vue';
import i18n from 'vue-banana-i18n';

const messages = {
	en: {
		'log-in': 'Log in',
	},
};

Vue.use( i18n, {
	locale: 'en',
	messages,
	wikilinks: true,
} );

describe('AuthWidget.vue', () => {
    it('displays a the username when provided', () => {
        const username = "TinkyWinky"
        const wrapper = mount(AuthWidget, {
            propsData: {
                user: { name: username }
            }
        });

        expect(wrapper.text()).toContain(username);
    });

    it('displays a "Guest" when username is missing', () => {
        const wrapper = mount(AuthWidget);

        expect(wrapper.text()).toContain("Log in");
    });
});
