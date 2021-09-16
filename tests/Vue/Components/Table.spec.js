import { mount } from '@vue/test-utils';
import Table from '@/Components/Table.vue';
import {Breakpoint} from '@/types/Breakpoint.ts';

describe('Table.vue', () => {
    it('accepts a linearize property', () => {
        const wrapper = mount(Table, {
            propsData: {
                linearize: Breakpoint.Desktop
            }
        });

        expect( wrapper.props().linearize ).toBe( Breakpoint.Desktop );
		expect( wrapper.find( 'table' ).classes() ).toContain( 'wikit-Table--linear-desktop' );
    });

    it('ignores invalid breakpoint values', () => {
        const wrapper = mount(Table, {
            propsData: { linearize: 'nonsense' }
        });

        expect(wrapper.find('table').classes()).toContain('wikit-Table--linear-tablet');
    });
})
