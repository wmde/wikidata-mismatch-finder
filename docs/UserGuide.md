# Mismatch Finder User Guide

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->

- [Logging in](#login)
- [Accessing the API](#apiAccess)
- [Obtaining an API token](#apiToken)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Logging in <a id="login"></a>

You can log in to the Mismatch Finder website using your MediaWiki account on `www.wikidata.org`. Simply click the Login button on the Welcome page and get redirected to Wikidata, to allow Mismatch Finder access to your account as a "Connected Application". If you are not logged in already, Wikidata will ask you for the username and passwort of your MediaWiki account.

## Accessing the API <a id="apiAccess"></a>

Once you have logged in and have started to use the Mismatch Finder web interface, you may want to access the REST API as well. In order to perform some actions with the REST api, such as uploading files, you will need a [personal access token](#apiToken). 

For each request that involves authorization, your personal token must be provided in the `Authorization` header of your request as such:

```
Authorization: Bearer <your-access-token>
```

**Note:** Don't forget to replace `<your-access-token>` in the example above with your actual personal access token.

## Obtaining an API access token <a id="apiToken"></a>

To obtain a personal access token, follow these steps:

1. At the [application homepage](https://mismatch-finder.toolforge.org/), click the API token link, or go to: https://mismatch-finder.toolforge.org/auth/token
2. If you do no have an access token already, you will be prompted to create a new token.
3. **Important!** Write the created token down in a safe place, as it will not be displayed again.
4. Once you have noted down the token you will be redirected back to the token management page.

In any case you want to revoke an existing token, simply click the revoke link in the token management page. After the token is revoked, you will be able to create a fresh token by repeating the steps above.


