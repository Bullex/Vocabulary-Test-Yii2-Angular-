Yii 2 + Angular Dictionary Test
===============================

Yii 2 + Angular Dictionary Test is a test assignment.

DIRECTORY STRUCTURE
-------------------

      assets/             contains assets definition
      commands/           contains console commands (controllers)
      config/             contains application configurations
      controllers/        contains Web controller classes
      mail/               contains view files for e-mails
      models/             contains model classes
      runtime/            contains files generated during runtime
      tests/              contains various tests for the basic application
      vendor/             contains Yii2 framework along with dependent 3rd-party packages
      views/              contains view files for the Web application
      web/                contains the entry script and Web resources



REQUIREMENTS
------------

1. PHP VERSION
    - The minimum requirement for this project is that your Web server supports PHP 5.4.0.
2. APACHE2
    - You should have apache2 installed on your server with modules allowing to process php scripts.


INSTALLATION
------------

Set cookie validation key in `config/web.php` file to some random secret string:

```php
'request' => [
    // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
    'cookieValidationKey' => '<secret random string goes here>',
],
```

CONFIGURATION
-------------

### Database

1. Edit the file `config/db.php` with real data, for example:

```
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=localhost;dbname=skyeng',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
];
```

2. Source the file `db_dump.sql` to your MySQL backend server. 

3. Grant proper r/w access for the user `root` to the database named `skyeng`.

4. Point your apache2 server to the directory `skyeng/web` under your project root.


RUN & TEST
------------

After you launch the apache2 server, the application should be running on your localhost:

~~~
http://localhost
~~~
