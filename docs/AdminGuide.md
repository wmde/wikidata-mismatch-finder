# Mismatch Finder Administration Guide

## Managing Upload Users

While every user can obtain an API token to interact with the Mismatch Finder API, not every user is allowed to upload mismatch files. As an administrator, you can manage the list of users who are allowed to provide them.

### Using Laravel's artisan console

1. Log in to your server and `cd` into the wikidata-mismatch-finder directory. 
1. Place a text file that lists one user per line in the `storage/app/allowlist/` directory and run the command with your file name as first argument.

The following custom commands are provided to manage the allow list:
 * `php artisan uploadUsers:show` will show you the current list of users who are allowed to provide uploads.
 * `php artisan uploadUsers:set {allowlist}` will wipe the existing allow list and replace it with a new one. 

Instructions on how to use the commands can be displayed using:
 * `php artisan help uploadUsers:show` and
 * `php artisan help uploadUsers:set`

**Examples**

Import an initial allow list:

```bash
$ cd wikidata-mismatch-finder/
$ php artisan uploadusers:set example_1.txt
Trying to read allow list from storage/app/allowlist/example_1.txt
Successfully imported 3 upload users.
```

Show the result:

```bash
$ php artisan uploadusers:show
Example User 1
Example User 2
Example User 3
```
Replace an existing allow list:

```bash
$ php artisan uploadusers:set example_2.txt
Trying to read allow list from storage/app/allowlist/example_2.txt
Successfully imported 3 upload users.

$ php artisan uploadusers:show
Example User 4
Example User 5
Example User 6
```

## Log Track of Users' Review Decisions

Review decisions are written to mismatch entries in the database directly, without recording an edit history. Thus, for sanity reasons, a record of review decisions is kept on the filesystem in `storage/logs/mismatch_updates.log`.

Example entry:
```
{
    "username": "zakary.johnson",
    "mw_userid": 63812352,
    "mismatch_id": 1,
    "item_id": "Q3570615",
    "property_id": "P5474221",
    "statement_guid": "Q3570615$7b22f0c9-7f5b-386b-a2da-92f1dd7d01c8",
    "wikidata_value": "404509851"
    "external_value": "482752654",
    "review_status_old": "pending",
    "review_status_new": "wikidata",
    "time": "2021-10-05 14:44:59",
}
```
