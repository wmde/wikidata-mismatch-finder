import { createLocalVue, mount } from "@vue/test-utils";
import WikidataToolFooter from "@/Components/WikidataToolFooter.vue";

describe("WikidataToolFooter.vue", () => {
    function mockI18n(key, ...params) {
        return key + (params.length ? ` : ${params.join(', ')}` : '');
    }

    function mountWithMocks(props = {}, slot = '') {
        const defaultProps = {
            labels: {},
            urls: {}
        };

        const localVue = createLocalVue();

        localVue.directive('i18n-html', (elem, { arg, value }) => {
            elem.innerHTML = mockI18n(arg, ...value);
        });

        return mount(WikidataToolFooter, {
            props: {
                ...defaultProps,
                ...props
            },
            slots: {
                default: slot
            },
            mocks: {
                $i18n: mockI18n
            },
            localVue
        });
    }

    it("renders a footer", () => {
        const wrapper = mountWithMocks();
        expect(wrapper.find("footer").exists()).toBe(true);
    });

    it("accepts a content class", () => {
        const contentClass = "custom-class";

        const wrapper = mountWithMocks({ contentClass });

        expect(wrapper.props().contentClass).toBe(contentClass);
        expect(wrapper.find("footer").classes()).toContain(contentClass);
    });

    it("accepts a labels prop", () => {
        const labels = {
            tool: "Tool",
            license: "License"
        };

        const wrapper = mountWithMocks({ labels });

        expect(wrapper.props().labels).toBe(labels);

        Object.values(labels).forEach(label => {
            expect(wrapper.html()).toContain(label);
        });
    });

    it("accepts a urls prop", () => {
        const urls = {
            license: "https://license.example.com",
            source: "https://source.example.com",
            issues: "https://issues.example.com",
        };

        const wrapper = mountWithMocks({ urls });

        expect(wrapper.props().urls).toBe(urls);

        Object.values(urls).forEach(url => {
            expect(wrapper.html()).toContain(url);
        });
    });

    it("accepts arbitrary html", () => {
        const html = '<div class="arbitrary">Some arbitrary html</div>';

        const wrapper = mountWithMocks(null, html);

        expect(wrapper.find(".arbitrary").exists()).toBe(true);
    });
})
