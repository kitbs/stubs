# Stubs

[![Build Status](https://travis-ci.com/dillingham/stubs.svg?branch=master)](https://travis-ci.com/dillingham/stubs)
[![Latest Version on Github](https://img.shields.io/github/release/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs)
[![Total Downloads](https://img.shields.io/packagist/dt/dillingham/stubs.svg?style=flat-square)](https://packagist.org/packages/dillingham/stubs) [![Twitter Follow](https://img.shields.io/twitter/follow/dillinghammm?color=%231da1f1&label=Twitter&logo=%231da1f1&logoColor=%231da1f1&style=flat-square)](https://twitter.com/dillinghammm)

A package to create files, folders and content with variables.

Available in PHP for applications or a CLI as a productivity tool.

![banner](https://user-images.githubusercontent.com/29180903/63810460-c688b680-c8f2-11e9-925d-444c00176e71.png)

[PHP Class](https://github.com/dillingham/stubs#render-stubs) | [Standalone CLI](https://github.com/dillingham/stubs#standalone-cli) | [Laravel Support](https://github.com/dillingham/stubs#laravel-support)

## Installation

```
composer require dillingham/stubs
```

## What is a stub?

A stub is a file or series of files that you wish to replicate on command. Furthermore, the file names, folder structure and content can be made unique with the use of variables. Save your stubs, groups of related files, in folders with a descriptive name and render them using methods documented below.

## Render stubs

### Basic usage

Simply declare the source path, output path, and which variables to render.

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

`render()` returns the count of created files.

Note: Optionally append `.stub` to filenames to avoid IDE errors.

#### Variables

In `render()`, variables are declared as `'variable' => 'value'`:

```php
[
    'resource' => 'User',
    'plural' => 'Users',
    'lower' => 'user',
]
```

Becomes `{{resource}}` `{{plural}}` `{{lower}}` in the stubs. [View examples](https://github.com/dillingham/stubs/tree/master/tests/stubs).

#### Variable sets

A common use case when replacing variables in stubs is to take a simple value, such as the name of a model (e.g. `blog post`), and transform it in predictable ways, e.g. for use as a class name (singular StudlyCase `BlogPost`), a table name (plural snake_case `blog_posts`), perhaps a permission key (plural kebab-case `blog-posts`), etc.

For these situations, it is possible to use a "variable set". A variable set is a class that extends `Stub\VariableSet` and implements the abstract function `transform()` to return an array of derived variables (see below).

##### Initialising a variable set

The variable set is initialised with a "base" value, and you can optionally include any other additional variables as an array:

```php
$variableSet = ModelVariables::make('blog post');
```
or:

```php
$variables = [
    'additional' => 'additional value'
];

$variableSet = ModelVariables::make('blog post', $variables);
```

Note: You can also pass another `VariableSet` into the constructor to provide the additional variables.

##### Deriving variables

Within the `transform()` function, you should use `$this->get()` without an argument to retrieve the base value, or with an argument, i.e. `$this->get('additional')`, to retrieve one of the additional variables that was passed to the constructor:

```php
use Stub\VariableSet;
use Illuminate\Support\Str;

class ModelVariables extends VariableSet
{
    protected function transform()
    {
        return [
            'model'      => Str::studly(Str::singular($this->get())),
            'table'      => Str::snake(Str::plural($this->get())),
            'permission' => Str::kebab(Str::plural($this->get())),
            'additional' => Str::upper($this->get('additional')),
        ];
    }
}
```

Note: `Illuminate\Support\Str` is used in the above example to transform the case of the values, but this is not required.

##### Retrieving values

The array of derived values from the variable set can be retrieved with `$variableSet->values()`, or you can simply pass the `VariableSet` object to `render()` or `create()`.

Note: The additional variables that were passed to the constructor will be merged with the values returned by `transform()`, so if they do not need any transformation, you do not need to include them again in the `transform()` array.

##### Variable validation

If you need to validate the base value or additional variables that are passed into the constructor, you can override the `validate()` function on your `VariableSet` class, and throw an `InvalidArgumentException` on any value from `get()` that does not pass your checks. [View example](https://github.com/dillingham/stubs/tree/master/tests/VariableSets/NovaTool#L24).

### Advanced usage

#### Process a folder and send the files to a callback:

```php
(new Stub)
    ->source('stubs/stub-2')
    ->output(function($path, $content) {

        // Called for each parsed file, instead of storing it
        // Useful for further modifications before you store it
        // or posting to an API like stubbing a GitHub repository

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

Creating stubs by hand is easy and works just as well. But you may find cases where you wish to generate stubs automatically. Such as, you really like a way a current project is structured and you want the ability to replicate it quickly in the future. That scenario is probably better accomplished via the CLI tool or artisan command, but it's available via the class also.

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
- Appends `.stub` to filenames (to avoid IDE errors)

`create()` returns the count of created files.

---

# Standalone CLI

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
