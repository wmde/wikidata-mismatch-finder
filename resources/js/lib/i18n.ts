import axios from 'axios';

export default async function getMessages(lang:string = 'en'): Promise<Object> {
    let messages: {[p: string]: Object} = {
        'en': (await axios.get('/i18n/en.json')).data
    };

    if (lang !== 'en') {
        try {
            messages[lang] = (await axios.get(`/i18n/${lang}.json`)).data;
        } catch (error) {
            if (error.response.status !== 404){
                throw error;
            }

            console.warn( 'The language requested could not be retrieved, falling back to English' );
        }
    }

    return messages;
}
