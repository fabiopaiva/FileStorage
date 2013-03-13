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
