import { mount } from '@vue/test-utils';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import { createI18n } from 'vue-banana-i18n';

const i18n = createI18n({
    messages: {},
    locale: 'en',
    wikilinks: true
});

describe('LanguageSelector.vue', () => {
    it('renders', () => {
        const wrapper = mount(LanguageSelector, {
            global: {
                plugins: [i18n],
        }});

        expect(wrapper.find('.mismatchfinder__language-selector').exists()).toBe(true);
    });
});
