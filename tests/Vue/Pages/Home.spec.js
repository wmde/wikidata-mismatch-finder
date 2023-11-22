import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import Home, { MAX_NUM_IDS } from '@/Pages/Home.vue';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue3', () => ({}));

describe('Home.vue', () => {

    const mocks = {
        $i18n: key => key,
        $page: {
            props: { flash: {} }
        },
    }


    it('sanitises input with empty lines', async () => {

        const itemsInput = 'Q1\n\nQ2\n';

        const wrapper = mount(Home, {
            global: {
                mocks,
                plugins: [createTestingPinia({
                    initialState: {
                        storeId: {
                            lastSearchedIds: itemsInput
                        }
                    }
                })],
            }
        });

        expect( wrapper.vm.serializeInput() ).toEqual('Q1|Q2');
    });

    it('restores lastSearchIds value from store on page load', async () => {

        const itemsInput = 'Q1\n\nQ2\n';

        const wrapper = mount(Home, {
            global: {
                mocks,
                plugins: [createTestingPinia({
                    initialState: {
                        storeId: {
                            lastSearchedIds: itemsInput
                        }
                    }
                })]
            }
        });

        expect( wrapper.vm.textareaInputValue).toEqual(itemsInput);
    });

    it('shows dialog after clicking the more info button', async () => {

        const wrapper = mount(Home, {
            attachTo: document.body,
            global:
            {
                mocks,
                plugins: [createTestingPinia()],
                stubs: {
                    teleport: true,
                    transition: true
                }
            }});
        await wrapper.find('#faq-button').trigger('click');
        const dialog = wrapper.find('#faq-dialog.cdx-dialog');
        expect(dialog.isVisible()).toBe(true);
    });

    it('validates that textarea input is not empty', async () => {
        const itemsInput = '';

        const wrapper = mount(Home, {
            global: {
                mocks,
                plugins: [createTestingPinia()]
            },
            data() {
                return {
                    form: {
                        itemsInput
                    }
                 }
            }
        });

        wrapper.vm.validate();

        expect(wrapper.vm.validationError).toStrictEqual({
            type: 'error',
            message: { error: 'item-form-error-message-empty' }}
        );
    });

    it('validates that items in textarea input dont exceed the maximum number of ids', async () => {
        const itemsInput = Array(MAX_NUM_IDS + 2).fill('Q21').join('\n');

        const wrapper = mount(Home, {
            global: {
                mocks,
                plugins: [createTestingPinia({
                    initialState: {
                        storeId: {
                            lastSearchedIds: itemsInput
                        }
                    }
                })],
            }
        });

        wrapper.vm.validate();
        expect(wrapper.vm.validationError).toStrictEqual({
            type: 'error',
            message: { error: 'item-form-error-message-max'}}
        );
    });

    it('validates that items in textarea input are well-formed', async () => {
        const itemsInput = 'L12345';

        const wrapper = mount(Home, {
            global: {
                mocks,
                plugins: [createTestingPinia({
                    initialState: {
                        storeId: {
                            lastSearchedIds: itemsInput
                        }
                    }
                })],
            }
        });

        wrapper.vm.validate();
        expect(wrapper.vm.validationError).toStrictEqual({
            type: 'error',
            message: { error: 'item-form-error-message-invalid' } }
        );
    });

    it('shows error message upon serverside validation errors', async () => {
        mocks.$page.props.errors = { 'someKey' : 'someError'}
        const wrapper = mount(Home, { global: {
                mocks,
                plugins: [createTestingPinia()]
            }});
        const errorMessage = wrapper.find('#message-section .cdx-message--error');
        expect(errorMessage.isVisible()).toBe(true);
    });
})
