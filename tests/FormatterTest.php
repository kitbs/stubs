<?php

namespace Tests;

use InvalidArgumentException;

use Stub\Stub;
use Tests\Formatters;

class FormatterTest extends TestCase
{
    public function testNovaToolFormatter()
    {
        $package = 'test-vendor/test-package';

        $variables = (new Formatters\NovaTool(['package' => $package]))->compute();

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

        $variables = (new Formatters\NovaTool(['package' => $package]))->compute();
    }

    public function testNovaToolFormatterValidateMissing()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The `package` variable expects a vendor and name in \'Composer\' format');

        $variables = (new Formatters\NovaTool([]))->compute();
    }

    public function testFormatterNoAdditional()
    {
        $variables = (new Formatters\Example1(['name' => 'User']))->compute();

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testFormatterAdditionalArray()
    {
        $variables = (new Formatters\Example1(['name' => 'User', 'additional' => 'extra']))->compute();

        $this->assertEquals([
            'additional'   => 'extra',
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testFormatterHelperMethods()
    {
        $variables = (new Formatters\WithHelpers(['name' => 'User']))->compute();

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    // public function testFormatterAdditionalFormatter()
    // {
    //     $postVariables = (new Formatters\Example2(['name' => 'Post']))->compute();
    //     $variables = (new Formatters\Example1('User', $postVariables))->compute();
    //
    //     $this->assertEquals([
    //         'name'              => 'User',
    //         'lower_plural'      => 'users',
    //         'post_name'         => 'Post',
    //         'post_lower_plural' => 'posts',
    //     ], $variables);
    // }

    public function testFormatterStubbing()
    {
        $variables = (new Formatters\Example1(['name' => 'Blog Post']))->compute();

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
}
