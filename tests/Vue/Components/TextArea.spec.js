import { mount } from '@vue/test-utils';
import TextArea from '@/Components/TextArea.vue';
import { ResizeLimit } from '@/types/ResizeLimit';
import { toEditorSettings } from 'typescript';

describe('TextArea.vue', () => {
    it('accepts rows property', () => {
        const wrapper = mount(TextArea, {
            propsData: { rows: 42 }
        });

        expect(wrapper.props().rows).toBe(42);
        expect(wrapper.find('textarea').attributes('rows')).toBe('42');
    });

    it('accepts resize property', () => {
        const wrapper = mount(TextArea, {
            propsData: { resize: ResizeLimit.Horizontal }
        });

        expect(wrapper.props().resize).toBe(ResizeLimit.Horizontal);
        expect(wrapper.find('textarea').classes()).toContain('wikit-TextArea__textarea--horizontal');
    });

    it('ignores invalid resize values', () => {
        const wrapper = mount(TextArea, {
            propsData: { resize: 'nonsense' }
        });

        expect(wrapper.find('textarea').classes()).not.toContain('wikit-TextArea__textarea--nonsense');
    });

    it('accepts label property', () => {
        const label = 'da Label';
        const wrapper = mount(TextArea, {
            propsData: { label }
        });

        expect(wrapper.props().label).toBe(label);
        expect(wrapper.find('label').text()).toBe(label);
    });

    it('accepts placeholder property', () => {
        const placeholder = 'This is a placeholder';
        const wrapper = mount(TextArea, {
            propsData: { placeholder }
        });

        expect(wrapper.find('textarea').attributes('placeholder')).toBe(placeholder);
    });

    test.todo('should emit a change event with textarea value');
});
