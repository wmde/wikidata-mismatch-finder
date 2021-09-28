export default interface Mismatch {
    id: number,
    property_id: string,
    wikidata_value: string,
    external_value: string,
    import_meta: {
        user: {
            username: string
        },
        created_at: string
    }
}
