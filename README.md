# 📂 Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs) [![Twitter Follow](https://img.shields.io/twitter/follow/dillinghammm?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/dillinghammm)

A package to create files, folders and content with variables.

[Jump to CLI Section](https://github.com/dillingham/stubs#command-line-interface)

### Installation

```
composer require dillingham/stubs
```
or install globally to use the cli:
```
composer global require dillingham/stubs
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

### Variable Placement

Variables can be in filepaths, filenames & in the content

```
/views/{{name}}/index.blade.php
```
```
<buton>Create {{name}}</button>
```

For a basic example, [click here](https://github.com/dillingham/stubs/tree/master/tests/stubs)

### Usage

Simply declare the source and output and which variables to parse.
```php
use Stub\Stub;
```
#### Process the contents of a folder & output to another folder
```php
(new Stub)
    ->source('stubs/stub-1')
    ->output('project-name')
    ->parse($variables);
```

#### Process the contents of a folder and send the results to a callback
```php
(new Stub)
    ->source('stubs/stub-2')
    ->output(function($path, $content) {
        // called for each parsed file
    })->parse($variables);
```
You must handle/store file(s) yourself in the callback.

## Command Line Interface

Perform the same behavior described above from the command line

---
Composer install this package globally:

```bash
composer global require dillingham/stubs
```

You can pass variables to `stubs` like so:

```bash
stub parse ./source ./output key:value key:"value with spaces"
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
