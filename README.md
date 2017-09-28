# sbox - Share Your Files Securely!

**Note**: This project is at an early stage of development and still in heavy development. It’s also missing a thorough security audit.  This service is currently able to transparently de- and encrypt database entries on a per user basis. It deploys encryption at rest, not end to end encryption. It the future, this project  will be reuploaded with much higher code quality and an increased feature set. 

## Installation

What you'll need:

 * PHP >= 7.1
 * libsodium >= 1.0.12
 * PostgreSQL >= 9.0
 * php-libsodium >= 2.0
 * php-pgsql
 * php-sqlite

Then follow these steps:

 * Make sure, that the php extensions are enabled in your php configuration.

Issue the following commands from the project root directory to get started:

```shell
php composer.phar install             # Install dependencies.
bin/console doctrine:database:create  # Create the database.
```

_Note_: If you use Unix domain socket authentication, set the `database_host` value to `null` and the `database_user` to the OS user under which the sbox application will be running.

Now add the `uuid-ossp` extension to your database:

```shell
psql -d sbox_db -c 'CREATE EXTENSION IF NOT EXISTS "uuid-ossp";'
```

Then:

```shell
bin/console doctrine:schema:update --force   # Create the database tables.
bin/console sbox:user:create                 # Add a test user.
# Enter the data of a test user.
```
## Run

To start the internal web server that ships with PHP, issue the following command:

```shell
bin/console server:start
```
    
The application should now be reachable under `http://localhost:8000`. A valid login request looks as follows:

```http
POST /api/user/login HTTP/1.1
Host: localhost
Content-Type: application/x-www-form-urlencoded
Content-Length: 40
    
username=<your-user-name>&password=<your-password>
```

Once you have authenticated successfully, you can test the API with the `/api/hello` request:

```http
GET /app_dev.php/api/hello HTTP/1.1
Host: localhost
Cookie: sbox_session=<the-session-id-you-received-before-with-set-cookie>
```

If everything goes well, you should receive something like this:

```http
HTTP/1.1 200 OK
Host: localhost
Connection: close
Cache-Control: no-cache, private
Date: Wed, 31 May 2017 17:04:29 GMT
Content-Type: application/json

{"hello":"world"}
```

