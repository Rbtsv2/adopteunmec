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
     * lat et lon definissent la position de l'utilisateur qui souhaite afficher les clients sur la carte
     * return une liste de client d'un perimetre de 8 kilometres en fonction de la position de l'utilisateur
     * @Route("/users/{lat}/{lon}", name="users")
     * @return Response
     */
    public function getClientsOnScreenLocate(Request $request, $lat, $lon)
    {
         $result = $this->em->getRepository('App:ParsingId')->getScreenLocateIds($lat,$lon); ///api/users/43.60436/1.44295 [toulouse]
         return new JsonResponse($result);
        
    }

    /**
     * return les coordonnées d'un client
     * @route("/user/locate/{id}", name="client_locate")
     * @return Response
     */
    public function getLatLonFromOneClient($id)
    {
       
        $results = $this->em->getRepository('App:ParsingId')->getId($id);

        // deserialize DC2Type to array
        foreach ($results as $result) {
            $data = unserialize($result["data"]);

            $tabResult[] = [
                'lat'        => $data['sideColumn']['map']["coords"]["memberLat"], // On le transforme en tableau
                'lon'        => $data['sideColumn']['map']["coords"]["memberLng"],
                'created'    => $result['created_at'] 
            ];

        }
       
        return new jsonResponse($tabResult);
    }


    /**
     * @Route("/similar/{id}", name="similar")
     */
    public function similar(Request $request, $id)
    {  
        $result = $this->adopteService->exctractAndCompareApi($id);
        return new jsonResponse($result);
    }


    /**
     * @Route("/search", name="Api_search")
     */
    public function apiSearch(Request $request)
    {  

        $result =  json_decode($request->request->get('data'),true);
        $chaine_de_recherche = $result["data"][0]["search"];
        $range = $result["data"][0]["range"];  
        $genre = $result["data"][0]["genre"]; 

       
        $mots = explode(" ", $chaine_de_recherche); // on sépare la chaine de recherche
        $mots[1] = isset($mots[1]) ? $mots[1] : '';
        $nombre_de_mot = count($mots); // la requette dependra du nombre de mot et construira la requette
        $result = $this->em->getRepository('App:ParsingId')->getSearch($mots, $range, $genre);

        return new jsonResponse($result);
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

    /**
     *  @Route("/proxy", name="proxy")
     */
    public function proxy() {
        return new JsonResponse($this->adopteService->getProxy2());
    }


}
