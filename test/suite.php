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

// bootstrap
require_once realpath('../external/lean/lean/init.php');

define('APPLICATION_ROOT', realpath('..'));

$autoload = new \lean\Autoload();
$autoload->loadLean();
$autoload->register('test', __DIR__ . '/lib');
$autoload->register('stackos', APPLICATION_ROOT . '/lib');

date_default_timezone_set('Europe/Berlin');

require_once APPLICATION_ROOT . '/external/PHP-on-Couch/lib/couch.php';
require_once APPLICATION_ROOT . '/external/PHP-on-Couch/lib/couchClient.php';
require_once APPLICATION_ROOT . '/external/PHP-on-Couch/lib/couchDocument.php';


class stackosSuite {
    public static function suite() {
        $suite = new \PHPUnit_Framework_TestSuite('stackos');
        $suite->addTestSuite('test\KernelTests');
        $suite->addTestSuite('test\UserTests');
        $suite->addTestSuite('test\FileTests');
        $suite->addTestSuite('test\PermissionTests');

        return $suite;
    }
}