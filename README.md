# 📂 Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs?style=flat-square)](https://packagist.org/packages/dillingham/stubs)

A package to create files, folders & content with variables. [Example](https://github.com/dillingham/stubs/tree/master/tests/stubs)

### Install

```
composer require dillingham/stubs
```

### Variables

Variables are declared as an associative array

The `key` is referenced between brackets {{key}}

```php
[
    'name' => 'User',
    'plural' => 'Users',
    'lower' => 'user',
]
```

becomes: {{name}} {{plural}} {{lower}}

### Usage

Simply declare the  source and output and which variables to parse.
```php
use Stub\Stub;
```

```php
Stub::source('/folder')->output('/folder')->parse($variables);
```
```php
Stub::source('/folder/file.php')->output('/folder')->parse($variables);
```
```php
Stub::source('/folder')
    ->output(function($path, $content){
        // called for every parsed file
    })->parse($variables);
```
```php
Stub::source('/folder/file.php')
    ->output(function($path, $content){
        // called for the single file
    })->parse($variables);
```

### Examples


Folder names

```
views/{{plural}}/index.blade.php
```
```
views/users/index.blade.php
```

File names

```
controllers/{{name}}Controller.php
```
```
controllers/UserController.php
```

File content

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

You can also append `.stub` to avoid IDE errors

```
controllers/{{name}}Controller.php.stub
```
```
controllers/UsersController.php
```
