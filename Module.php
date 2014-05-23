<?php

namespace FileStorage;

class Module {

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getViewHelperConfig() {
        return array(
            'factories' => array(
                'fileRender' => function($sm) {
            return new \FileStorage\Form\View\Helper\FileRender($sm->getServiceLocator());
        },
            ),
            'invokables' => array(
                'formElement' => 'FileStorage\Form\View\Helper\FormElement',
            )
        );
    }

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

}
