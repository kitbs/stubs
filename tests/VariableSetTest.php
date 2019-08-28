<?php

namespace Tests;

use InvalidArgumentException;

use Stub\Stub;
use Tests\VariableSets;

class VariableSetTest extends TestCase
{
    public function testNovaToolVariableSet()
    {
        $package = 'test-vendor/test-package';

        $variables = VariableSets\NovaTool::make($package)->values();

        $this->assertEquals([
            'package'          => 'test-vendor/test-package',
            'component'        => 'test-package',
            'title'            => 'Test Package',
            'class'            => 'TestPackage',
            'namespace'        => 'TestVendor\\TestPackage',
            'name'             => 'test-package',
            'escapedNamespace' => 'TestVendor\\\\TestPackage',
        ], $variables);
    }

    public function testNovaToolVariableSetValidate()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The base value expects a vendor and name in \'Composer\' format');

        $package = 'not-a-package';

        $variables = VariableSets\NovaTool::make($package)->values();
    }

    public function testVariableSetNoAdditional()
    {
        $variables = VariableSets\Example1::make('User')->values();

        $this->assertEquals([
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testVariableSetAdditionalArray()
    {
        $variables = VariableSets\Example1::make('User', ['additional' => 'extra'])->values();

        $this->assertEquals([
            'additional'   => 'extra',
            'name'         => 'User',
            'lower_plural' => 'users',
        ], $variables);
    }

    public function testVariableSetAdditionalVariableSet()
    {
        $postVariables = VariableSets\Example2::make('Post')->values();
        $variables = VariableSets\Example1::make('User', $postVariables)->values();

        $this->assertEquals([
            'name'              => 'User',
            'lower_plural'      => 'users',
            'post_name'         => 'Post',
            'post_lower_plural' => 'posts',
        ], $variables);
    }

    public function testVariableSetStubbing()
    {
        $variables = VariableSets\Example1::make('Blog Post');

        $count = (new Stub)
            ->source(__DIR__.'/stubs/stub-2')
            ->output(__DIR__.'/project')
            ->render($variables);

        $this->assertFileExists(__DIR__.'/project/views/blog-posts/index.blade.php');
        $this->assertEquals(
            '<button>Create Blog Post</button>',
            file_get_contents(__DIR__.'/project/views/blog-posts/index.blade.php')
        );

        $this->assertEquals(2, $count);
    }
}
