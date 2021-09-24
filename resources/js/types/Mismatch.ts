interface Mismatch {
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

export interface LabelledMismatch extends Mismatch {
    property_label: string,
    value_label: string|null
}

export default Mismatch;
