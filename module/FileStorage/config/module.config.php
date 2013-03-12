<?php
namespace FileStorage;

return array(

    // Controllers in this module
    'controllers' => array(
        'invokables' => array(
            'FileStorage\Controller\Document' => 'FileStorage\Controller\DocumentController'
        ),
    ),

    // Routes for this module
    'router' => array(
        'routes' => array(
            // Documents
            'document' => array(
                'type' => 'Segment',
                'options' => array(
                   'route'    => '/document[/:action]',
                        'constraints' => array(
                            'action'=> '[a-zA-Z][a-zA-Z0-9_-]*',
                        ),
                    'defaults' => array(
                        'controller' => 'FileStorage\Controller\Document',
                        'action'     => 'index',
                    ),
                ),
            ),
            // Download
            'download'  => array(
                'type' => 'Segment',
                'options' => array(
                   'route'    => '/download[/:id]',
                        'constraints' => array(
                            'action'=> '[0-9]+',
                        ),
                    'defaults' => array(
                        'controller' => 'FileStorage\Controller\Document',
                        'action'     => 'download',
                    ),
                ),
            ),
        ),
    ),

    // View setup for this module
    'view_manager' => array(
        'template_path_stack' => array(
            'FileStorage' => __DIR__ . '/../view',
        ),
    ),

    // Doctrine configuration
    'doctrine' => array(
        'driver' => array(
            __NAMESPACE__ . '_driver' => array(
                'class' => 'Doctrine\ORM\Mapping\Driver\AnnotationDriver',
                'cache' => 'array',
                'paths' => array(__DIR__ . '/../src/' . __NAMESPACE__ . '/Entity')
            ),
            'orm_default' => array(
                'drivers' => array(
                    __NAMESPACE__ . '\Entity' => __NAMESPACE__ . '_driver'
                ),
            ),
        ),
    ),

);
