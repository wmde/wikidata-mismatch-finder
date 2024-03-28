import {mount} from '@vue/test-utils';

import {CdxSelect} from "@wikimedia/codex";

import {ReviewDecision} from '@/types/Mismatch.ts';

import MismatchRow from '@/Components/MismatchRow.vue';
import { createI18n } from 'vue-banana-i18n';

const i18n = createI18n({
    messages: {},
    locale: 'en',
    wikilinks: true
});

describe('MismatchesRow.vue', () => {
    it('accepts a disabled property', () => {
        const disabled = true;
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch, disabled},
            global: {
                plugins: [i18n],
            }
        });

        const dropdown = wrapper.findComponent(CdxSelect);

        expect(wrapper.props().disabled).toBe(disabled);
        expect(dropdown.props().disabled).toBe(disabled);
    });

    it('accepts a mismatch property', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                external_source_url: 'http://www.whatever.com',
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });
        const rowText = wrapper.find('tr').text();

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);

        [
            mismatch.property_label,
            mismatch.wikidata_value,
            mismatch.external_value,
            `review-status-${mismatch.review_status}`,
            mismatch.import_meta.user.username,
            mismatch.import_meta.created_at,
            mismatch.import_meta.external_source
        ].forEach(value => expect(rowText).toContain(value));
    });

    it('displays a link in the External Value column when external_url is provided', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            external_url: 'http://www.external.com',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const td = wrapper.findAll('td').filter(td => td.attributes('data-header') === 'column-external-value')[0];
        const linkUrl = td.find('a').attributes('href');

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(linkUrl).toEqual(mismatch.external_url);
    });

    it('does not display a link in the External Value column when external_url is not provided', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const td = wrapper.findAll('td').filter(td => td.attributes('data-header') === 'column-external-value')[0];

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(td.find('a').exists()).toBe(false);
    });

    it('displays a link in the External Source column with external_source_url is provided', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                external_source_url: 'http://www.whatever.com',
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const td = wrapper.findAll('td').filter(td => td.attributes('data-header') === 'column-external-source')[0];
        const linkUrl = td.find('a').attributes('href');

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(linkUrl).toEqual(mismatch.import_meta.external_source_url);
    });

    it('truncates the upload info description and adds "see full description" link when longer than 100 chars ', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                external_source_url: 'http://www.whatever.com',
                created_at: '2021-09-23',
                description: `Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Suspendisse viverra ut quam eget congue. Nulla accumsan hendrerit eleifend.
                Donec eget tempor metus. Cras sit amet pellentesque eros. Pellentesque mattis
                sed justo ac commodo. Proin auctor lectus congue ligula lacinia dignissim.`
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const desiredTruncateLength = 100;

        const truncateSuffixLength = '...'.length;

        const td = wrapper.findAll('td').filter(td => td.attributes('data-header') === 'column-upload-info')[0];
        const descriptionElementText = td.find('.description').text();

        // workaround pseudo selector :not(.full-description-link) not working
        const descriptionLinkText = td.find('.full-description-button').text();
        const renderedDescriptionText = descriptionElementText.replace(descriptionLinkText, '').trim();

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(renderedDescriptionText.length - truncateSuffixLength).toEqual(desiredTruncateLength);
        expect(descriptionElementText).toContain(descriptionLinkText);
    });

    it('does not truncate upload info description when description shorter than 100 chars ', () => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                external_source_url: 'http://www.whatever.com',
                created_at: '2021-09-23',
                description: 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.' //text length is less than 100
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const td = wrapper.findAll('td').filter(td => td.attributes('data-header') === 'column-upload-info')[0];
        const descriptionElementText = td.find('.description').text();

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(descriptionElementText).toEqual(mismatch.import_meta.description);
    });

    it('Shows dialog after clicking "see full description" in Upload Info column', async () => {

        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                external_source: 'some external source',
                external_source_url: 'http://www.whatever.com',
                created_at: '2021-09-23',
                description: `Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Suspendisse viverra ut quam eget congue. Nulla accumsan hendrerit eleifend.
                Donec eget tempor metus. Cras sit amet pellentesque eros. Pellentesque mattis
                sed justo ac commodo. Proin auctor lectus congue ligula lacinia dignissim.`
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
                stubs: {
                    teleport: true,
                    transition: true
                }
            }
        });

        await wrapper.find('.full-description-button').trigger('click');

        const dialog = wrapper.find('.full-description-dialog.cdx-dialog');

        expect(dialog.isVisible()).toBe(true);
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
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(wrapper.find('tr').text()).toContain(mismatch.value_label);
    });

    it('shows "statement" in type column when value is empty (old database imports)', () => {
        const mismatch = {
            wikidata_value: 'Some value',
            value_label: 'Some label',
            type: '',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const td = wrapper.findAll('td').filter(td => td.attributes('data-header') === 'column-type')[0];
        const statementText = td.find('span').text();

        expect(wrapper.props().mismatch).toStrictEqual(mismatch);
        expect(statementText).toContain('statement');
    });

    test.each(Object.values(ReviewDecision))
    ('shows a dropdown with the %s option', (currentStatus) => {
        const mismatch = {
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: currentStatus,
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                created_at: '2021-09-23'
            },
        };

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
            }
        });

        const dropdown = wrapper.findComponent(CdxSelect);

        expect(dropdown.props().selected).toBe(currentStatus);
    });

    it('bubbles a decision event on dropdown input', () => {
        const mismatch = {
            id: 123,
            item_id: 'Q123',
            property_label: 'Hey hey',
            wikidata_value: 'Some value',
            external_value: 'Another Value',
            review_status: 'pending',
            import_meta: {
                user: {
                    username: 'some_user_name'
                },
                created_at: '2021-09-23'
            },
        };

        const bubbleStub = jest.fn();

        const wrapper = mount(MismatchRow, {
            props: {mismatch},
            global: {
                plugins: [i18n],
                mocks: {
                    $bubble: bubbleStub
                }
            }
        });

        const dropdown = wrapper.findComponent(CdxSelect);

        dropdown.vm.$emit('update:selected', ReviewDecision.Wikidata);

        expect(bubbleStub).toHaveBeenCalledTimes(1);
        expect(bubbleStub).toHaveBeenCalledWith('decision', {
            id: mismatch.id,
            item_id: mismatch.item_id,
            review_status: ReviewDecision.Wikidata
        });
    });
})
