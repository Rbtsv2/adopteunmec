<?php

namespace App\Controller;

use App\Entity\ParsinId;
use App\Service\AdopteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
/**
 * @Route("/api")
 */
class ApiController extends AbstractController
{


    public function __construct(EntityManagerInterface $em, AdopteService $adopteService)
    {
        $this->em = $em;
        $this->adopteService = $adopteService;
    }

    /**
     * @Route("/users/{lat}/{lon}", name="users")
     * @return Response
     */
    public function index(Request $request, $lat, $lon)
    {
         $result = $this->em->getRepository('App:ParsingId')->getScreenLocateIds($lat,$lon); ///api/users/43.60436/1.44295 [toulouse]
         return new JsonResponse($result);
        
    }


    /**
     * @Route("/similar/{id}", name="similar")
     */
    public function similar(Request $request, $id)
    {  
 
        $result = $this->adopteService->exctractAndCompareApi($id);
        var_dump($result);
        die;
       
    }


    /**
     * route privée qui active le worker app:parse
     * @Route("/user/worker/{etat}", name="play")
     */
    public function playWorker($etat)
    {
        if ($etat) {
                // activation du worker
        } else {
                //Desactivation du worker
        }

        return new JsonResponse();

    }


}
