<?php
namespace stackos;
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

class Exception extends \lean\Exception {
    public function __construct($message = '', $code = 10, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Exception_UserNotFound extends Exception {
    public function __construct($message = '', $code = 20, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Exception_FileNotFound extends Exception {
    public function __construct($message = '', $code = 30, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Exception_UserExists extends Exception {
    public function __construct($message = '', $code = 40, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Exception_PermissionDenied extends Exception {
    public function __construct($message = '', $code = 50, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    const PERMISSION_DENIED_MISSING_PERMISSION = 51;
    const PERMISSION_DENIED_CREDENTIALS_REVOKED = 52;
    const PERMISSION_DENIED_CANT_CREATE_ROOT_USER = 53;
    const PERMISSION_DENIED_CANT_CREATE_ROOT_FILE = 54;
}

class Exception_MissingSecurityStrategy extends Exception {
    public function __construct($message = '', $code = 50, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class Exception_RootHasNoParent extends Exception{
    public function __construct($message = '', $code = 60, \Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}