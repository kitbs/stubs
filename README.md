# Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs) [![Twitter Follow](https://img.shields.io/twitter/follow/dillinghammm?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/dillinghammm)

A package to create files, folders and content with variables.

Available in PHP for applications or a CLI as a productivity tool.

![banner](https://user-images.githubusercontent.com/29180903/63810460-c688b680-c8f2-11e9-925d-444c00176e71.png)

[PHP Class](https://github.com/dillingham/stubs#render-stubs) | [Standalone CLI](https://github.com/dillingham/stubs#console) | [Laravel Support](https://github.com/dillingham/stubs#laravel-support)

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

# Console

> Perform the same behavior described above from the command line

Composer install this package globally:

```bash
composer global require dillingham/stubs
```


**For a quick interactive clone with search and replace:**

```
stub quick source output
```
<img width="1019" alt="Screen Shot 2019-08-28 at 2 08 08 AM" src="https://user-images.githubusercontent.com/29180903/63829877-b693c580-c938-11e9-8b43-4c0f65f28bb6.png">

#### Render stubs

```bash
stub render ./source ./output key:value key:"with spaces"
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
}
```
You can generate this interactively by calling `init`

```bash
stub init
```

The **stub.json** from the stub source folder is used by default:
```
stub render source output
```

Add questions to a stub's  source folder:
```json
{
    "What is the user's name?": "name",
    "What is the user's email?": "email",
}
```
The questions will be asked interactively in the console.

The answers will replace `{{name}}` `{{email}}` in the stubs.

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

```bash
php artisan stub:init
```
```bash
php artisan stub:render ./stubs ./project stub.json
```
```bash
php artisan stub:create ./project ./stubs stub.json
```
```bash
php artisan stub:quick ./project/one ./project/two
```
