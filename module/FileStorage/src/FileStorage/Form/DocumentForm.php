<?php
namespace FileStorage\Form;

use Zend\Form\Form;

class DocumentForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('Document');
        
        $this->setAttribute('method', 'post');
        
        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        
        $file = new \Zend\Form\Element\File('content');
        $file->setLabel('File')
             ->setAttribute('id', 'content');
        $this->add($file);
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Upload',
                'id' => 'submitbutton',
            ),
        ));
    }
}
