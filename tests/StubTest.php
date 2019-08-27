<?php

namespace Tests;

use Stub\Stub;

class StubTest extends TestCase
{
    public function testDirectoryToDirectoryStubbingStub1()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-1')
            ->output(__DIR__.'/project')
            ->parse(['name' => 'User', 'lower_plural' => 'users']);

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

    public function testDirectoryToDirectoryStubbingStub2()
    {
        (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->parse(['name' => 'User', 'lower_plural' => 'users']);

        $this->assertFileExists(__DIR__.'/project/views/users/index.blade.php');
        $this->assertEquals(
            '<button>Create User</button>',
            file_get_contents(__DIR__.'/project/views/users/index.blade.php')
        );
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
            })->parse(['name' => 'User']);
    }

    public function testRelativeDirectoryToDirectoryStubbing()
    {
        (new Stub)
            ->source('stubs/stub-1')
            ->output('project')
            ->parse(['name' => 'User', 'lower' => 'user']);

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
}
