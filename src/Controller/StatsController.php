<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use App\Service\AdopteService;

use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class StatsController extends AbstractController
{

    public function __construct(EntityManagerInterface $em,AdopteService $adopteService)
    {
        $this->em = $em;
        $this->adopteService = $adopteService;
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        
        return $this->render('home/index.html.twig');
    }

    /**
     * @Route("/maps", name="maps")
     */
    public function maps()
    {
        return $this->render('home/maps.html.twig'); 
    }

     /**
     * @Route("/search", name="search")
     */
    public function search()
    {
        $result = $this->em->getRepository('App:ParsingId')->getRecentsIds();
        return $this->render('home/search.html.twig', ['users' => $result]); 
    }
    /**
     * @Route("/stats", name="stats")
     */
    public function stats()
    {

        $result = $this->em->getRepository('App:ParsingId')->getAllIds();
        return $this->render('home/stats.html.twig', ['users' => $result]);        
    }

    /**
     * @Route("/product/{id}", name="product")
     * @Route("/product/{id}/{save}", name="product_save")
     */
    public function product($id, $save =null)
    {
        // si il y a un parametre save renseigné alors on l'affiche solution temporaire
        if ($save) {
            // on recuperer le profil save cible pour l'afficher
            $results = $this->em->getRepository('App:Informations')->getOneData($save);
            
            $tabResult[] = [
                'id_save'         => $results[0]["id"],
                'data'       => unserialize($results[0]["data"]),
                'created_at' => $results[0]['created_at']  
            ];

            if ($results) {
                return $this->render('user_save.html.twig', ['user' => $tabResult]);
            } else {
                var_dump('no data, its possible this profile are closed or wait');
                die;
            }
        }
        
        $results = $this->em->getRepository('App:ParsingId')->getId($id);

        // deserialize DC2Type to array
        foreach ($results as $result) {
                   
            $tabResult[] = [
                'id_save'         => $result["id"],
                'data'       => unserialize($result["data"]),
                'created_at' => $result['created_at']  
            ];

        }
        if ($results) {
            return $this->render('user.html.twig', ['user' => $tabResult]);
        } else {
            var_dump('no data, its possible this profile are closed or wait');
            die;
        }
        //print_r($result);
        //die;
        // Convert array -> to string -> to object
        //$result =  (json_decode(json_encode($result)));

        
    }

    

    /**
     *  @Route("/proxy", name="proxy")
     */
    public function proxy() {
        return  $this->adopteService->getProxy();
    }

    /**
     * route privée qui active le worker app:parse
     * @Route("/user/worker", name="user.worker")
     */
    public function userWorker()
    {
        return $this->render('user/worker.html.twig');
    }

    /**
     * route privée qui active le worker app:parse
     * @Route("/user/worker", name="user.account")
     */
    public function userAccount()
    {
        return $this->render('user/account.html.twig');
    }

    /**
     * route privée qui active le worker app:parse
     * @Route("/user/worker", name="user.billing")
     */
    public function userBilling()
    {
        return $this->render('user/billing.html.twig');
    }


    /**
     * Login a user or fallback to the form.
     * @Route("/login", name="login")
     * @param AuthenticationUtils $authenticationUtils
     * @param AuthorizationCheckerInterface $authChecker
     * @return Response
     */
    public function login(
        AuthenticationUtils $authenticationUtils,
        AuthorizationCheckerInterface $authChecker
    ) : Response
    {
         if (false !== $authChecker->isGranted('ROLE_ADMIN') || false !== $authChecker->isGranted('ROLE_USER'))
             return $this->redirectToRoute('user.dashboard');

        return $this->render('user/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
    }


}
