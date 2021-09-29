import { mount } from '@vue/test-utils';
import Results from '@/Pages/Results.vue';
import Home from '@/Pages/Home.vue';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue', () => ({}));

describe('Results.vue', () => {
    it('mounts', () => {
        const wrapper = mount(Results, {
            propsData: {}
        });

        expect(true).toBe(true);
    });
})
