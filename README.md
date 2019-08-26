# 📂 Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)

A package to create files, folders and content with variables.

[Jump to CLI Section](https://github.com/dillingham/stubs#command-line-interface)

### Install

```
composer require dillingham/stubs
```

### Variables

Variables are declared as an associative array.

The `key` is referenced in the file paths and contents between brackets, as `{{key}}`.

```php
[
    'name' => 'User',
    'plural' => 'Users',
    'lower' => 'user',
]
```

becomes `{{name}}` `{{plural}}` `{{lower}}`.

### Usage

Simply declare the source and output and which variables to parse.
```php
use Stub\Stub;
```
#### Copy the contents of one folder to another folder
```php
Stub::source('stubs')->output('output')->parse($variables);
```

#### Copy one file to a folder
```php
Stub::source('stubs/file.php')->output('output')->parse($variables);
```

#### Copy one file to another file
```php
Stub::source('stubs/file.php')->output('output/newfile.php', true)->parse($variables);
```

#### Use variables in the output path
```php
Stub::source('stubs/file.php')->output('output/{{name}}')->parse($variables);
```
```php
Stub::source('stubs/file.php')->output('output/{{name}}/newfile.php', true)->parse($variables);
```

#### Process the contents of a folder and send the results to a callback
```php
Stub::source('stubs')
    ->output(function($path, $content) {
        // called for every parsed file
    })->parse($variables);
```
You must store each file yourself in the callback.

#### Process one file and send the results to a callback
```php
Stub::source('stubs/file.php')
    ->output(function($path, $content) {
        // called for the single file
    })->parse($variables);
```
You must store the file yourself in the callback.

### Example [view](https://github.com/dillingham/stubs/tree/master/tests/stubs)

#### Folder names

```
views/{{plural}}/index.blade.php
```
```
views/users/index.blade.php
```

#### File names

```
controllers/{{name}}Controller.php
```
```
controllers/UserController.php
```

#### File content

```php
class {{name}}Controller
{
    // create {{plural}}
    public function create() {}
}
```

```php
class UserController
{
    // create users
    public function create() {}
}
```

You can also append `.stub` to avoid IDE errors:

```
controllers/{{name}}Controller.php.stub
```
```
controllers/UsersController.php
```


# Command Line Interface
This stubs package call also be used as a CLI tool.

It comes with a `parse` command to perform the above examples.

Composer install this package globally:

```bash
composer global require dillingham/stubs
```

You can pass variables to `stubs` like so:

```bash
stub parse ./source ./output key:value key:value key:"value with spaces"
```

For many or more complex variable sets, pass a json file path:

```bash
stub parse ./source ./output values.json
```

Example of the json file content:

```json
{
    "name": "Brian Dillingham",
    "email": "brian@dillingham.dev",
    "title": "Programmer"
}
```
