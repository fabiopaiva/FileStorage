<?php

namespace FileStorage\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="FileStorage")
 */
class Document implements InputFilterAwareInterface {

    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="integer");
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=100);
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=20);
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=20);
     */
    protected $size;

    /**
     * @ORM\Column(type="string");
     */
    protected $filepath;

    /**
     * Não há necessidade de mapear este atributo
     * 
     * @var string
     */
    protected $tmp_name;

    /**
     * @var Zend\InputFilter\InputFilter
     */
    protected $inputFilter;

    /**
     * @param   array   $data
     * @return  Document
     */
    public function __construct($data = null) {
        $this->exchangeArray($data);

        return $this;
    }

    /**
     * Preenche automaticamente essa model com os dados
     * vindos do formulário
     */
    public function exchangeArray($data) {
        if ($data != null) {
            foreach ($data as $attribute => $value) {
                if (!property_exists($this, $attribute)) {
                    continue;
                }
                $this->$attribute = $value;
            }
        }
    }

    /**
     * Retorna este objeto em forma de array
     *
     * @return array
     */
    public function toArray() {
        return get_object_vars($this);
    }

    /**
     * @param InputFilterInterface
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter) {
        throw new \Exception("Not used!");
    }

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter() {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                        'name' => 'content',
                        'required' => true,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }

    /**
     * @param int
     * @return Document
     */
    public function setId($id) {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param string
     * @return Document
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string
     * @return Document
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param int|string
     * @return Document
     */
    public function setSize($size) {
        $this->size = $size;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getSize() {
        return $this->size;
    }

    public function getFilepath() {
        return $this->filepath;
    }

    public function setFilepath($filepath) {
        $this->filepath = $filepath;
        return $this;
    }

    /**
     * @param array $data   Dados para gravar este arquivo
     */
    public function upload(array $data) {
        $fileOpen = fopen($data['tmp_name'], 'r');
        $content = fread($fileOpen, $data['size']);

        $this->exchangeArray($data);
        $this->setContent($content);

        return $this;
    }
    
    public function getDownloadLink() {
        $path = realpath($this->getFilepath());
        $root = $_SERVER['DOCUMENT_ROOT'];
        return str_replace($root, '', $path);
        
    }

}
