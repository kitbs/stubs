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

    public function testFileToFileStubbing()
    {
        Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(__DIR__.'/output/NewUser.php', true)
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists(__DIR__.'/output/NewUser.php');

        $this->assertEquals('User is present', file_get_contents(__DIR__.'/output/NewUser.php'));
    }

    public function testDirectoryToFileStubbingThrowsException()
    {
        $this->expectExceptionMessage('Argument $isFile passed to Stub\Stub::output() must not be true if argument $path is a directory');

        Stub::source(__DIR__.'/stubs/{{name}}-folder')
            ->output(__DIR__.'/output/ExampleUser.php', true)
            ->parse(['name' => 'User', 'lower' => 'user']);
        
        $this->assertFileNotExists(__DIR__.'/output/ExampleUser.php');
    }

    public function testDirectoryToFileStubbingDoesNotCreateFiles()
    {
        try {
            Stub::source(__DIR__.'/stubs/{{name}}-folder')
                ->output(__DIR__.'/output/ExampleUser.php', true)
                ->parse(['name' => 'User', 'lower' => 'user']);
        } catch (\Exception $e) {
            // Do nothing
        } finally {
            $this->assertFileNotExists(__DIR__.'/output/ExampleUser.php');
        }
    }

    public function testVariablesInOutputDirectoryStubbing()
    {
        Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(__DIR__.'/output/{{lower}}')
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists(__DIR__.'/output/user/User.php');

        $this->assertEquals('User is present', file_get_contents(__DIR__.'/output/user/User.php'));
    }

    public function testVariablesInOutputFileStubbing()
    {
        Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(__DIR__.'/output/{{lower}}/{{name}}Model.php', true)
            ->parse(['name' => 'User', 'lower' => 'user']);

        $this->assertFileExists(__DIR__.'/output/user/UserModel.php');

        $this->assertEquals('User is present', file_get_contents(__DIR__.'/output/user/UserModel.php'));
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

    public function testCallbackStubbingWithIsFileThrowsException()
    {
        $this->expectExceptionMessage('Argument $isFile passed to Stub\Stub::output() must not be true if argument $path is callable');

        $attemptedOutput = false;
        
        Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
            ->output(function ($path, $content) use (&$attemptedOutput) {
                $attemptedOutput = true;
            }, true)->parse(['name' => 'User', 'lower' => 'user']);
        
        $this->assertFalse($attemptedOutput, 'Failed asserting that the output callback was not called.');
    }

    public function testCallbackStubbingWithIsFileDoesNotAttemptOutput()
    {
        $attemptedOutput = false;

        try {
            Stub::source(__DIR__.'/stubs/{{name}}.php.stub')
                ->output(function ($path, $content) use (&$attemptedOutput) {
                    $attemptedOutput = true;
                }, true)->parse(['name' => 'User', 'lower' => 'user']);
        } catch (\Exception $e) {
            // Do nothing
        } finally {
            $this->assertFalse($attemptedOutput, 'Failed asserting that the output callback was not called.');
        }
    }
}
