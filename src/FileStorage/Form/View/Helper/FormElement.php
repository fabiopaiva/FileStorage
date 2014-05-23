<?php

namespace FileStorage\Form\View\Helper;

use Zend\Form\ElementInterface;
use Zend\Form\View\Helper\FormElement as FE;
use FileStorage\Form\Element\File;
use FileStorage\Form\View\Helper\FileRender;

class FormElement extends FE {

    public function render(ElementInterface $element) {
        if ($element instanceof File) {
            $renderer = $this->getView();
            $helper = $renderer->plugin('fileRender');
            return $helper($element);
        }
        return parent::render($element);
    }

}
