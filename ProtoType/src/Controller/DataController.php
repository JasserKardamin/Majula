<?php

namespace App\Controller;

use App\Entity\Data;
use App\Form\DataType;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpParser\Builder\Method;
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

            return $this->entityManager->createQueryBuilder()
                ->select('a') 
                ->from('App\Entity\Data','a')
                ->orderBy('a.id','DESC')
                ->setMaxResults(8)
                ->getQuery()
                ->getResult();

    }

    private function get_courses(string $value) : array {
            return $this->entityManager->createQueryBuilder()
                ->select('a') 
                ->from('App\Entity\Data','a')
                ->where('a.type = :type')
                ->setParameter('type',$value) 
                ->getQuery()
                ->getResult();
    }

    private function get_courses_title(string $value): array {
        return $this->entityManager->createQueryBuilder()
            ->select('d')
            ->from('App\Entity\Data', 'd')
            ->where('d.title LIKE :searchvalue') 
            ->setParameter('searchvalue', '%' . $value . '%') 
            ->getQuery()
            ->getResult();
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

    #[Route('/data/myposts', name: 'show_data_myposts')]
    public function myposts(): Response
    {
        $query = $this->entityManager->createQuery('SELECT u FROM App\Entity\User u ORDER BY u.id ASC');
        $query->setMaxResults(1);
        $user =  $query->getOneOrNullResult(); 

        $query = $this->entityManager->createQuery('SELECT d FROM App\Entity\Data d WHERE d.user = :user') ; 
        $query->setParameter('user',$user) ; 
        $posts = $query->getResult() ; 

        return $this->render('data/index.html.twig', [
            'data' => $posts,
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


    #[Route('/data/display_all_courses', name: 'display_all_courses')]
    public function Courses(): Response
    {
        $data = $this->get_courses('course') ; 

        return $this->render('data/index.html.twig', [
            'data' => $data,
        ]);
    }


    #[Route('/data/display_all_tds', name: 'display_all_tds')]
    public function Tds(): Response
    {
        $data = $this->get_courses('serie') ; 

        return $this->render('data/index.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/data/display_all_tests', name: 'display_all_tests')]
    public function Tests(): Response
    {
        $data = $this->get_courses('Test') ; 

        return $this->render('data/index.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/data/display_all_Exams', name: 'display_all_exams')]
    public function Exams(): Response
    {
        $data = $this->get_courses('Exam') ; 

        return $this->render('data/index.html.twig', [
            'data' => $data,
        ]);
    }

    #[Route('/data/search', name: 'data_search')]
    public function search(Request $request ): Response
    {
        $data = []; 
        $search_value = '' ;
        
        
        if ($request->isMethod('POST')) {
            $search_value = $request->request->get('search_value');

            if($search_value == '' ) {
                return $this->redirectToRoute('app_data');
            }

            $data = $this->get_courses_title($search_value);
        }
        return $this->render('data/index.html.twig', [
            'data' => $data,
            'searchvalue' => $search_value

        ]);
    }

    #[Route('/data/suggest', name: 'data_suggest')]
    public function suggest(Request $request): JsonResponse
    {
        $query = $request->query->get('query'); 
        $suggestions = $this->get_courses_title($query); 
        $result = array_map(fn($item) => ['title' => $item->getTitle()], $suggestions); 

        return new JsonResponse($result); 
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

