Gravando arquivos no banco de dados com ZF2 e Doctrine
==================

## Descrição

Este projeto consiste em um tutorial cuja finalidade é demonstrar a utilização do [Zend Framework 2](http://framework.zend.com/manual/2.0/en/user-guide/overview.html) com o ORM [Doctrine](https://github.com/doctrine/DoctrineORMModule) efetuando a persistencia de arquivos diretamente no banco de dados.

A configuração da máquina utilizada para realização deste tutorial foi:

* Ubuntu 13.04
* Apache 2.2.22
* MySQL 5.5.29
* PHP 5.4.6
* Git 1.7.10.4

## Preparação do ambiente

#### Obtendo o Zend Framework 2

Este tutorial assume que o local deste projeto será no diretório **/var/www**.

```
cd /var/www
sudo git clone git@github.com:zendframework/ZendSkeletonApplication.git zf2-doctrine
```

## Instalando dependências

##### composer.json

Acrescentar as depencencias referentes ao doctrine no arquivo:

```
"doctrine/doctrine-orm-module": "0.*"
```

Desta forma, o arquivo, ficará da seguinte maneira:

```
{
    "name": "zendframework/skeleton-application",
    "description": "Skeleton Application for ZF2",
    "license": "BSD-3-Clause",
    "keywords": [
        "framework",
        "zf2"
    ],
    "homepage": "http://framework.zend.com/",
    "require": {
        "php": ">=5.3.3",
        "zendframework/zendframework": "dev-master",
        "doctrine/doctrine-orm-module": "0.*"
    }
}
```

Após efetuar as alterações no arquivo composer.json, basta executar o comando:

```
php composer.phar self-update && php composer.phar install
```

## VirtualHost

```
<VirtualHost *:80>
    ServerName zf2-upload-tutorial.local
    DocumentRoot /var/www/zf2-upload-tutorial/public

    SetEnv APPLICATION_ENV "development"
    SetEnv PROJECT_ROOT "/var/www/zf2-upload-tutorial"

    <Directory "/var/www/zf2-upload-tutorial/public">
        DirectoryIndex index.php
        AllowOverride All
        Order allow,deny
        Allow from all
    </Directory>

</VirtualHost>
```

## Hosts

```
echo "127.0.0.1 zf2-upload-tutorial.local" >> /etc/hosts
```

## Database (script para geração da base de dados)

```
DROP DATABASE IF EXISTS zf2;
CREATE DATABASE zf2;
USE zf2;

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(20) NOT NULL,
  `size` varchar(20) NOT NULL,
  `content` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;
```

## Configurando o projeto

#### config/autoload/local.php

```php
<?php
return array(
    'doctrine' => array(
        'connection' => array(
            'orm_default' => array(
                'driverClass' => 'Doctrine\DBAL\Driver\PDOMySql\Driver',
                'params' => array(
                    'host'     => 'localhost',
                    'port'     => '3306',
                    'user'     => 'root',
                    'password' => 'root',
                    'dbname'   => 'zf2',
                ),
            ),
        ),
    ),
);

#### Adicionando o módulo nas configurações da aplicação

```php
<?php
// config/application.config.php
return array(
    // This should be an array of module namespaces used in the application.
    'modules' => array(
        'Application',
        'DoctrineModule',       // Adicionar
        'DoctrineORMModule',    // Adicionar
        'FileStorage',          // Adicionar (módulo que iremos criar)
    ),
    .
    .
    .
```

## Criação do Módulo

Iremos criar um módulo do Zend Framework 2 para que possamos utilizar o Doctrine, portanto, dentro do diretório *zf2-doctrine/module* do projeto, devemos criar a seguinte estrutura de diretório:

```
FileStorage
  src
    FileStorage
      Controller
      Entity
  view
    file-storage
      document
```

Criando a estrutura de diretórios.

```
mkdir FileStorage
mkdir -p FileStorage/config
mkdir -p FileStorage/src/FileStorage/Controller
mkdir -p FileStorage/src/FileStorage/Entity
mkdir -p FileStorage/src/FileStorage/Form
mkdir -p FileStorage/view/file-storage/document
```
    

## FileStorage/Module.php
```php
<?php
namespace FileStorage;

class Module
{
    public function getAutoloaderConfig()
    {
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
    
}
```

## FileStorage/config/module.config.php

```php
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
            'document' => array(
                'type' => 'Segment',
                'options' => array(
                   'route'    => '/document[/:action][/]',
                        'constraints' => array(
                            'action'=> '[a-zA-Z][a-zA-Z0-9_-]*',
                    ),
                    'defaults' => array(
                        'controller' => 'Document',
                        'action'     => 'index',
                    ),
                ),
            ),
            // Download
            'download' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/download[/:id][/]',
                    'defaults' => array(
                        'controller' => 'Document',
                        'action'     => 'download',
                    ),
                ),
            ),
            // Delete a document
            'delete' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/delete[/:id][/]',
                    'defaults' => array(
                        'controller' => 'Document',
                        'action'     => 'delete',
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
```

## FileStorage/autoload_classmap.php
```php
<?php
return array();
```

## FileStorage/Entity/Document.php

```php
<?php
namespace FileStorage\Entity;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface; 

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="documents")
 */
class Document implements InputFilterAwareInterface
{
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
     * @ORM\Column(type="blob");
     */
    protected $content;
    
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
	 * @param 	array	$data
	 * @return	Document
	 */
	public function __construct($data = null)
	{
		$this->exchangeArray($data);
		
		return $this;
	}
	
	/**
	 * Preenche automaticamente essa model com os dados
	 * vindos do formulário
	 */
	public function exchangeArray($data)
	{
	    if ($data != null) {
			foreach ($data as $attribute => $value) {
				if (! property_exists($this, $attribute)) {
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
	public function toArray()
	{
	    return get_object_vars($this);
	}
    /**
     * @param InputFilterInterface
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used!");
    }

    /**
     * @return InputFilterInterface
     */
    public function getInputFilter()
    {
        if (! $this->inputFilter) {
            $inputFilter = new InputFilter();

            $factory = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'content',
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
    public function setId($id)
    {
        $this->id = $id;
        
        return $this;
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param string
     * @return Document
     */
    public function setName($name)
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * @param string
     * @return Document
     */
    public function setType($type)
    {
        $this->type = $type;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
    
    /**
     * @param int|string
     * @return Document
     */
    public function setSize($size)
    {
        $this->size = $size;
        
        return $this;
    }
    
    /**
     * @return int|string
     */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
     * @param string
     * @return Document
     */
    public function setContent($content)
    {
        $this->content = $content;
        
        return $this;
    }
    
    /**
     * @return int|string
     */
    public function getContent()
    {
        return stream_get_contents($this->content);
    }
    
    /**
     * @param array $data   Dados para gravar este arquivo
     */
    public function upload(array $data)
    {
        $fileOpen = fopen($data['tmp_name'], 'r');
        $content  = fread($fileOpen, $data['size']);

        $this->exchangeArray($data);
        $this->setContent($content);
        
        return $this;
    }
    
    /**
     * This method is used to download of this file
     *
     * @param  \Zend\Http\PhpEnvironment\Response
     * @return \Zend\Http\PhpEnvironment\Response
     */
    public function download($response)
    {
        $response->setContent($this->getContent());

        $headers = $response->getHeaders();
        $headers->clearHeaders()
            ->addHeaderLine("Content-length: {$this->getSize()}")
            ->addHeaderLine("Content-type: {$this->getType()}")
            ->addHeaderLine("Content-Disposition: attachment; filename={$this->getName()}");
            
        return $response;
    }
    
}
```

## FileStorage/Form/DocumentForm.php

```php
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
```

## FileStorage/Controller/DocumentController.php

```php
<?php
namespace FileStorage\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel; 
use Doctrine\ORM\EntityManager;

use FileStorage\Entity\Document;

use Doctrine\Common\Collections\ArrayCollection;
use DoctrineModule\Paginator\Adapter\Collection as Adapter;

use FileStorage\Form\DocumentForm;

class DocumentController extends AbstractActionController
{
    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;

    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
 
    /**
     * Return a EntityManager
     *
     * @return Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        if ($this->em === null) {
            $this->em = $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
        }
        
        return $this->em;
    } 
    
    public function indexAction()
    {
        $page = (int) $this->params()->fromRoute('page');

        $paginator = $this->getDoctrinePaginator($page);
                        
        return new ViewModel(array(
            'paginator' => $paginator
        ));
    }
    
    /**
     * Return an instance of Doctrine Paginator
     * 
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private function getDoctrinePaginator($page)
    {
        $entityManager = $this->getEntityManager()
            ->getRepository('FileStorage\Entity\Document');
        
        $query = $entityManager->createQueryBuilder('d')
                               ->setFirstResult($page)
                               ->setMaxResults(10);
        
        $paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query);
        
        return $paginator;
    }
    
    /**
     * Download of a document
     */
    public function downloadAction() 
    {
        $id = (int) $this->params()->fromRoute('id');

        $document = $this->getEntityManager()
            ->getRepository('FileStorage\Entity\Document')
            ->find($id);

        return $document->download($this->getResponse());
    }
    
    /**
     * Remove a document through your id
     */
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id');
        
         $document = $this->getEntityManager()
            ->getRepository('FileStorage\Entity\Document')
            ->find($id);

        if ($document != null) {
            $this->getEntityManager()->remove($document);
            $this->getEntityManager()->flush();
        }
        
        return $this->redirect()->toRoute('document');
    }
             
    /**
     * Store a file into database.
     *
     * @return array
     */
    public function createAction()
    {
        $form = new DocumentForm();

        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $document = new Document();

            $form->setInputFilter($document->getInputFilter());
            
            $post = array_merge_recursive(
                $this->getRequest()->getPost()->toArray(),
                $this->getRequest()->getFiles()->toArray()
            );

            $form->setData($post);

            if ($form->isValid()) {
                $document->upload($post['content']);
                
                $this->getEntityManager()->persist($document);
                $this->getEntityManager()->flush();

                return $this->redirect()->toRoute('document');
            }
        }

        return array('form' => $form);
    }
    
}
```

## FileStorage/view/file-storage/create.phtml

```php
<?php
$form = $this->form;

$form->setAttribute(
    'action',
    $this->url(
        'document',
        array(
            'controller'=>'document', 
            'action' => 'create'
        )
    )
); 

$form->prepare();
?>

<div class="well">
    <?php echo $this->form()->openTag($form); ?>

    <div class="row-fluid">
        <div class="span4">
            <?php echo $this->formRow($form->get('content')); ?>
        </div>
    </div>
</div>      

<div class="form-actions">
    <?php echo $this->formSubmit($form->get('submit')); ?>
</div>       
<?php echo $this->formRow($form->get('id')); ?>
<?php echo $this->form()->closeTag(); ?>
```

## FileStorage/view/file-storage/index.phtml

```php
<?php if (count($this->paginator)): ?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12 conteudo">
			<div class="dados">
		
				<table class="table table-striped table-bordered">
				    <thead>
				        <tr>
							<th class="span1">ID</th>
				           	<th class="span4">Name</th>
							<th class="span4">Download</th>
				        </tr>
				    </thead>
				    <tbody>
                        <?php foreach ($this->paginator as $document): ?>
				        <tr>
				            <td><?php echo $document->getId(); ?></td>
				            <td><?php echo $document->getName(); ?></td>
				            <td>
				                <a href="<?php echo $this->url('download', array(
                                        'action'=>'download', 
                                        'id' => $document->getId(),
                                ));?>">Download</a> | 
                                <a href="<?php echo $this->url('delete', array(
                                        'action'=>'delete', 
                                        'id' => $document->getId(),
                                ));?>">Delete</a>
                            </td>
				        </tr>
				        <?php endforeach; ?>
				    </tbody>
				</table>
				
			</div>
		</div>
	</div>
</div>

<a href="<?php echo $this->url('document', array('action'=>'create'));?>" class="btn-primary btn">New</a>
<?php endif; ?>
```

## Testando as funcionalidades

#### Efetuando o upload de um arquivo
* http://zf2-upload-tutorial.local/document/create

#### Listando os arquivos cadastrados
* http://zf2-upload-tutorial.local/document
