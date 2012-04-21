<?php
/*
 * Copyright (C) 2012 Michael Saller
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions
 * of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF
 * CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace test;

class PermissionTests extends \PHPUnit_Framework_TestCase {
    /**
     * @var \enork\Kernel
     */
    private static $kernel;

    public function setUp() {
        self::resetKernel();
    }

    private static function resetKernel() {
        self::$kernel = new \enork\Kernel('http://root:root@127.0.0.1:5984', 'enork');
        self::$kernel->destroy();
        self::$kernel->init();
    }

    /**
     */
    public function testCheckUber() {
        // arrange
        $user = new \enork\User(self::$kernel, 'root');
        $context = new KernelTest_Mock_Context($user);
        self::$kernel->pushContext($context);

        $file = self::$kernel->getRootFile();
        $context->setUser(self::$kernel->getRootUser());

        // act
        $check = $context->checkPermissions($file, \enork\kernel\Context::PERMISSION_READ)
                    && $context->checkPermissions($file, \enork\kernel\Context::PERMISSION_WRITE)
                    && $context->checkPermissions($file, \enork\kernel\Context::PERMISSION_EXECUTE);

        // assert
        $this->assertTrue($check);
    }

    public function testPopEmptyContext() {
        try {
            self::$kernel->popContext();
            $this->fail('Expecting Exception_MissingContext');
        }
        catch(\enork\Exception_MissingContext $e) {
            // pass
        }
    }
}

class KernelTest_Mock_Context extends \enork\kernel\UserContext {
    /**
     * @param array  $permissions
     * @param string $requested the requested permission type
     * @return bool
     */
    public function checkPermissions(\enork\File $permissions, $requested) {
        return parent::checkFilePermission($permissions, $requested);
    }
}