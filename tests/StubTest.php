<?php

namespace Tests;

use Stub\Stub;
use Stub\Sources\Github;
use InvalidArgumentException;

class StubTest extends TestCase
{
    public function testDirectoryToDirectoryStubbingStub1()
    {
        $stub = (new Stub)
            ->source(__DIR__.'/stubs/stub-1')
            ->output(__DIR__.'/project')
            ->render(['name' => 'User', 'lower_plural' => 'users']);

        $this->assertFileExists(__DIR__.'/project/User.php');
        $this->assertEquals(
            'class User extends Model {}',
            file_get_contents(__DIR__.'/project/User.php')
        );
        $this->assertFileExists(__DIR__.'/project/Observers/UserObserver.php');
        $this->assertEquals(
            'class UserObserver {}',
            file_get_contents(__DIR__.'/project/Observers/UserObserver.php')
        );

        $this->assertCount(4, $stub->rendered);
    }

    public function testDirectoryToDirectoryStubbingStub2()
    {
        $stub = (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->render(['name' => 'User', 'lower_plural' => 'users']);

        $this->assertFileExists(__DIR__.'/project/views/users/index.blade.php');
        $this->assertEquals(
            '<button>Create User</button>',
            file_get_contents(__DIR__.'/project/views/users/index.blade.php')
        );

        $this->assertCount(2, $stub->rendered);
    }

    public function testDirectoryToCallbackStubbing()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-1')
            ->output(function ($path, $content) {
                $this->assertContains('User', $path);
                $this->assertContains('User', $content);
                $this->assertNotContains('stubs', $path);
                $this->assertNotContains(getcwd(), $path);
            })->render(['name' => 'User']);
    }

    public function testRelativeDirectoryToDirectoryStubbing()
    {
        (new Stub)
            ->source('stubs/stub-1')
            ->output('project')
            ->render(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists(__DIR__.'/project/User.php');
        $this->assertEquals(
            'class User extends Model {}',
            file_get_contents(__DIR__.'/project/User.php')
        );
        $this->assertFileExists(__DIR__.'/project/Observers/UserObserver.php');
        $this->assertEquals(
            'class UserObserver {}',
            file_get_contents(__DIR__.'/project/Observers/UserObserver.php')
        );
    }

    public function testCreatingStubs()
    {
        (new Stub)
            ->source(__DIR__.'/reverse')
            ->output(__DIR__.'/project')
            ->create(['User' => 'name', 'users' => 'lower_plural']);

        $this->assertFileExists(__DIR__.'/project/{{name}}.php.stub');
        $this->assertEquals(
            '{{name}} extends Model',
            file_get_contents(__DIR__.'/project/{{name}}.php.stub')
        );
        $this->assertFileExists(__DIR__.'/project/Controllers/{{name}}Controller.php.stub');
        $this->assertEquals(
            '{{name}}Controller',
            file_get_contents(__DIR__.'/project/Controllers/{{name}}Controller.php.stub')
        );
    }


    public function testArrayIndexAsVariableKeysWhenCreating()
    {
        (new Stub)
            ->source(__DIR__.'/reverse')
            ->output(__DIR__.'/project')
            ->create(array_flip(['User']));

        // array_flip() produces ['User' => '0']
        $this->assertFileExists(__DIR__.'/project/{{0}}.php.stub');
    }

    public function testArrayIndexAsVariableKeysWhenRendering()
    {
        // using  project/User.php
        // pass ['User' => 0]
        // create project/{{0}}.php.stub
        // Then pass [0 => 'User'] to replace it

        (new Stub)
            ->source(__DIR__.'/reverse')
            ->output(__DIR__.'/project')
            ->create(array_flip(['User']));

        (new Stub)
            ->source(__DIR__.'/project')
            ->output(__DIR__.'/project')
            ->render(['Brian']);

        $this->assertFileExists(__DIR__.'/project/Brian.php');
    }

    public function testListenerCallback()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->listen(function ($path, $content, $success) {
                $this->assertContains(__DIR__.'/project/views/users', $path);
                $this->assertFileExists($path);
                $this->assertContains('User', $content);
                $this->assertTrue($success);
            })->render(['name' => 'User', 'lower_plural' => 'users']);
    }

    public function testGetSourceStubSettings()
    {
        $settings = (new Stub)->settings('stubs/stub-3');
        $this->assertEquals($settings['User\'s name'], 'name');
        $this->assertEquals($settings['User\'s email'], 'email');
        $this->assertEquals($settings['User\'s title'], 'title');
    }

    public function testExceptionWhenSettingsMissing()
    {
        $this->expectException(InvalidArgumentException::class);

        (new Stub)->settings('stubs/stub-2');
    }

    public function testFilterFalseCallback()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->filter(function ($path, $content) {
                return false;
            })->render(['lower_plural' => 'users']);

        $this->assertDirectoryNotExists(__DIR__.'/project/views/users');
    }

    public function testFilterTrueCallback()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->filter(function ($path, $content) {
                return true;
            })->render(['lower_plural' => 'users']);

        $this->assertDirectoryExists(__DIR__.'/project/views/users');
    }

    public function testFilterBlankCallback()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->filter(function ($path, $content) {
                $this->assertContains('views/users', $path);
            })->render(['lower_plural' => 'users']);

        $this->assertDirectoryExists(__DIR__.'/project/views/users');
    }

    public function testGithubSource()
    {
        $stub = (new Stub)
            ->source('https://github.com/awesome-stubs/skeleton-php')
            ->output('project');

        $source = $stub->source;
        $stub->render(['class' => 'Comment']);

        $this->assertDirectoryNotExists($source);
        $this->assertNull($stub->source);
        $this->assertFileExists('project/src/Comment.php');
    }

    public function testGithubSourceShorthand()
    {
        $stub = (new Stub)
            ->source(':skeleton-php')
            ->output('project');

        $source = $stub->source;
        $stub->render(['class' => 'Comment']);

        $this->assertDirectoryNotExists($source);
        $this->assertNull($stub->source);
        $this->assertFileExists('project/src/Comment.php');
    }
}
