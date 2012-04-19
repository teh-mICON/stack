<?php
namespace enork;
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

class Kernel {
    const PERMISSION_READ = 'r';
    const PERMISSION_WRITE = 'w';
    const PERMISSION_EXECUTE = 'x';

    const PERMISSION_TYPE_GROUP = 'g';
    const PERMISSION_TYPE_USER = 'u';

    /**
     * @var \couchClient
     */
    private $couchClient;

    /**
     * @var User;
     */
    private $rootUser;

    /**
     * @var File
     */
    private $rootFile;

    /**
     * @var Kernel_Adapter
     */
    private $adapter;

    /**
     * @var array
     */
    private $contextStack = array();

    /**
     * @var User
     */
    private $user;

    /**
     * Create the kernel.
     *
     * @param string $dsn
     * @param string $dbName
     */
    public function __construct($dsn, $dbName) {
        $this->couchClient = new \couchClient($dsn, $dbName);
        $this->adapter = new Kernel_Adapter($this);
    }

    /**
     * Push a context onto the stack.
     *
     * @param kernel\Context $context
     * @return \enork\Kernel
     */
    public function pushContext(kernel\Context $context) {
        $this->contextStack[] = $context;
        return $this;
    }

    /**
     * Pop a context off the stack
     *
     * @return \enork\kernel\Context
     */
    public function popContext() {
        if (!count($this->contextStack)) {
            throw new Exception_MissingContext("There is no active context on the stack.");
        }
        return array_pop($this->contextStack);
    }

    /**
     * Get the current context from the stack.
     *
     * @throws\enork\Exception_MissingContext
     * @return \enork\kernel\Context
     */
    public function currentContext() {
        if (!count($this->contextStack)) {
            throw new Exception_MissingContext("There is no active context on the stack.");
        }
        return end($this->contextStack);
    }

    /**
     * Lazy get root user
     * @return \enork\User
     */
    public function getRootUser() {
        if ($this->rootUser === null) {
            return $this->rootUser = $this->getUser('root');
        }
        return $this->rootUser;
    }

    /**
     * Lazy get root file
     * @return \enork\File
     */
    public function getRootFile() {
        if ($this->rootFile === null) {
            $this->rootFile = $this->getFile('/');
        }
        return $this->rootFile;
    }

    /**
     * Initialize the kernel.
     * Make sure the database exists.
     * If not:
     * + create database
     * + create filesystem root
     * + create root user
     * + create root user home
     */
    public function init() {
        if (!$this->couchClient->databaseExists()) {
            $this->couchClient->createDatabase();
            $this->pushContext(new \enork\kernel\PrivilegedContext());
            try {
                // root user
                $rootUser = new User($this, 'root', array('root'), '/root');
                $rootUser->setUber(true);
                $doc = $this->adapter->fromUser($rootUser);
                $this->couchClient->storeDoc($doc);
                // root file
                $rootFile = new File($this, '/', $rootUser->getUname());
                $doc = $this->adapter->fromFile($rootFile);
                $this->couchClient->storeDoc($doc);
                // root home file
                $rootHome = new File($this, '/root', $rootUser->getUname());
                $doc = $this->adapter->fromFile($rootHome);
                $this->couchClient->storeDoc($doc);
            }
            // finally pop context
            catch(\Exception $e) {
                // roll back context in case of exception
                $this->popContext();
                throw $e;
            }
            $this->popContext();
        }
    }

    /**
     * Destroy the database.
     */
    public function destroy() {
        if ($this->couchClient->databaseExists()) {
            $this->couchClient->deleteDatabase();
        }
    }

    /**
     * Get a user by their uname
     *
     * @param string $uname
     * @return \enork\User
     */
    public function getUser($uname) {
        // get document
        try {
            $doc = $this->couchClient->getDoc("user:$uname");
        }
        catch (\couchNotFoundException $e) {
            throw new Exception_UserNotFound("The user with the uname '$uname' was not found.");
        }

        // return user abstracted via User instance
        return $this->adapter->toUser($doc);
    }

    /**
     * @param User $user
     * @throws \enork\Exception_MissingContext|\enork\Exception_PermissionDenied|\enork\Exception_UserExists
     */
    public function createUser(User $user) {

        if (!$this->currentContext()->checkUserCreatePermission($user)) {
            throw new Exception_PermissionDenied("The permission to create the user has been denied");
        }
        $doc = $this->adapter->fromUser($user);
        try {
            $this->couchClient->storeDoc($doc);
        }
        catch (\couchConflictException $e) {
            throw new Exception_UserExists("Could not create user with uname '{$user->getUname()}'. Already exists.");
        }
    }

    /**
     * Get a file by its path
     *
     * @param string $path
     * @throws \enork\Exception_MissingContext|\enork\Exception_PermissionDenied
     * @return \enork\File
     */
    public function getFile($path) {
        try {
            $doc = $this->couchClient->getDoc("file:$path");
        }
        catch (\couchNotFoundException $e) {
            throw new Exception_FileNotFound("File '$path' was not found'");
        }
        $file = $this->adapter->toFile($doc);
        if (!$this->currentContext()->checkFilePermission($file, self::PERMISSION_READ)) {
            throw new Exception_PermissionDenied("Permission to receive file '$path' was denied.");
        }
        return $file;
    }

    /**
     * Write a file to the file system
     * Check permissions to write to parent file first
     *
     * @param File $file
     * @return File
     * @throws Exception_PermissionDenied
     */
    public function createFile(File $file) {
        // don't allow creation of root file
        if($file->getPath() == '/') {
            throw new Exception_PermissionDenied("Not allowed to create or delete root file.", Exception_PermissionDenied::PERMISSION_DENIED_CANT_CREATE_ROOT);
        }
        // check file permission on the document
        if (!$this->currentContext()->checkFilePermission($file, self::PERMISSION_READ)) {
            throw new Exception_PermissionDenied("Permission to create file at path '{$file->getPath()}' was denied.", Exception_PermissionDenied::PERMISSION_DENIED_MISSING_PERMISSION);
        }

        // actually write document
        $doc = $this->adapter->fromFile($file);
        $this->couchClient->storeDoc($doc);

        return $file;
    }
}

/**
 * Adapts from and to couchDB documents to instances of the appropriate type
 */
class Kernel_Adapter {
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @param Kernel $kernel
     */
    public function __construct(Kernel $kernel) {
        $this->kernel = $kernel;
    }

    /**
     * Adapt User instance to be saved as a couchdb document
     *
     * @param User $user
     * @return \object
     */
    public function fromUser(User $user) {
        $doc = new \stdClass;
        $doc->_id = 'user:' . $user->getUname();
        $doc->home = 'file:' . $user->getHome();
        $doc->groups = $user->getGroups();
        $doc->uber = $user->getUber();
        return $doc;
    }

    /**
     * Adapt a couchdb document to a User instance
     *
     * @param \object $doc
     * @return User
     */
    public function toUser($doc) {
        // cut prefixes and create user
        $uname = \lean\Text::offsetLeft($doc->_id, 'user:');
        $home = \lean\Text::offsetLeft($doc->home, 'file:');
        $user = new User($this->kernel, $uname, $doc->groups, $home);
        $user->setUber($doc->uber);
        return $user;
    }

    /**
     * Adapt File instance to be saved as a couchdb document
     *
     * @param File $file
     * @return \object
     */
    public function fromFile(File $file) {
        $doc = new \stdClass;
        $doc->_id = 'file:' . $file->getPath();
        $doc->owner = 'user:' . $file->getOwner();
        $doc->permissions = $file->getPermissions();
        return $doc;
    }

    /**
     * Adapt a couchdb document to a File instance
     *
     * @param \object $doc
     * @return File
     */
    public function toFile($doc) {
        $path = \lean\Text::offsetLeft($doc->_id, 'file:');
        $owner = \lean\Text::offsetLeft($doc->owner, 'user:');
        return new File($this->kernel, $path, $owner, $doc->permissions);
    }
}