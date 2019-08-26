<?php

namespace Tests;

use Stub\Stub;

class StubRelativeTest extends TestCase
{
    public function testRelativeDirectoryToDirectoryStubbing()
    {
        Stub::source('stubs')
            ->output('output')
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists('output/User.php');
        $this->assertFileExists('output/folder/UserFactory.php');
        $this->assertFileExists('output/User-folder/Example.php');
        $this->assertFileExists('output/folder/another-folder/UserController.php');

        $this->assertEquals('User is present', file_get_contents('output/User.php'));
    }

    public function testRelativeFileToDirectoryStubbing()
    {
        Stub::source('stubs/{{name}}.php.stub')
            ->output('output')
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists('output/User.php');

        $this->assertEquals('User is present', file_get_contents('output/User.php'));
    }

    public function testRelativeFileToFileStubbing()
    {
        Stub::source('stubs/{{name}}.php.stub')
            ->output('output/NewUser.php', true)
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists('output/NewUser.php');

        $this->assertEquals('User is present', file_get_contents('output/NewUser.php'));
    }

    public function testRelativeVariablesInOutputDirectoryStubbing()
    {
        Stub::source('stubs/{{name}}.php.stub')
            ->output('output/{{lower}}')
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists('output/user/User.php');

        $this->assertEquals('User is present', file_get_contents('output/user/User.php'));
    }

    public function testRelativeVariablesInOutputFileStubbing()
    {
        Stub::source('stubs/{{name}}.php.stub')
            ->output('output/{{lower}}/{{name}}Model.php', true)
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists('output/user/UserModel.php');

        $this->assertEquals('User is present', file_get_contents('output/user/UserModel.php'));
    }
}
