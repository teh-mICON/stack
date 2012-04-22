<?php
namespace stackos\security;
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

class Permission {
    /**
     * @var string he type of holder [user/group]
     */
    private $holderType;
    private $holder;
    private $priviledge;

    public function __construct($holder, $priviledge, $holderType) {
        $this->holder = $holder;
        $this->priviledge = $priviledge;
        $this->holderType = $holderType;
    }

    public function getHolder() {
        return $this->holder;
    }

    public function getPriviledge() {
        return $this->priviledge;
    }

    public function getHolderType() {
        return $this->holderType;
    }
}

class Permission_Group extends Permission {
    public function __construct($holder, $priviledge) {
        parent::__construct($holder, $priviledge, Strategy::PERMISSION_HOLDER_TYPE_GROUP);
    }
}

class Permission_User extends Permission {
    public function __construct($holder, $priviledge) {
        parent::__construct($holder, $priviledge, Strategy::PERMISSION_HOLDER_TYPE_USER);
    }
}