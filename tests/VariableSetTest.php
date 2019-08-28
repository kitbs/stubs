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

    public function testNovaToolVariableSetValidated()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The base value expects a vendor and name in \'Composer\' format');

        $package = 'not-a-package';

        $variables = VariableSets\NovaTool::make($package)->values();
    }

    public function testVariableSetStubbing()
    {
        $variables = VariableSets\View::make('Blog Post');

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
