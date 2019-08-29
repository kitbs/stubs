# Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs) [![Twitter Follow](https://img.shields.io/twitter/follow/dillinghammm?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/dillinghammm)

A package to create files, folders and content with variables.

Available in PHP for applications or as a [CLI productivity tool](https://github.com/awesome-stubs/cli).

![banner](https://user-images.githubusercontent.com/29180903/63966958-6583ee80-ca6a-11e9-82f8-0a3caa80a604.png)

[PHP Class](https://github.com/dillingham/stubs#render-stubs) | [Standalone CLI](https://github.com/awesome-stubs/cli) | [Laravel Support](https://github.com/dillingham/stubs#laravel-support)

## Installation

```
composer require dillingham/stubs
```

## What is a stub?

A stub is a file or series of files that you wish to replicate on command. Furthermore, the file names, folder structure and content can be made unique with the use of variables. Save your stubs, groups of related files, in folders with a descriptive name and and render them using methods documented below:

## Render stubs

Simply declare the source, output and which variables to render.

```php
use Stub\Stub;
```
#### Process a folder and output files to another folder:
```php
(new Stub)
    ->source('stubs/stub-1')
    ->output('projects/project-2')
    ->render($variables);
```

`render()` returns the count of created files

#### Variables

In `render()`, variables are declared as `'variable' => 'value'`

```php
[
    'resource' => 'User',
    'plural' => 'Users',
    'lower' => 'user',
]
```

Becomes `{{resource}}` `{{plural}}` `{{lower}}` in the stubs. [View examples](https://github.com/dillingham/stubs/tree/master/tests/stubs).

Note: optionally append `.stub` to filenames to avoid IDE errors.

#### Process a folder and send the files to a callback:

```php
(new Stub)
    ->source('stubs/stub-2')
    ->output(function($path, $content) {

        // Called for each parsed file, instead of storing it
        // Useful for further modifications before you store it
        // or posting to an API like stubbing a github repository

    })->render($variables);
```

#### Inspect & filter parsed files & content before outputing:

```php
(new Stub)
    ->source('stubs/stub-3')
    ->output('project-name')
    ->filter(function($path, $content) {
    
        // called for each rendered file, BEFORE it is created
        // return false will prevent the output of that path
        // returning true or nothing will proceed normally
        
    })->render($variables);
```

#### Process a folder and listen to all created files with a callback:

```php
(new Stub)
    ->source('stubs/stub-3')
    ->output('project-name')
    ->listen(function($path, $content, $success) {

        // Called for each file after the file it is parsed & stored
        // This may be used to log or output the results of the process
        // $success is either true / false depending on the storing result

    })->render($variables);
```

## Create stubs

Creating stubs by hand is easy and works just as well. But you may find cases where you wish to generate stubs automatcally. Such as, you really like a way a current project is structured and you want the ability to replicate it quickly in the future. That scenario is probably better accompliished via the CLI tool or artisan command, but its available via the class also.

#### Convert existing files into stubs for future use:

```php
(new Stub)
    ->source('project')
    ->output('stubs/stub-name')
    ->create([
        'User' => 'resource',
        'Users' => 'plural',
        'user' => 'lower'
    ]);
```

The above code performs the following behavior:

- Renders all files & folders in `project`
- Replaces `Users` with `{{name}}`
- Replaces `user` with `{{lower}}`
- Appends `.stub` to filenames (Avoids IDE errors)

`create()` returns the count of created files

---

# Laravel Support

> Use artisan commands & facades along with methods demonstrated above

You have immediate access to the following after you composer install:

#### Facade

```php
Stub::source(resource_path('stubs/pattern-1'))
    ->output(app_path())
    ->render($variables);
```
```php
Stub::source(app_path())
    ->output(resource_path('stubs/pattern-1'))
    ->create($variables);
```

#### Artisan

Create variables in a stub.json interactively:
```bash
php artisan stub:init
```

```bash
php artisan stub:render source output stub.json
```
```bash
php artisan stub:create source output stub.json
```
```bash
php artisan stub:quick ./project/one ./project/two
```
