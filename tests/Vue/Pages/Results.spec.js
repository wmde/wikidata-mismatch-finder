import { mount } from '@vue/test-utils';
import Results from '@/Pages/Results.vue';
import MismatchesTable from '@/Components/MismatchesTable.vue';

import { ReviewDecision } from '@/types/Mismatch.ts';

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

    it('Updates decisions mismatches on emitted decision events', () => {
        const results = {
            'Q321': [{
                id: 123,
                item_id: 'Q321',
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                review_status: 'pending',
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
        const emitted = {
            id: 123,
            item_id: 'Q321',
            review_status: ReviewDecision.Wikidata
        };

        tables.at(0).vm.$emit('decision', emitted);

        expect(wrapper.vm.decisions['Q321'][123]).toEqual(emitted)
    });

    it('Sends a put request with the selected decisions on click of "Apply changes" button', () => {
        
        const item_id = 'Q321';
        
        const results = {
            [item_id]: [{
                id: 123,
                item_id,
                property_id: 'P123',
                wikidata_value: 'Q1986',
                external_value: 'Another Value',
                review_status: 'pending',
                import_meta: {
                    user: {
                        username: 'some_user_name'
                    },
                    created_at: '2021-09-23'
                },
            }]
        };

        const decisions = { [item_id]:{1:{id:1, item_id ,review_status: ReviewDecision.Wikidata}}};

        const inertiaPut = jest.fn();

        const wrapper = mount(Results, {
            propsData: { results },
            mocks: {
                ... mocks,
                $inertia: { put: inertiaPut },
            },
            data() {
                return {
                    decisions
                }
            },
        });

        const decisionsBeforeDelete = decisions[item_id];
        wrapper.vm.send( item_id );
     
        expect( inertiaPut ).toHaveBeenCalledWith( '/mismatch-review' , decisionsBeforeDelete );
        
        //the decisions object will be empty after sending the put request on one item
        expect(wrapper.vm.decisions).toEqual({});

    });

})
