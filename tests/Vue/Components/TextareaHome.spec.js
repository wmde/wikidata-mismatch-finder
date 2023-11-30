import { mount } from '@vue/test-utils';
import { createTestingPinia } from '@pinia/testing';
import { MAX_NUM_IDS } from '@/Pages/Home.vue';
import TextareaHome from '@/Components/TextareaHome.vue';

describe('TextareaHome.vue', () => {

    const mocks = {
        $i18n: key => key,
        $page: {
            props: { flash: {} }
        },
    }

    it('sanitises input with empty lines', async () => {

        const itemsInput = 'Q1\n\nQ2\n';

        const wrapper = mount(TextareaHome, {
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

        const wrapper = mount(TextareaHome, {
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

    it('validates that textarea input is not empty', async () => {
        const itemsInput = '';

        const wrapper = mount(TextareaHome, {
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

        const wrapper = mount(TextareaHome, {
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

        const wrapper = mount(TextareaHome, {
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

})
