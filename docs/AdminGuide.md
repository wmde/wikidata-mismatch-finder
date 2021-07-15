# Mismatch Finder Administration Guide

## Managing Upload Users

While every user can obtain an API token to interact with the Mismatch Finder API, not every user is allowed to upload mismatch files. As an administrator, you can manage the list of users who are allowed to provide them.

### Using Laravel's artisan console

Log in to the server and `cd` into the wikidata-mismatch-finder directory. The following custom commands are provided to manage the allow list:
 * `php artisan uploadUsers:show` will show you the current list of users who are allowed to provide uploads.
 * `php artisan uploadUsers:set {allowlist}` will wipe the existing allow list and replace it with a new one. Place a text file that lists one user per line in the `storage/app/allowlist/` directory and run the command with the file name as first argument.

Instructions on how to use the commands can be displayed using:
 * `php artisan help uploadUsers:show` and
 * `php artisan help uploadUsers:set`


**Example**

Import an initial allow list:
```
# cd wikidata-mismatch-finder/
# php artisan uploadusers:set example_1.txt
Trying to read allow list from storage/app/allowlist/example_1.txt
Successfully imported 3 upload users.
```
Show the result:
```
# php artisan uploadusers:show
Example User 1
Example User 2
Example User 3
```
Replace an existing allow list:
```
# php artisan uploadusers:set example_2.txt
Trying to read allow list from storage/app/allowlist/example_2.txt
Successfully imported 3 upload users.
# php artisan uploadusers:show
Example User 4
Example User 5
Example User 6
```

### Using the API
tbd

### Using the Admin Web Interface
tbd