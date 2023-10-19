# Mismatch Finder User Guide

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- param::maxHeaderLevel::3:: -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Logging in](#logging-in)
- [Accessing the API](#accessing-the-api)
- [Obtaining an API access token](#obtaining-an-api-access-token)
- [Importing mismatches](#importing-mismatches)
  - [Creating a mismatches import file](#creating-a-mismatches-import-file)
  - [Getting upload rights](#getting-upload-rights)
  - [Uploading an import file](#uploading-an-import-file)
    - [Headers](#headers)
    - [Body](#body)
    - [Example with curl](#example-with-curl)
    - [Response](#response)
  - [Reviewing Mismatches](#reviewing-mismatches)
    - [Review](#review)
    - [Submit decisions](#submit-decisions)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Logging in

You can log in to the Mismatch Finder website using your MediaWiki account on `www.wikidata.org`. Simply click the Login button on the Welcome page and get redirected to Wikidata, to allow Mismatch Finder access to your account as a "Connected Application". If you are not logged in already, Wikidata will ask you for the username and password of your MediaWiki account.

## Accessing the API

Once you have logged in and have started to use the Mismatch Finder web interface, you may want to access the REST API as well. The available operations you can perform with the REST API can be checked in the [Wikidata Mismatch Finder OpenApi specification](https://mismatch-finder.toolforge.org/api-docs/index.html).

In order to perform some actions with the REST api, such as uploading files, you will need a [personal access token](#obtaining-an-api-access-token).

For each request that involves authorization, your personal token must be provided in the `Authorization` header of your request as such:

```
Authorization: Bearer <your-access-token>
```

**Note:** Don't forget to replace `<your-access-token>` in the example above with your actual personal access token.

## Obtaining an API access token

To obtain a personal access token, follow these steps:

1. Go to: https://mismatch-finder.toolforge.org/store/api-settings
2. If you do no have an access token already, you will be prompted to create a new token.
3. **Important!** Write the created token down in a safe place, as it will not be displayed again.
4. Once you have noted down the token you will be redirected back to the token management page.

In any case you want to revoke an existing token, simply click the revoke link in the token management page. After the token is revoked, you will be able to create a fresh token by repeating the steps above.

## Importing mismatches

Users which have sufficient access rights may perform batch imports into the Mismatch Finder by uploading a CSV file to describe their found mismatches.

### Creating a mismatches import file

A CSV import file must include the following header row, to describe each column:

```csv
item_id,statement_guid,property_id,wikidata_value,meta_wikidata_value,external_value,external_url,type
```

* `item_id` - The item ID of the Wikidata item containing the mismatching statement.
* `statement_guid` - _(Optional)_ Represents that unique id of the statement on wikidata that contains the mismatching data.
  If present, must be consistent with the `item_id`.
  Can be empty to signify that no matching value was found on Wikidata, in which case the `wikidata_value` must also be empty.
* `property_id` - The id of the wikidata property defining the wikidata value of the mismatch.
* `wikidata_value` - _(Optional)_ The value on wikidata that mismatches an external database. 
  Can be empty (see `statement_guid`).
* `meta_wikidata_value` - _(Optional)_ The value on wikidata that represents property calendar/time type.
* `external_value` - The value in the external database that mismatches a wikidata value.
* `external_url` - _(Optional)_ A url or uri to the mismatching entity in the external database.
* `type` - _(Optional)_ A string that contains either the value 'statement' or the value 'qualifier' to indicate where the mismatch occurs. If left empty a value of 'statement' will be assumed.

_**Note**: The data `wikidata_value`, `external_value`, `external_url` should be limited to a length of 1500 characters maximum._

All columns must be present; optional values can be left empty, e.g. `,,` for empty `meta_wikidata_value`.

You can consult this [mismatch file](exampleMismatchFile.csv) for a valid example.

**Note**: If possible, please do not include mismatches in your import file that have already been uploaded and reviewed before to avoid double-work by reviewers.

### Getting upload rights

To be able to upload new mismatches your Wikidata account needs to be added to the allow-list. To be added please get in touch with [Lydia Pintscher](https://www.wikidata.org/wiki/User:Lydia_Pintscher_(WMDE)). Alternatively you can [open a ticket](https://phabricator.wikimedia.org/project/view/5385/) with your prepared mismatch file and the Wikidata team will upload your mismatches for you.

### Uploading an import file

To upload an import file, users may send a request to our `POST /api/imports` API endpoint.

#### Headers

The request should include the following headers:
* `Authorization: Bearer ACCESS_TOKEN`, see [access token](#accessing-the-api)
* `Accept: application/json`
* `Content-Type: multipart/form-data`

#### Body

The request body should include the following fields:
* `mismatch_file` - The CSV file containing mismatches to import to Mismatch Finder.
* `description` - _(Optional)_ A short text (up to 350 characters) to describe this import.
* `external_source` - The name of the external source that mismatches are coming from (up to 100 characters).
* `external_source_url` - _(Optional)_ A URL to the external source that mismatches are coming from.
* `expires` - _(Optional)_ An ISO formatted date to describe the date where the mismatches imported will be no longer relevant. If omitted, mismatches from the import will expire after 6 months by default. A timeframe of a few weeks or months is recommended.

#### Example with curl

```
curl -X POST "https://mismatch-finder.toolforge.org/api/imports" \
-H "Accept: application/json" \
-H "Authorization: Bearer ACCESS_TOKEN" \
-F "mismatch_file=@PATH_TO_CSV_FILE" \
-F "description=DESCRIPTION" \
-F "external_source=SOURCE" \
-F "external_source_url=URL" \
-F "expires=YYYY-MM-DD"
```

#### Response

Once an import is submitted, the newly created import status will be included in the response, alongside an api link to check its status again. Additionally, the status of the last 10 imports in to Mismatch Finder can be checked at our [import status page](https://mismatch-finder.toolforge.org/store/imports).

For more information take a look at our [REST API documentation](https://mismatch-finder.toolforge.org/api-docs/index.html#/store/post_imports).

### Reviewing Mismatches


#### Review

To review mismatches by Item ID, users may send a request to our `GET /api/mismatches` api endpoint.

The request should include the required `ids` query parameter to specify Item IDs. For example, the following request will retrieve all mismatches for Items `Q1` and `Q2`:

```
GET /api/mismatches?ids=Q1|Q2
```

For more information take a look at our [REST API documentation](https://mismatch-finder.toolforge.org/api-docs/index.html#/store/get_mismatches).

#### Submit decisions

To submit a decision regarding a particular mismatch, users may send a request to `PUT /api/mismatches/{mismatchId}`.

The decision regarding a mismatch should be sent using the mismatch's id. For example, to submit a decision regarding mismatch `42` we will send the following request:

```
PUT /api/mismatches/42
```

The request body should include the decision itself as a json object. That is, to decide that the mismatch in data is on the external source, the user would send:

```json
{
    "review_status": "external"
}
```

_**Note**: any other fields related to a mismatch and included in the request will be prohibited and result in a validation error._

The only possible values for a review status are:

- `"pending"` - The mismatch is awaiting a review decision.
- `"wikidata"` - The mismatching information is on Wikidata.
- `"missing"` - The information is missing on Wikidata and correct on the external source.
- `"external"` - The mismatching information is on the external source.
- `"both"` - Both sources are incorrect.
- `"none"` - None of the above.

For more information take a look at our [REST API documentation](https://mismatch-finder.toolforge.org/api-docs/index.html#/store/put_mismatches__mismatchId_).

