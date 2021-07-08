# Mismatch Finder User Guide

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Logging in <a id="login"></a>](#logging-in-a-idlogina)
- [Obtaining an API token <a id="apiToken"></a>](#obtaining-an-api-token-a-idapitokena)
- [Accessing the API <a id="apiAccess"></a>](#accessing-the-api-a-idapiaccessa)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Logging in <a id="login"></a>

You can log in to the Mismatch Finder website using your MediaWiki account on `www.wikidata.org`. Simply click the Login button on the Welcome page and get redirected to Wikidata, to allow Mismatch Finder access to your account as a "Connected Application". If you are not logged in already, Wikidata will ask you for the username and passwort of your MediaWiki account.

## Obtaining an API token <a id="apiToken"></a>

Once you have logged in and have started to use the Mismatch Finder web interface, you may want to get access to the REST API, as well. You will need a personalised token for that, which you must provide in the `Authorization:` header of your API requests. Open the "API Token" tab to create a token. Mismatch finder will generate the secret token for you and display it only once after creation, so make sure to write it down in a safe place or copy-paste it into your password management tool.

## Accessing the API <a id="apiAccess"></a>

To access the API, include your personalised API token as `Bearer <token>` string in the `Authorization:` header field.

