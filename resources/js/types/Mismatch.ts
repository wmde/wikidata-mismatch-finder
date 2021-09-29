export enum ReviewStatus {
    Pending = 'pending',
    Wikidata = 'wikidata',
    External = 'external',
    Both = 'both',
    None = 'none'
}

interface Mismatch {
    id: number,
    item_id: string,
    statement_guid: string,
    property_id: string,
    wikidata_value: string,
    external_value: string,
    review_status: ReviewStatus,
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
