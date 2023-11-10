import axios from 'axios';

export interface I18nMessages {
    [lang: string]: {
        [key: string]: string
    }
}

export default async function getMessages(lang:string = 'en'): Promise<I18nMessages> {
    let messages: I18nMessages = {
        'en': (await axios.get('/i18n/en.json')).data
    };

    if (lang !== 'en') {
        try {
            messages[lang] = (await axios.get(`/i18n/${lang}.json`)).data;
        } catch (error: any) {
            if (error.response.status !== 404){
                throw error;
            }

            console.warn( 'The language requested could not be retrieved, falling back to English' );
        }
    }

    return messages;
}
