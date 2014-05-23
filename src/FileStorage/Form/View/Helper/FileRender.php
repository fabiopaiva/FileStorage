<?php

namespace FileStorage\Form\View\Helper;

use Zend\Form\View\Helper\AbstractHelper;

class FileRender extends AbstractHelper {

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    private $sm;
    protected $validTypes = array(
        'hidden' => true);
    protected $validTagAttributes = array(
        'name' => true,
        'accept' => true,
        'alt' => true,
        'autocomplete' => true,
        'autofocus' => true,
        'checked' => true,
        'dirname' => true,
        'disabled' => true,
        'form' => true,
        'formaction' => true,
        'formenctype' => true,
        'formmethod' => true,
        'formnovalidate' => true,
        'formtarget' => true,
        'height' => true,
        'list' => true,
        'max' => true,
        'maxlength' => true,
        'min' => true,
        'multiple' => true,
        'pattern' => true,
        'placeholder' => true,
        'readonly' => true,
        'required' => true,
        'size' => true,
        'src' => true,
        'step' => true,
        'type' => true,
        'value' => true,
        'width' => true,
    );

    function __construct(\Zend\ServiceManager\ServiceManager $sm) {
        $this->sm = $sm;
    }

    public function __invoke(\Zend\Form\ElementInterface $element = null) {
        return $this->render($element);
    }

    public function render(\FileStorage\Form\Element\File $element) {
        $translator = $this->sm->get('translator');

        $name = $element->getName();
        if ($name === null || $name === '') {
            throw new Exception\DomainException(sprintf(
                    '%s requires that the element has an assigned name; none discovered', __METHOD__
            ));
        }
        $attributes = $element->getAttributes();
        $attributes['name'] = $name;
        $attributes['type'] = 'hidden';
        $attributes['value'] = $element->getValue();
        $html = sprintf(
                '<input %s%s', $this->createAttributesString($attributes), $this->getInlineClosingBracket()
        );
        ;
        if ($element->getValue()) {
            /* @var $document \FileStorage\Entity\Document */
            $document = $this->sm->get('Doctrine\ORM\EntityManager')
                    ->getRepository('FileStorage\Entity\Document')
                    ->find($element->getValue());
            if(!is_file($document->getFilepath())) {
                return $html;
            }
            $is_image = in_array($document->getType(), array(
                'image/png',
                'image/jpeg',
                'image/gif'
            ));

            $html .= '<div class="row">'
                    . '<div class="col-sm-6">'
                    . ($is_image ? '<img src="'.$document->getDownloadLink().'" class="img-responsive"/>' : '')
                    . $translator->translate('Filename')
                    . ': <b>'
                    . $document->getName()
                    . '</b>'
                    . '</div>'
                    . '<div class="col-sm-6">'
                    . '<a href="' . $document->getDownloadLink() . '" target="_blank" class="btn btn-sm btn-info">'
                    . '<span class="glyphicon glyphicon-search"></span> '
                    . $translator->translate('Open file')
                    . '</a>'
                    . '</div>'
                    . '</div>'
                    . $translator->translate('Choose another file to replace')
            ;
        }
        return $html;
    }

}
