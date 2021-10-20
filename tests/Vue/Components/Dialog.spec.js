import { mount } from '@vue/test-utils';
import Dialog from '@/Components/Dialog.vue';

describe('Dialog.vue', () => {
    // Props
    it('accepts and renders title prop', () => {
        const title = 'Hello World!';

        const wrapper = mount(Dialog, {
            propsData: {
                title
            }
        });

        const header = wrapper.find('header');

        expect(wrapper.props().title).toBe(title);
        expect(header.text()).toContain(title);
    });

    it('accepts and renders actions prop', () => {
        const actions = [
            {
                label: 'Primary Test!',
                namespace: 'primary-test'
            },
            {
                label: 'Secondary Test!',
                namespace: 'secondary-test'
            }
        ];

        const wrapper = mount(Dialog, {
            propsData: {
                title: 'Hello World!',
                actions
            }
        });

        const footer = wrapper.find('footer');

        expect(wrapper.props().actions).toBe(actions);

        actions.forEach(({label, namespace}) => {
            const button = footer.find(`.${namespace}`);

            expect(button.text()).toBe(label);
        });
    });

    it('accepts dismissible prop', () => {
        const wrapper = mount(Dialog, {
            propsData: {
                title: 'Hello World!',
                actions: [
                    {
                        label: 'Primary Test!',
                        namespace: 'primary-test'
                    }
                ],
                dismissible: true
            }
        });

        const closeButton = wrapper.find('.wikit-Dialog__close');

        expect(wrapper.props().dismissible).toBe(true);
        expect(closeButton.exists()).toBe(true);
    });

    test.todo('accepts open prop');

    // Slots
    test.todo('renders content slot');

    // Events
    test.todo('emits closed event');
    test.todo('emits action event');

    // Methods
    test.todo('exposes show method');

    // Bindings
    test.todo('bind visibility state to v-model');

    // Future iterations
    test.todo('exposes hide method');
    test.todo('exposes toggle method');
    test.todo('accepts loading prop');
    test.todo('accepts responsive breakpoint prop');
});
