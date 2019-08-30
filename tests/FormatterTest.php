<?php

namespace Tests;

use ErrorException;
use InvalidArgumentException;

use Stub\Stub;
use Tests\Formatters;

class FormatterTest extends TestCase
{
    public function testNovaToolFormatter()
    {
        $package = 'test-vendor/test-package';

        $variables = (new Formatters\NovaTool(['package' => $package]))->all();

        $this->assertEquals([
            'package'          => 'test-vendor/test-package',
            'component'        => 'test-package',
            'title'            => 'Test Package',
            'class'            => 'TestPackage',
            'namespace'        => 'TestVendor\\TestPackage',
            'name'             => 'test-package',
            'vendor'           => 'test-vendor',
            'escapedNamespace' => 'TestVendor\\\\TestPackage',
        ], $variables);
    }

    public function testNovaToolFormatterValidateIncorrect()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `package` variable expects a vendor and name in \'Composer\' format');

        $package = 'not-a-package';

        $variables = (new Formatters\NovaTool(['package' => $package]))->all();
    }

    public function testNovaToolFormatterValidateMissing()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Undefined variable: Tests\Formatters\NovaTool::$package');

        $variables = (new Formatters\NovaTool([]))->all();
    }

    public function testFormatterInvoke()
    {
        $variables = (new Formatters\Example1(['name' => 'User']));

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables());
    }

    public function testFormatterBasic()
    {
        $variables = (new Formatters\Example1(['name' => 'User']))->all();

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testFormatterOriginal()
    {
        $variables = (new Formatters\Example1(['name' => 'User']))->original();

        $this->assertEquals([
            'name' => 'User',
        ], $variables);
    }

    public function testFormatterAdditionalVariables()
    {
        $variables = (new Formatters\Example1(['name' => 'User', 'additional' => 'extra']))->all();

        $this->assertEquals([
            'additional'   => 'extra',
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testFormatterStaticMake()
    {
        $variables = Formatters\Example1::make(['name' => 'User']);

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testFormatterHelperMethods()
    {
        $variables = (new Formatters\WithHelpers(['name' => 'User']))->all();

        $this->assertNotContains('helper', $variables);
        $this->assertNotContains('helper_with_argument', $variables);

        $this->assertEquals([
            'name'                      => 'User',
            'test_helper'               => '__HELPER__',
            'test_helper_with_argument' => '__HELPER:ARGUMENT__',
        ], $variables);
    }

    public function testFormatterStubbingWithArray()
    {
        $variables = (new Formatters\Example1(['name' => 'Blog Post']))->all();

        $stub = (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->render($variables);

        $count = count($stub->rendered);

        $this->assertFileExists(__DIR__.'/project/views/blog-posts/index.blade.php');
        $this->assertEquals(
            '<button>Create Blog Post</button>',
            file_get_contents(__DIR__.'/project/views/blog-posts/index.blade.php')
        );

        $this->assertEquals(2, $count);
    }

    public function testFormatterStubbingWithFormatterObject()
    {
        $formatter = new Formatters\Example1(['name' => 'Blog Post']);

        $stub = (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->render($formatter);

        $count = count($stub->rendered);

        $this->assertFileExists(__DIR__.'/project/views/blog-posts/index.blade.php');
        $this->assertEquals(
            '<button>Create Blog Post</button>',
            file_get_contents(__DIR__.'/project/views/blog-posts/index.blade.php')
        );

        $this->assertEquals(2, $count);
    }

    public function testFormatterVariableGet()
    {
        $formatter = new Formatters\Example2(['name' => 'Users']);

        // Will return unmodified variable
        $this->assertEquals('Users', $formatter->get('name'), 'Failed asserting that `->get(\'name\')` returns expected value.');
        $this->assertEquals('Users', $formatter->name, 'Failed asserting that `->name` returns expected value.');

        // Will return modified variable
        $this->assertEquals('Users', $formatter->name(), 'Failed asserting that `->name()` returns expected value.');
        $this->assertEquals('User', $formatter->singular_name(), 'Failed asserting that `->singular_name()` returns expected value.');
    }

    public function testFormatterComputedVariableGet()
    {
        $formatter = new Formatters\Example2(['name' => 'Users']);

        // Computed variable cannot be retrieved with get()
        try {
            $this->assertEquals('users', $formatter->get('lower_plural'));
        }
        catch (\Exception $e) {
            $this->assertInstanceOf(\ErrorException::class, $e, 'Failed asserting that `->get(\'lower_plural\')` does not exist.');
        }

        // Computed variable cannot be retrieved with __get()
        try {
            $this->assertEquals('users', $formatter->lower_plural);
        }
        catch (\Exception $e) {
            $this->assertInstanceOf(\ErrorException::class, $e, 'Failed asserting that `->lower_plural` does not exist.');
        }

        // Will return computed variable
        $this->assertEquals('users', $formatter->lower_plural(), 'Failed asserting that `->lower_plural()` returns expected value');
    }

    public function testFormatterVariableSet()
    {
        $formatter = new Formatters\Example2(['name' => 'Users']);

        $this->assertFalse($formatter->has('package'), 'Failed asserting that variable \'package\' does not exist.');

        $formatter->set('package', 'users-package');

        $this->assertTrue($formatter->has('package'), 'Failed asserting that variable \'package\' exists.');

        $variables = $formatter->all();

        $this->assertEquals([
            'name'          => 'Users',
            'lower_plural'  => 'users',
            'singular_name' => 'User',
            'package'       => 'users-package',
        ], $variables);
    }

    public function testFormatterVariableUnset()
    {
        $formatter = new Formatters\Example2(['name' => 'Users', 'package' => 'users-package']);

        $this->assertTrue($formatter->has('package'), 'Failed asserting that variable \'package\' exists.');

        $formatter->unset('package');

        $this->assertFalse($formatter->has('package'), 'Failed asserting that variable \'package\' does not exist.');

        $variables = $formatter->all();

        $this->assertEquals([
            'name'          => 'Users',
            'lower_plural'  => 'users',
            'singular_name' => 'User',
        ], $variables);
    }

    public function testFormatterVariableMerge()
    {
        $formatter = new Formatters\Example1(['name' => 'User']);

        $this->assertFalse($formatter->has('package'), 'Failed asserting that variable \'package\' does not exist.');

        $formatter->merge([
            'package'    => 'users-package',
            'additional' => 'extra',
        ]);

        $this->assertTrue($formatter->has('package'), 'Failed asserting that variable \'package\' exists.');

        $variables = $formatter->all();

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
            'package'      => 'users-package',
            'additional'   => 'extra',
        ], $variables);
    }
}
