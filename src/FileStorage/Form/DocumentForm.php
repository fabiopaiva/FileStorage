<?php

namespace FileStorage\Form;

use Zend\Form\Fieldset;
use Zend\InputFilter\InputFilterProviderInterface;
use FileStorage\Entity\Document;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use Zend\Form\Element\Image;

class DocumentForm extends Fieldset implements InputFilterProviderInterface {

    /**
     * \Zend\ServiceManager\ServiceManager
     * @var 
     */
    private $sm;

    /**
     * Name of the field is required
     * @param string $name
     * @param \Zend\ServiceManager\ServiceManager $sm
     * @param string $destinationFolder
     */
    public function __construct($name, \Zend\ServiceManager\ServiceManager $sm, $destinationFolder = 'public/fileStorage') {
        $this->sm = $sm;
        if (!is_writable($destinationFolder)) {
            throw new \Exception('Destination folder is not writable: ' . $destinationFolder);
        }
        parent::__construct($name);
        $this
                ->setObject(new Document())
                ->setHydrator(new DoctrineObject($sm->get('Doctrine\ORM\EntityManager')))
                ->add(array(
                    'name' => 'file',
                    'type' => 'file',
                    'options' => array(
                        'label' => 'File'
                    ),
                    'attributes' => array(
                        'class' => 'form-control input-sm'
                    )
                ))
                ->add(array(
                    'name' => 'name',
                    'type' => 'hidden'
                ))
                ->add(array(
                    'name' => 'type',
                    'type' => 'hidden'
                ))
                ->add(array(
                    'name' => 'size',
                    'type' => 'hidden'
                ))
                ->add(array(
                    'name' => 'filepath',
                    'type' => 'hidden'
                ))
                ->add(array(
                    'name' => 'id',
                    'type' => 'FileStorage\Form\Element\File'
                ))
        ;
        /* @var $request Zend\Http\PhpEnvironment\Request */
        $request = $this->sm->get('Request');
        if ($request->isPost()) {
            $file = $request->getFiles()->get($name);
            if ($file && is_file($file['file']['tmp_name'])) {
                $params = $file['file'];
                $newName = $destinationFolder
                        . DIRECTORY_SEPARATOR
                        . uniqid()
                        . '_'
                        . $params['name'];
                move_uploaded_file($params['tmp_name'], $newName);
                /* @var $newParams \Zend\Stdlib\Parameters */
                $newParams = $request->getPost();
                $newParams->set($name, array(
                    'name' => $params['name'],
                    'type' => $params['type'],
                    'size' => $params['size'],
                    'filepath' => $newName
                ));
            }
        }
    }

    public function getInputFilterSpecification() {
        return array();
    }

}
