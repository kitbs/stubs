<?php

namespace Tests;

use Stub\Stub;

class StubTest extends TestCase
{
    public function testDirectoryToDirectoryStubbing()
    {
        Stub::source(__DIR__.'/stubs')
            ->output(__DIR__.'/output')
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists(__DIR__.'/output/User.php');
        $this->assertFileExists(__DIR__.'/output/folder/UserFactory.php');
        $this->assertFileExists(__DIR__.'/output/User-folder/Example.php');
        $this->assertFileExists(__DIR__.'/output/folder/another-folder/UserController.php');

        $this->assertEquals('User is present', file_get_contents(__DIR__.'/output/User.php'));
    }

    public function testFileToDirectoryStubbing()
    {
        Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(__DIR__.'/output')
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists(__DIR__.'/output/User.php');

        $this->assertEquals('User is present', file_get_contents(__DIR__.'/output/User.php'));
    }

    public function testDirectoryToCallbackStubbing()
    {
        Stub::source(__DIR__.'/stubs/')
            ->output(function ($path, $content) {
                $this->assertContains('User', $path);
                $this->assertContains('User', $content);
                $this->assertNotContains('stubs', $path);
            })->parse(['name' => 'User', 'lower' => 'user']);
    }

    public function testFileToCallbackStubbing()
    {
        Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(function ($path, $content) {
                $this->assertEquals('User.php', $path);
                $this->assertEquals('User is present', $content);
            })->parse(['name' => 'User', 'lower' => 'user']);
    }

    public function testFileToCallbackStubbingNonStatic()
    {
        (new Stub)->source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(function ($path, $content) {
                $this->assertContains('User', $path);
                $this->assertContains('User', $content);
            })->parse(['name' => 'User', 'lower' => 'user']);
    }
}
