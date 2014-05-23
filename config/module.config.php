<?php

namespace FileStorage;

return array(
    // Controllers in this module
    'controllers' => array(
        'invokables' => array(
            'Document' => 'FileStorage\Controller\DocumentController'
        ),
    ),
    // Routes for this module
    'router' => array(
        'routes' => array(
            // Documents
            'file-storage' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/filestorage',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Document',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    // Delete a document
                    'delete' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/delete[/:id][/]',
                            'defaults' => array(
                                'controller' => 'Document',
                                'action' => 'delete',
                            ),
                        ),
                    ),
                )
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
