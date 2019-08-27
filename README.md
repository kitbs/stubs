# 📂 Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs) [![Twitter Follow](https://img.shields.io/twitter/follow/dillinghammm?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/dillinghammm)

A package to create files, folders and content with variables.

[PHP Class](https://github.com/dillingham/stubs#usage) | [Standalone CLI](https://github.com/dillingham/stubs#standalone-cli) | [Laravel Support](https://github.com/dillingham/stubs#laravel-support)

## Installation

```
composer require dillingham/stubs
```
or install globally to use the cli:
```
composer global require dillingham/stubs
```

#### Variables

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

#### Variable Placement

Variables can be in file paths, file names and in the content:

```
/views/{{name}}/index.blade.php
```
```
<button>Create {{name}}</button>
```

For a basic example, [click here](https://github.com/dillingham/stubs/tree/master/tests/stubs).

## Render stubs

Simply declare the source and output and which variables to render.

Note: optionally append `.stub` to filenames to avoid IDE errors.
```php
use Stub\Stub;
```
#### Process a folder and output files to another folder:
```php
(new Stub)
    ->source('stubs/stub-1')
    ->output('project-name')
    ->render($variables);
```

`render()` returns the count of created files

#### Process a folder and send the files to a callback:
```php
(new Stub)
    ->source('stubs/stub-2')
    ->output(function(string $path, string $content) {
        // called for each rendered file, INSTEAD of creating it
    })->render($variables);
```

You must handle/store file(s) yourself in the callback:

- This may be used to modify the file's path or contents before you store it.
- This may be used to send to an API, like stubbing a github repository.

#### Process a folder and listen to all created files with a callback:
```php
(new Stub)
    ->source('stubs/stub-3')
    ->output('project-name')
    ->listen(function(string $path, string $content, bool $success) {
        // called for each rendered file, AFTER it is created
    })->render($variables);
```

The `listen()` callback is called *after* each file has already been created.

This may be used to log or output the results of the process.

## Create stubs

#### Convert existing files into stubs for future use:
```php
(new Stub)
    ->source('project')
    ->output('stubs/stub-name')
    ->create([
        'Users' => 'name',
        'user' => 'lower'
    ]);
```

- Renders all files & folders in `project`
- Replaces `Users` with `{{name}}`
- Replaces `user` with `{{lower}}`
- Appends `.stub` to filenames (Avoids IDE errors)

`create()` returns the count of created files

---

# Standalone CLI

> Perform the same behavior described above from the command line

Composer install this package globally:

```bash
composer global require dillingham/stubs
```

You can pass variables to `stubs` like so:

```bash
stub render ./source ./output key:value key:"value with spaces"
```

For many or more complex variable sets, pass a JSON file path:

```bash
stub render ./source ./output stub.json
```

Example of the JSON file content:

```json
{
    "name": "Brian Dillingham",
    "email": "brian@dillingham.dev",
    "title": "Programmer"
}
```
You can generate this interactively by calling `init`

```bash
stub init
```

---

# Laravel Support

> Use artisan commands & facades along with methods demonstrated above


#### Artisan
```bash
php artisan stub:init
```
```bash
php artisan stub:render ./stubs ./project stub.json
```
```bash
php artisan stub:create ./project ./stubs stub.json
```

#### Facade

```php
Stub::source(resource_path('stubs'))
    ->output(resource_path('Models'))
    ->render($variables);
```
```php
Stub::source(app_path())
    ->output(resource_path('stubs'))
    ->create($variables);
```
