# Mismatch Finder User Guide

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Logging in](#login)
- [Accessing the API](#apiAccess)
- [Obtaining an API access token](#apiToken)
- [Importing mismatches](#importing)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Logging in <a id="login"></a>

You can log in to the Mismatch Finder website using your MediaWiki account on `www.wikidata.org`. Simply click the Login button on the Welcome page and get redirected to Wikidata, to allow Mismatch Finder access to your account as a "Connected Application". If you are not logged in already, Wikidata will ask you for the username and password of your MediaWiki account.

## Accessing the API <a id="apiAccess"></a>

Once you have logged in and have started to use the Mismatch Finder web interface, you may want to access the REST API as well. In order to perform some actions with the REST api, such as uploading files, you will need a [personal access token](#apiToken). 

For each request that involves authorization, your personal token must be provided in the `Authorization` header of your request as such:

```
Authorization: Bearer <your-access-token>
```

**Note:** Don't forget to replace `<your-access-token>` in the example above with your actual personal access token.

## Obtaining an API access token <a id="apiToken"></a>

To obtain a personal access token, follow these steps:

1. At the [application homepage](https://mismatch-finder.toolforge.org/), click the API token link, or go to: https://mismatch-finder.toolforge.org/auth/api-settings
2. If you do no have an access token already, you will be prompted to create a new token.
3. **Important!** Write the created token down in a safe place, as it will not be displayed again.
4. Once you have noted down the token you will be redirected back to the token management page.

In any case you want to revoke an existing token, simply click the revoke link in the token management page. After the token is revoked, you will be able to create a fresh token by repeating the steps above.

## Importing mismatches <a id="importing"></a>

Users which have sufficient access rights may perform batch imports into the Mismatch Finder by uploading a CSV file to describe their found mismatches.

### Creating a mismatches import file

A CSV import file must include the following header row, to describe each column:

```csv
statement_guid,property_id,wikidata_value,external_value,external_url
```

* `statement_guid` - Represents that unique id of the statement on wikidata that contains the mismatching data.
* `property_id` - The id of the wikidata property defining the wikidata value of the mismatch.
* `wikidata_value` - The value on wikidata that mismatches an external database.
* `external_value` - The value in the external database that mismatches a wikidata value.
* `external_url` - _(Optional)_ A url or uri to the mismatching entity in the external database.

_**Note**: The data `wikidata_value`, `external_value`, `external_url` should be limited to a length of 1500 characters maximum._

Additionally, each row of the csv file must contain exactly 4 commas (`,`). Optional values can simply be left out.

You can consult this [mismatch file](exampleMismatchFile.csv) for a valid example.

### Uploading an import file

<!-- TODO: Replace this description with a link to our API Specification, once deployed (See T287948). -->

To upload an import file, users may send a request to our `POST /api/imports` api endpoint. 

The request should include the `Authorization` header with a personal [access token](#apiAccess). Additionally, the request should include a `Content-Type: multipart/form-data` header.

The request body should include the following fields:

* `mismatchFile` - The CSV file containing mismatches to import to Mismatch Finder.
* `description` - _(Optional)_ A short text (up to 350 characters) to describe this import.
* `expires` - _(Optional)_ An ISO formatted date to describe the date where the mismatches imported will be no longer relevant. If omitted, mismatches from the import will expire after 6 months by default.

Once an import is submitted, the newly created import status will be included in the response, alongside an api link to check its status again. Additionally, the status of the last 10 imports in to Mismatch Finder can be checked at our [import status page](https://mismatch-finder.toolforge.org/imports).
