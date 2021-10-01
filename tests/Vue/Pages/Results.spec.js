import { mount } from '@vue/test-utils';
import Results from '@/Pages/Results.vue';
import MismatchesTable from '@/Components/MismatchesTable.vue';

// Stub the inertia vue components module entirely so that we don't run into
// issues with the Head component.
jest.mock('@inertiajs/inertia-vue', () => ({}));

describe('Results.vue', () => {

    const mocks = {
        $i18n: key => key,
        $page: {
            props: { flash: {} }
        },
    }

    it('accepts and renders item ids', () => {
        const item_ids =  ['Q1', 'Q2']
        const wrapper = mount(Results, {
            propsData: { item_ids },
            mocks
        });

        expect(wrapper.props().item_ids).toBe(item_ids);
        item_ids.forEach(id => expect(wrapper.text()).toContain(id));
    });

    it('accepts and renders results', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                property_label: 'some property',
                wikidata_value: 'Some value',
                value_label: null,
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }],
            'Q987': [{
                id: 654,
                item_id: 'Q987',
                property_id: 'p789',
                property_label: 'some property',
                wikidata_value: 'Some value',
                value_label: null,
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const wrapper = mount(Results, {
            propsData: { results },
            mocks
        });

        const tables = wrapper.findAllComponents(MismatchesTable);

        expect(wrapper.props().results).toBe(results);

        Object.keys(results).forEach((itemId, i) => {
            const section = wrapper.find(`#item-mismatches-${itemId}`);
            const table = tables.at(i);

            expect(section.text()).toContain(itemId);
            expect(table.props().mismatches).toEqual(results[itemId]);
        });
    });

    it('accepts and renders labels', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const labels = {
            'Q321': 'Some Item',
            'P123': 'Some Property',
            'Q1986': 'Some Value'
        }

        const wrapper = mount(Results, {
            propsData: { results, labels },
            mocks
        });

        expect(wrapper.props().labels).toBe(labels);

        Object.values(labels).forEach(label => expect(wrapper.text()).toContain(label));
    });
})
