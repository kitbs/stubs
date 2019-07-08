# Stubs

A package to create files & folders with variables

### Install

```
composer require dillingham/stubs
```

### Overview

Place variables, using this syntax {{variable}} in:

Folder names

```
views/{{plural}}/index.blade.php
views/users/index.blade.php
```

File names

```
controllers/{{name}}Controller.php
controllers/UserController.php
```

File content

```php
class {{user}}Controller
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
controllers/UsersController.php
```

### Variables

Variables are declared as an associative array

The key is referenced  between brackets {{key}}

```php
[
    'name' => 'User',
    'plural' => 'Users',
    'lower' => 'user',
]
```

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