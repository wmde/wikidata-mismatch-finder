import { mount } from '@vue/test-utils';
import MismatchRow from '@/Components/MismatchRow.vue';

describe('MismatchesRow.vue', () => {
    it('accepts a mismatch property', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            propsData: { mismatch },
            mocks: {
                // Mock the banana-i18n plugin dependency
                $i18n: key => key
            }
        });

        expect( wrapper.props().mismatch ).toBe( mismatch );

        expect( wrapper.find( 'tr' ).text() ).toContain(
            mismatch.property_label,
            mismatch.wikidata_value,
            mismatch.external_value,
            mismatch.import_meta.user.username,
            mismatch.import_meta.created_at
        );
    });

    it('shows wikidata label over value when available', () => {
        const mismatch = {
            wikidata_value: 'Some value',
            value_label: 'Some label',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            propsData: { mismatch },
            mocks: {
                // Mock the banana-i18n plugin dependency
                $i18n: key => key
            }
        });

        expect( wrapper.props().mismatch ).toBe( mismatch );
        expect( wrapper.find( 'tr' ).text() ).toContain(mismatch.value_label);
    });
})
