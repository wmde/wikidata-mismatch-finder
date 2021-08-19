import getMessages from '@/lib/i18n';
import axios from 'axios';
import MockAdapter from 'axios-mock-adapter';



describe('i18n.getMessages()', () => {
    it('resolves with "en" messages by default', () => {
        const mockMessages = { "some-translation-key": "Some sentence to translate" };
        const fakeServer = new MockAdapter(axios);

        fakeServer.onGet('/i18n/en.json').reply(200, mockMessages);

        const promise = getMessages();

        return expect(promise).resolves.toEqual({ en: mockMessages });
    });

    it('rejects and throws when "en" messages file is not found', () => {
        const fakeServer = new MockAdapter(axios);

        fakeServer.onGet('/i18n/en.json').reply(404);

        return expect(getMessages()).rejects.toThrow('404');
    });

    it('resolves additionally requested languages', () => {
        const mockEnglishMessages = { "some-translation-key": "Some sentence to translate" };
        const mockHebrewMessages = { "some-translation-key": "משפט כלשהו לתרגום" };
        const fakeServer = new MockAdapter(axios);

        fakeServer.onGet('/i18n/en.json').reply(200, mockEnglishMessages);
        fakeServer.onGet('/i18n/he.json').reply(200, mockHebrewMessages);

        const promise = getMessages('he');

        return expect(promise).resolves.toEqual({
            en: mockEnglishMessages,
            he: mockHebrewMessages
        });
    });

    it('falls back to "en" if additional messages files are not found', () => {
        const mockMessages = { "some-translation-key": "Some sentence to translate" };
        const fakeServer = new MockAdapter(axios);

        fakeServer.onGet('/i18n/en.json').reply(200, mockMessages);
        fakeServer.onGet('/i18n/he.json').reply(404);
        jest.spyOn(console, 'warn').mockImplementation(() => {});

        const promise = getMessages('he');
        return expect(promise).resolves.toEqual({ en: mockMessages });
    });


    it('rejects and throws when other server errors occur', () => {
        const fakeServer = new MockAdapter(axios);

        fakeServer.onGet('/i18n/en.json').reply(200, {});
        fakeServer.onGet('/i18n/he.json').reply(500);

        const promise = getMessages('he');
        return expect(promise).rejects.toThrow('500');
    });
});
