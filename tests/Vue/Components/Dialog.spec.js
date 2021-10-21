import { mount } from '@vue/test-utils';
import Dialog from '@/Components/Dialog.vue';

describe('Dialog.vue', () => {
    it('doesn\'t show dialog by default', () => {
        const wrapper = mount(Dialog);

        const container = wrapper.find('.wikit-Dialog');

        expect(wrapper.props().open).toBe(false);
        expect(container.isVisible()).toBe(false);
    });

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
            propsData: { actions }
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
            propsData: { dismissible: true }
        });

        const closeButton = wrapper.find('.wikit-Dialog__close');

        expect(wrapper.props().dismissible).toBe(true);
        expect(closeButton.exists()).toBe(true);
    });

    it('accepts open prop and shows dialog', () => {
        const wrapper = mount(Dialog, {
            propsData: { open: true }
        });

        const container = wrapper.find('.wikit-Dialog');

        expect(wrapper.props().open).toBe(true);
        expect(container.isVisible()).toBe(true);
    });

    // Slots
    it('renders content slot', () => {
        const content = '<p>Hello World</p>';
        const wrapper = mount(Dialog, {
            slots: {
                default: content
            }
        });

        const slot = wrapper.find('.wikit-Dialog__content');

        expect(slot.element.innerHTML).toBe(content);
    });

    // Events
    test.todo('emits closed event');
    test.todo('emits action event');

    // Methods
    test.todo('exposes show method');

    // Bindings
    test.todo('binds visibility state to v-model');

    // Behaviour
    test.todo('closes when pressing the close button');

    // Future iterations
    test.todo('exposes hide method');
    test.todo('exposes toggle method');
    test.todo('accepts loading prop');
    test.todo('accepts responsive breakpoint prop');
});
