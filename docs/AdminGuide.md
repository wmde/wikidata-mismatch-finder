<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
<!-- param::maxHeaderLevel::2:: -->
**Table of Contents**

- [Mismatch Finder Administration Guide](#mismatch-finder-administration-guide)
  - [Managing Upload Users](#managing-upload-users)
  - [Managing Imported Mismatches](#managing-imported-mismatches)
  - [Log Track of Users' Review Decisions](#log-track-of-users-review-decisions)
  - [Update the expiry date of mismatches](#update-the-expiry-date-of-mismatches)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

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

## Managing Imported Mismatches

While users on the upload list may import their mismatches to Mismatch Finder, they are not allowed to delete the uploaded data again. As an administrator, you can drop entire file imports from Mismatch Finder's store.

### Using Laravel's artisan console

The following custom commands are provided to show and drop mismatch imports:
 * `php artisan import:list` will show you the list of all mismatch imports whose status is _completed_.
 * `php artisan import:drop {id}` will delete an entire import and all of its associated mismatches from Mismatch Finder's store.

Instructions on how to use the commands can be displayed using:
 * `php artisan help import:show` and
 * `php artisan help import:drop`

 **IMPORTANT: Dropping an import from the store will delete all its asociated mismatches, whether they have been reviewed or not.**

**Examples**

```bash
$ cd wikidata-mismatch-finder/
$ php artisan import:list

+----+-------------+-----------------+-----------------+------------+-----------------+
| ID | Import Date | External Source | User            | Expires at | # of Mismatches |
+----+-------------+-----------------+-----------------+------------+-----------------+
| 11 | 2021-09-07  | internet        | raheem.eichmann | 2022-09-07 | 23              |
| 12 | 2021-09-11  | internet        | raheem.eichmann | 2022-09-11 | 42              |
| 13 | 2021-09-17  | internet        | raheem.eichmann | 2022-09-17 | 345             |
+----+-------------+-----------------+-----------------+------------+-----------------+

$ php artisan import:drop 12
Dropping import ID 12 with 42 mismatches

 Are you sure? (yes/no) [no]:
 > y

Successfully dropped import ID 12 with 42 associated mismatches
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
    "meta_wikidata_value": "Q71706",
    "statement_guid": "Q3570615$7b22f0c9-7f5b-386b-a2da-92f1dd7d01c8",
    "wikidata_value": "404509851"
    "external_value": "482752654",
    "review_status_old": "pending",
    "review_status_new": "wikidata",
    "time": "2021-10-05 14:44:59",
    "type": "statement"
}
```

## Update the expiry date of mismatches

1. SSH into toolforge.

    ```bash
    ssh <your_username>@login.toolforge.org
    ```

1. The `become` commands allows you to sudo to another user while retaining your personalized environment.

    ```bash
    become mismatch-finder
    ```

1. Get into the `mismatch-finder-repo` folder

    ```bash
    cd mismatch-finder-repo

1. Access the project's database using artisan. 

    ```bash
    php artisan db
    ```

1. Now inside the database we can perform the update operation. 

    ```sql
    START TRANSACTION;
    UPDATE import_meta SET expires='<date_of_expiration>' where id = <id_of_entry_to_update>; 
    COMMIT; 
    ```

    Example:

    ```sql
    START TRANSACTION;
    UPDATE import_meta SET expires='2022-11-01' where id = 6;
    COMMIT; 
    ```

1. Run this command to manually save the operation we just ran in the database to the [Wikidata Mismatch Finder Server Admin logs](https://sal.toolforge.org/tools.mismatch-finder).

```bash
dologmsg "UPDATE import_meta SET expires='<date_of_expiration>' where id = <id_of_entry_to_update> # <phabricator_ticket_number>"
```

Example:

```bash
dologmsg "UPDATE import_meta SET expires='2022-11-01' where id = 6 # T321586"
```

Make sure the message is exactly the same query used in the previous step and don't forget to add the comment at the end with the ticket number.