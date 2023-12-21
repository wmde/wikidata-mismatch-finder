import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import Home from '@/Pages/Home.vue';
import { createI18n } from 'vue-banana-i18n';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue3', () => ({
    usePage: () => ({
        props: {
          value: {
            flash: {},
            errors: { 'somekey' : 'someError'},
          },
        },
      })
}));

const i18n = createI18n({
    messages: {},
    locale: 'en',
    wikilinks: true
});

describe('Home.vue', () => {

    it('shows dialog after clicking the more info button', async () => {

        const wrapper = mount(Home, {
            attachTo: document.body,
            global:
            {
                plugins: [createTestingPinia(),i18n],
                stubs: {
                    teleport: true,
                    transition: true
                }
            }});
        await wrapper.find('#faq-button').trigger('click');
        const dialog = wrapper.find('#faq-dialog.cdx-dialog');
        expect(dialog.isVisible()).toBe(true);
    });

    it('shows error message upon serverside validation errors', async () => {
        const wrapper = mount(Home, { global: {
                plugins: [createTestingPinia(), i18n]
            }});
        const errorMessage = wrapper.find('#message-section .cdx-message--error');
        expect(errorMessage.isVisible()).toBe(true);
    });
})
