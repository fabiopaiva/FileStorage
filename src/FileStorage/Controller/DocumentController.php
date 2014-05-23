<?php
namespace FileStorage\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel; 
use Doctrine\ORM\EntityManager;

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
        
        return $this->redirect()->toRoute('file-storage');
    }
    
}    
