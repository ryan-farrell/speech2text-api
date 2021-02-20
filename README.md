#WIP

## README


## Setup

1. You'll need to create a local database with the name speech2text-api and setup your db user to have access to this new db. This is the db username you will use in point 3. 

2. Copy and paste the .env.example file and rename to just .env.

3. In the new .env you will need to add the db username (used in point 1) to line 14 and db password associated with this user to line 15 instead of the place holders. If the user has no password then delete the place holder and leave blank.

4. You will need to drop a copy of your google-credentials.json and rename to setup.json. Place this in the setup-files directory. The setup-files directory is in the root directory of the application.



Windows x86_64
Apache 2.4.43
MySQL 8.0.20
PHP 7.4
Laravel 8.12