<?php

namespace App\Controller;

use App\Entity\ParsinId;

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


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
