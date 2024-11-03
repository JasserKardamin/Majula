<?php

namespace App\Controller;

use App\Entity\Data;
use App\Form\DataType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
//use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class DataController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    private function get_data_page() : array {
        {
            return $this->entityManager->createQueryBuilder()
                ->select('a') 
                ->from('App\Entity\Data','a')
                ->setMaxResults(8)
                ->getQuery()
                ->getResult();
        }
    }
    
    #[Route('/data', name: 'app_data')]
    public function index(): Response
    {
        $data = $this->get_data_page() ; 

        return $this->render('data/index.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/data/add', name: 'add_data')]
    public function add(Request $request ): Response
    {
        $data = new data() ;
        $form = $this->createForm(Datatype::class,$data);
        $form->handleRequest($request) ; 

        if($form->isSubmitted() && $form->isValid()) {
            //$otherentity = $form->getData('') ; 
            $data->setLikes(0) ; 

            $currecnt_date = new DateTime() ; 
            $data->setPublicationdate($currecnt_date) ; 
            $data->setApproved(0) ; 
            $data->setAction("published") ;


            $query = $this->entityManager->createQuery('SELECT u FROM App\Entity\User u ORDER BY u.id ASC');
            $query->setMaxResults(1);
            $user =  $query->getOneOrNullResult(); 
            $data->setUser($user);


            $this->entityManager->persist($data);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('app_data');
        }

        return $this->render('data/add.html.twig', [
             'form' => $form->createView(),
        ]);
        

    }

    #[Route('/data/details/{id}', name: 'show_data_details')]
    public function details(?int $id = null): Response
    {
        $data = $this->entityManager->getRepository(Data::class)->findAll();

        return $this->render('data/index.html.twig', [
            'data' => $data,
        ]);
    }

    /* later : 
    #[Route('/data/{page}', name: 'load_more_data')]
    public function loadMoreData(int $page = 1): JsonResponse
    {
        $limit = 8; 
        $data = $this->entityManager->getRepository(Data::class)->findBy([], null, $limit, ($page - 1) * $limit);

        $response = [];
        foreach ($data as $element) {
            $response[] = [
                'id' => $element->getId(),
                'name' => $element->getTitle(),
                'price' => $element->getLikes(),
                'imageUrl' => $element->getImageUrl(), 
            ];
        }

        return new JsonResponse($response);
    }
    */

}

