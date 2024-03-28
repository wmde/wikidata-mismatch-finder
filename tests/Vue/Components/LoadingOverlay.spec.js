import { mount } from '@vue/test-utils';
import LoadingOverlay from '@/Components/LoadingOverlay.vue';

describe('LoadingOverlay.vue', () => {
    it('accepts delay prop', () => {
        const delay = 1000;
        const wrapper = mount(LoadingOverlay, {
            propsData: { delay }
        });

        expect(wrapper.props().delay).toBe(delay);
    });

    it('accepts visible prop', () => {
        const visible = true;
        const wrapper = mount(LoadingOverlay, {
            propsData: { visible }
        });

        expect(wrapper.props().visible).toBe(visible);
        expect(wrapper.find('.loading-indicator').exists()).toBe(true);
    });

    it('renders when calling show method', () => {
        const wrapper = mount(LoadingOverlay);

        wrapper.vm.show();

        return wrapper.vm.$nextTick().then(() => {
            expect(wrapper.find('.loading-indicator').exists()).toBe(true);
        });
    });

    it('hides when calling hide method', () => {
        const wrapper = mount(LoadingOverlay);

        // Hide returns a promise to ensure the animation completes at least after a minimal delay
        return wrapper.vm.hide()
            .then(() => wrapper.vm.$nextTick())
            .then(() => {
                expect(wrapper.find('.loading-indicator').exists()).toBe(false);
            });
    });
});
