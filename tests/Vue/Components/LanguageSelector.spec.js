import { mount } from '@vue/test-utils';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import { createI18n } from 'vue-banana-i18n';

const i18n = createI18n({
    messages: {},
    locale: 'en',
    wikilinks: true
});

describe('LanguageSelector.vue', () => {
    it('renders', async () => {
        const wrapper = mount(LanguageSelector, {
            global: {
                plugins: [i18n],
        }});

        const input = wrapper.find('input');

        expect(input.exists()).toBe(true);

        input.setValue('ger');
        expect(input.element.value).toBe('ger');
        const listItem = await wrapper.find('.languageSelector__options-menu__languages-list__item');

        console.debug(listItem.html({ raw: true }));
    });
});
