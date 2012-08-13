<?php
namespace stack\web;
/*
 * Copyright (C) 2012 Michael Saller
 * Licensed under MIT License, see /path/to/stack/LICENSE
 */

class Application extends \stack\Application {

    /**
     * @var Request
     */
    private $request;

    /**
     * @var \lean\Session
     */
    private $session;

    /**
     * @var \stack\module\User
     */
    private $user;

    /**
     * Override parent constructor to start session
     *
     * @param \stack\Context $context
     */
    public function __construct(\stack\Context $context) {
        parent::__construct($context);
        $this->session = new \lean\Session('stack.web.application');
        $context->getEnvironment()->setDefaultSettings($this->getDefaultSettings());
    }

    /**
     * Register login module
     */
    public function init() {
        parent::init();
        (new \stack\Bundle_Web())->registerModules($this->getShell());
    }

    /**
     * Run the web application.
     * If target is '/login', try to log in the user.
     * Elsewise, get the user from the filesystem and execute the module inside
     *
     * @param $target
     */
    public function run() {
        $request = new Request();

        // push user security if user is logged in, unpriviledged otherwise
        if($this->session->user) {
            // push user priv
        }
        else {
            $this->getContext()->pushSecurity(new \stack\security\AnonymousSecurity());
        }
        try {
            // get file
            $file = $this->getShell()->readFile($request->getPath());

            // init module arguments
            $args = func_get_args();
            array_shift($args); // shift off param $path
            array_unshift($args, $request); // unshift request as new second argument
            array_unshift($args, $this->getContext()); // unshift context as new first argument

            // run module
            $response = call_user_func_array(array($file->getModule(), 'run'), $args);
            if(!$response instanceof Response) {
                throw new \stack\Exception('Malformed response');
            }
        }
        catch(\stack\fileSystem\Exception_FileNotFound $e) {
            if($this->getEnvironment()->get('debug')) {
                throw $e;
            } else {
                $response = new Response_HTTP404("File at " . $request->getPath() . " not found");
            }
        }
        catch(\Exception $e) {
            if($this->getEnvironment()->get('debug')) {
                if($request->isXHR()) {
                    throw $e;
                }
            } else {
                $response = new \stack\web\Response(500, 'Internal Server Error.');
            }
        }
        $this->getContext()->pullSecurity();
        $response->send();
    }

    /**
     * Save user to session
     *
     * @param \stack\module\User $user
     */
    protected function setUser(\stack\module\User $user) {
        $this->session->user = $user;
    }

    /**
     * Retrieve user from session
     *
     * @return \stack\module\User
     */
    public function getUser() {
        return $this->session->user;
    }

    /**
     * Get default settings
     *
     * @return array default settings
     */
    protected function getDefaultSettings() {
        $settings = array();
        // environment
        $settings['stack.environment.name'] = 'development';
        $settings['stack.environment.file'] = STACK_APPLICATION_ROOT . '/config/environment.ini';
        // templates
        $settings['stack.template.directory'] = STACK_APPLICATION_ROOT . '/template';
        $settings['stack.template.document.directory'] = STACK_APPLICATION_ROOT . '/template/document';
        $settings['stack.template.layout.directory'] = STACK_APPLICATION_ROOT . '/template/layout';
        $settings['stack.template.view.directory'] = STACK_APPLICATION_ROOT . '/template/view';
        $settings['stack.template.partial.directory'] = STACK_APPLICATION_ROOT . '/template/partial';
        return $settings;
    }

    /**
     * Get an application setting
     *
     * @param string $setting
     * @return mixed setting value
     * @throws Exception
     */
    public function getSetting($setting) {
        return $this->environment->get($setting);
    }
}