# Developer Documentation

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**  *generated with [DocToc](https://github.com/thlorenz/doctoc)*

- [Quickstart](#quickstart)
- [Day to day](#day-to-day)
    - [Start the application server](#start-the-application-server)
    - [Stop the application server](#stop-the-application-server)
    - [Destroy all the things](#destroy-all-the-things)
- [Working with OAuth](#working-with-oauth)
- [Troubleshooting](#troubleshooting)
    - [Address already in use](#address-already-in-use)
- [Sources](#sources)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## Quickstart

1. Clone the repository

    ```bash
    git clone git@github.com:wmde/wikidata-mismatch-finder.git
    cd wikidata-mismatch-finder
    ```

1. Install dependencies with composer (requires composer 2+)

    ```bash
    composer install
    ```

    **OR** use docker to install dependencies

    ```bash
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v $(pwd):/opt \
        -w /opt \
        laravelsail/php80-composer:latest \
        composer install --ignore-platform-reqs
    ```

1. Copy default environment variables

    ```bash
    cp .env.example .env
    ```

1. Start the application server with Laravel sail

    ```bash
    ./vendor/bin/sail up
    ```

    **HOT TIP!** Add the following alias to your `.bashrc` or `.profile` for easier command execution

    ```bash
    alias sail='bash vendor/bin/sail'
    ```

1. Generate a unique app key with artisan

    ```bash
    sail artisan key:generate
    ```

## Day to day

### Start the application server

```bash
sail up
```

**OR** if you don't want to see live logging, start it in detached mode

```bash
sail up -d
```

### Stop the application server

```bash
sail down
```

### Destroy all the things

**DANGER!** Not only does this remove all downloaded images - which will result in some setup time when running `sail up` again, this will also **delete** all the data volumes for your application, removing all data from the database.

```bash
sail down --rmi all -v
```

## Working with OAuth

In production, this application relies on wikidata.org's OAuth capabilities in order to authorize and identify users. Since it is not ideal to test in the production environment, we recommend creating your own personal OAuth consumer credentials for testing purposes, in order to develop locally.

1. Register your local testing app against wikidata in the following link:

    [OAuth Consumer Registration](https://meta.wikimedia.org/wiki/Special:OAuthConsumerRegistration/propose)

1. Fill in the following fields in the form as follows (see [screenshot](img/Screenshot_2021-06-28_at_18-06-22_OAuth_consumer_registration_-_Meta.png) for example):
    - **Application name:** Make sure to include ***test*** in the name of the application, so that it is apparent for reviewers in the Wikimedia Foundation (WMF) that your consumer is meant for testing purposes.
    - **OAuth protocol version:** Leave this field as is. This application uses OAuth1.0a.
    - **Application description:** Fill in a description for your application, to make review easier.
    - **OAuth "callback" URL:** Make sure to fill this in with a ***localhost*** address, with the port you would be running this application on (if not using the default port). This should be identical to the `APP_URL` in your `.env` file.
    - **Applicable project:** This field should be set to `wikidatawiki`.
    - **Types of grants being requested:** Make sure to check the first radio button - "User identity verification only, no ability to read pages or act on a user's behalf". This will speed up the review process.

    The rest of the fields can stay on their default values. Don't forget to check the disclaimer before submitting the form.

1. Once the form is submitted, make sure to write down your consumer key and consumer secret in a safe place and fill in the details in your local `.env` file:

    ```bash
    #...

    MEDIAWIKI_OAUTH_CLIENT_ID=<your-consumer-key>
    MEDIAWIKI_OAUTH_CLIENT_SECRET=<your-consumer-secret>
    MEDIAWIKI_OAUTH_CALLBACK_URL="${APP_URL}/auth/callback"
    MEDIAWIKI_OAUTH_BASE_URL=https://www.wikidata.org

    #...
    ```

1. As soon as you receive the email from the WMF team that your consumer is approved, you may start testing your application by logging in through your local instance's home page.

## Troubleshooting

### Address already in use

#### MariaDB

**Problem:** Sail refuses to start with the following error.

```
ERROR: for wikidata-mismatch-finder_mariadb_1  Cannot start service mariadb: driver failed programming external connectivity on endpoint wikidata-mismatch-finder_mariadb_1 (dbd1f278da0a5edb416b7875f224b83d1f2c08feac6e9a31d01d28567b83b4c7): Error starting userland proxy: listen tcp4 0.0.0.0:3306: bind: address already in use
```

**Possible Explanation:** This error most probably occurs when you already have a local system-wide instance of MySQL or MariaDB running on the default port `3306`.

**Solution:** Set the port to something other than `3306` using the `FORWARD_DB_PORT` environment variable.

***While running the app:***

```bash
FORWARD_DB_PORT=3308 sail up
```

OR ***In your `.env` file:***

```bash
##...
APP_DEBUG=true
APP_URL=http://localhost

FORWARD_DB_PORT=3308
##...
```

#### `laravel.test`

**Problem:** Sail refuses to start with the following error.

```
ERROR: for wikidata-mismatch-finder_laravel.test_1  Cannot start service laravel.test: driver failed programming external connectivity on endpoint wikidata-mismatch-finder_laravel.test_1 (0fa137072f412614077154ca7927cbb0ca2f3df5474879bb5e8e33f15a1683e3): Error starting userland proxy: listen tcp4 0.0.0.0:80: bind: address already in use
```

**Possible Explanation:** This error most probably occurs when you already have a local web server running on the default port `80`.

**Solution:** Set the port to something other than `80` using the `APP_PORT` environment variable.

***While running the app:***

```bash
APP_PORT=1337 sail up
```

OR ***In your `.env` file:***

```bash
##...
APP_DEBUG=true
APP_URL=http://localhost:1337

APP_PORT=1337
##...
```





### OAuth Error retrieving temporary credentials

**Problem:** The local server returns this error on clicking the log in link.

```
League\OAuth1\Client\Credentials\CredentialsException
Error in retrieving temporary credentials.
http://localhost/auth/login
```
**Possible Explanation:**

After following the steps in the [Working with OAuth section](#working-with-oauth) you might get this error. It means your OAuth Consumer Registration is still being processed and you have to wait for the consumer key and consumer secret to be approved.

This error might be caused as well if you have entered the wrong consumer credentials, or a wrong APP_URL. 

**Solution:**  Wait a few hours and try again.

## Sources

[Laravel Sail](https://laravel.com/docs/8.x/sail)

[Laravel Installation](https://laravel.com/docs/8.x/installation)

[Laravel Configuration](https://laravel.com/docs/8.x/configuration)

[OAuth For Developers - MediaWiki](https://www.mediawiki.org/wiki/OAuth/For_Developers#Registration)
