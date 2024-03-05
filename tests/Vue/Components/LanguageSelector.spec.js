import { mount } from '@vue/test-utils';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import { createI18n } from 'vue-banana-i18n';

const i18n = createI18n({
    messages: {},
    locale: 'en',
    wikilinks: true
});

describe('LanguageSelector.vue', () => {
    it('suggests the relevant language upon input', async () => {
        const wrapper = mount(LanguageSelector, {
            global: {
                plugins: [i18n],
        }});

        const input = wrapper.find('input');

        expect(input.exists()).toBe(true);

        await input.setValue('deu');
        const listItems = await wrapper.findAll('.languageSelector__options-menu__languages-list__item');

        expect(listItems.at(0).text()).toContain('Deutsch');
    });

    it('suggests the relevant language upon RTL input', async () => {
        const wrapper = mount(LanguageSelector, {
            global: {
                plugins: [i18n],
        }});

        const input = wrapper.find('input');

        expect(input.exists()).toBe(true);

        await input.setValue('עב');
        const listItems = await wrapper.findAll('.languageSelector__options-menu__languages-list__item');

        expect(listItems.at(0).text()).toContain('עברית');
    });

});
