export enum ReviewDecision {
    Pending = 'pending',
    Wikidata = 'wikidata',
    Missing = 'missing',
    External = 'external',
    Both = 'both',
    None = 'none'
}

type ReviewStatus = ReviewDecision;

interface Mismatch {
    id: number,
    item_id: string,
    statement_guid: string,
    property_id: string,
    wikidata_value: string,
    meta_wikidata_value: string,
    external_value: string,
    review_status: ReviewStatus,
    import_meta: {
        user: {
            username: string
        },
        created_at: string,
        description: string
    }
}

export interface LabelledMismatch extends Mismatch {
    property_label: string,
    /**
     * A display label for the mismatch’s wikidata_value;
     * this may be the label of the referenced item for an item-type property,
     * or a formatted data value for some other properties.
     * If null, the wikidata_value itself should be used.
     */
    value_label: string|null
}

export default Mismatch;
