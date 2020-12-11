<?php

namespace App\Service;

use App\Notifications\EmailNotification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;

use App\Entity\Informations;
use App\Entity\ParsingId;

class AdopteService
{
   
    const LOGO = "
    _______ _________                _____       \r
    ___    |_\e[0;34;40mRbtS\e[0m_  /______ ________ __  /______ \r
    __  /| |_  __  / _  __ \___  __ \_  __/_  _\e[0;34;40mv3.0\e[0m \r
    _  ___ |/ /_/ /  / /_/ /__  /_/  / /_  /  __/\r
    /_/  |_|\__,_/   \____/ _  .___/ \__/  \___/ \r
                           /_/                   \r
        ";

    const API_URI          = 'https://geo.api.gouv.fr/communes?codeDepartement=';
    const TARGET           = 'https://www.adopteunmec.com/gogole?q=';
    const URI_CONNECTION   = 'https://www.adopteunmec.com//auth/login';
    const AGENT            = 'Mozilla/5.0';
    const COOKIE           = 'cookie.txt';
    const ERROR_CONNECTION = 'Check your connection';
    const PROFILE_FILTER   = "#/profile/(?!me)#i";
    const PROFILE          = "https://www.adopteunmec.com/profile/";
    const PROXY            = "http://pubproxy.com/api/proxy?country=FR&post=true&format=json";

    private $_version      = '3.0.0';
    private $_postfields   = array();
    private $_compteur     = 0;
    private $_time         = 3; //2000000 (2secondes) à tester usleep(2000000);
    private $em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }


    public function getProfilsWhitoutInformations($login, $password, $output) {
    
        $output->writeln(self::LOGO, OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Start payload Save', OutputInterface::VERBOSITY_NORMAL);

        $result = $this->em->getRepository('App:ParsingId')->findAllIdsWithoutInformations();


        $tab = [];
        foreach ($result as $key) {
             $tab[] = self::PROFILE . $key ['urlid'];
        }

        $this->parsingProfile($login, $password, $tab, $output, true);

    }


    public function getStart($login, $password, $code, $search, $output, $isonline) 
    {

        $output->writeln(self::LOGO, OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Start payload ', OutputInterface::VERBOSITY_NORMAL);
       

        if (!empty($search)) {
            $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [DEBUG] critère(s) specifié(s) : '. $search . '</> ', OutputInterface::VERBOSITY_NORMAL);
        }
     
      
        foreach ($this->getJson($code, $output) as $code) {
            $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Ville : ' . $code, OutputInterface::VERBOSITY_NORMAL);
            $result = $this->filterUrlsInPage($login, $password, $code, $search, $isonline, $output);

            $this->parsingProfile($login, $password, $result, $output);

        }

        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> End payload ', OutputInterface::VERBOSITY_NORMAL);

    }


    public function getJson($code, $output  = null)
    {

        $data = @file_get_contents(self::API_URI . $code);
        $contents = json_decode($data, true);
        shuffle($contents);
      
        if (!$contents) 
        {
           $output->writeln('<error>[' . date('Y-m-d H:i:s') . '] [ERROR] ' . self::ERROR_CONNECTION . '</>' , OutputInterface::VERBOSITY_NORMAL);
           exit();
        }
        foreach ($contents as $content) {
                yield ($content['nom']);
        }

    }

    public function getUrlsInPage($login, $password, $code, $search, $isonline, $output)
    {
        $isonline = ($isonline)?'online':'';
        $url = self::TARGET . $code . ' ' . $search . ' ' . $isonline ;
        $content = $this->curl($login, $password, $url, $output);
        $crawler = new Crawler($content);
        return $crawler->filterXPath('//a'); 
    }

    public function filterUrlsInPage($login, $password, $code, $search, $isonline, $output)
    {
        $content = $this->getUrlsInPage($login, $password, $code, $search, $isonline, $output);
        //var_dump($content);
        $urls = array();
        foreach ($content as $key) {
            if($key->getAttribute('href')) {
                $href = $key->getAttribute('href');

                var_dump($href);
            }

            if (preg_match(self::PROFILE_FILTER, $href)) {
                $urls[] = $href;
            }
        }

        if (empty($urls)) {
            $output->writeln('<error>[' . date('Y-m-d H:i:s') . '] [ERROR] : Vérifier vos identifiants login password, nous ne pouvons recuperer aucuns profils !</>' , OutputInterface::VERBOSITY_NORMAL);
            exit();
        } else {
            $result = array_unique($urls);
            // $output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [DEBUG] : '. print_r($result) .' </>', OutputInterface::VERBOSITY_DEBUG); 
        }

        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [NOTICE] : '. count($result) .' profils ont été récupérés sur la page ! </>', OutputInterface::VERBOSITY_VERY_VERBOSE);
        return $result;
    }

    public function parsingProfile($login, $password, $result, $output, $save = false)
    {

        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [Démarrage du scan : ' . count($result) . ' profil(s) à parcourir </>', OutputInterface::VERBOSITY_NORMAL); 
        $progress = new ProgressBar($output, count($result));
        $output->writeln("\r\n");    
        foreach ($result as $url) {

            $progress->advance();
            $output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [DEBUG] Récuperation du profile ' . $url . '</>', OutputInterface::VERBOSITY_VERY_VERBOSE);

            $content = $this->curl($login, $password, $url, $output);

            $output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [DEBUG] Profile visité ' . $url . '</>', OutputInterface::VERBOSITY_DEBUG);


            $data = $this->getAdopteApi($content, $output, $save);

            if (!empty($data)) {
               $this->saveIdsFromOneProfile($data, $output); 
               //$this->getSecureTime($output);
            } else {

                // détection kick fraude  
                $this->kickDetection($content, $output);

                $urlTab = parse_url($url);
                $path   = $urlTab['path'];
                $id     = str_replace("/profile/","",$path);
                $user   = $this->em->getRepository('App:ParsingId')->findOneBy(array('urlid' =>$id));
                $user->setIsActive(0);
                $this->em->flush();
                $output->writeln('<action>[NOTICE] Profile is close : ' . $id . '</>', OutputInterface::VERBOSITY_NORMAL);
            }
                
            
        }
        $progress->finish();
    }


    public function kickDetection($content, $output)
    {
        if (preg_match('/Profil introuvable/', $content)) {
            $output->writeln('<error>[' . date('Y-m-d H:i:s') . '] [ERROR] Votre compte a été bloqué </>', OutputInterface::VERBOSITY_NORMAL);
            die;
        }
    }

    public function getSecureTime($output, $microTimeMin = 20000000, $microTimeMax = 30000000)
    {
        $microTime = rand($microTimeMin, $microTimeMax);

        // convert to second 
        $second = round(($microTime / 1000000), 2);
        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [Notice] Time sleeping : </notice> <vip>' .  $second . ' secondes </vip></>', OutputInterface::VERBOSITY_NORMAL);
        usleep($microTime);

    }

    public function saveIdsFromOneProfile($data, $output) 
    {
       //On recupere tous les ids de l'api du profil dans la section random profil
        $randomMembers = $data['sideColumn']['randomMembers']['members'];

        foreach ($randomMembers as $key) {
        
            $user = $this->em->getRepository('App:ParsingId')->findOneBy(array('urlid' =>$key['id']));
                 
            if (!$user) {

                $output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [ACTION] fast pick up : <vip>' .  $key['pseudo'] . '</vip></>', OutputInterface::VERBOSITY_NORMAL);
                $saveId = new ParsingId;
                $saveId->setUrlid( $key['id'] );
                $saveId->setCity( $key['city'] );
                $saveId->setPseudo( $key['pseudo'] );
                $saveId->setAge( $key['age'] );
                $saveId->setSexe(NULL);  
           
                $this->em->persist($saveId);
                $this->em->flush();     
            }
        }
    }


    public function saveInformations($user, $data, $output = false) 
    {
        if ($output != false) {
            $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [ACTION] save credentials : </notice> <vip>' .  $data['member']['pseudo'] . '</vip></>', OutputInterface::VERBOSITY_NORMAL);
        }
       
        if ($data['member']['id'] != null) { 

            $informations = new Informations();
            $informations->setData($data);
            $informations->setRelation($user);
            $informations->setCreatedAt(new \DateTime('now'));
            $user->addInformation($informations);
            $user->setIsInfo(1);
    
            $sexe = $this->isWomanOrMan($data, $output);
            $user->setSexe($sexe);
            $this->em->persist($user);
            $this->em->flush();     
        }    

    }

    public function newSave($data, $output) 
    {
        $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [ACTION] save new profile : </notice> <vip>' .  $data['member']['pseudo'] . '</vip></>', OutputInterface::VERBOSITY_NORMAL);

        if ($data['member']['id'] != null) {
            $saveId = new ParsingId;
            $saveId->setUrlid( $data['member']['id'] );
            $saveId->setCity( $data['member']['city'] );
            $saveId->setPseudo( $data['member']['pseudo'] );
            $saveId->setAge( $data['member']['age'] );
            $saveId->setAvatar($data['member']['cover']);
            $saveId->setLat($data['sideColumn']['map']['coords']['memberLat']);
            $saveId->setLng($data['sideColumn']['map']['coords']['memberLng']);

            // fonction qui determine si Homme ou Femme
            $sexe = $this->isWomanOrMan($data, $output);
            $saveId->setSexe($sexe);
            
            $informations = new Informations();

            $informations->setData($data);
            $informations->setRelation($saveId->getId());
            $informations->setCreatedAt(new \DateTime('now'));
            $saveId->addInformation($informations);

            $this->em->persist($saveId);
            $this->em->flush();     
        }    

    }

    public function saveProfil($data, $output, $save) 
    {

        $user = $this->em->getRepository('App:ParsingId')->findOneBy(array('urlid' => $data['member']['id']));

        if ($save) { // si true alors on ne fait que recuperer les infos du profil
            
            $this->saveInformations($user, $data, $output);
        } 
        else {
            if (!$user) {
                $this->newSave($data, $output); 
                // time secure ici 
            } else {
                $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [NOTICE] This profil always existe (maybe need Maj) : </notice>', OutputInterface::VERBOSITY_NORMAL);
            }
        }
    }

    /**
     * return true if similar
     */
    public function isSimilar($oldData, $newData)
    {
        similar_text($oldData, $newData, $percent);
        // patch pour similar_text() renvoi false pour 2 chaines vides ce qui n'est pas normal..
        if ($oldData === '' && $newData === '') {
            return true;
        }else {
           return ($percent != 100)? FALSE : TRUE; 
        }
    }

   
    /**
     * return true 'mise à jour du profile'
     * return false 'aucune mise à jour necessaire'
     * return une execption le profil n'existe plus
     */
    public function exctractAndCompareApi($id)
    {
        $url = self::PROFILE . $id;
        $content = $this->curl("jeuleberne45@gmail.com", "ezUZAedh54e574", $url);
        $data = $this->extractApi($content); // on format serializé ? 
        if (!$data) {
            var_dump('the profile has deleted their account');
            die;
        }
        ////// DONNE EN BASE ///////////
        $results = $this->em->getRepository('App:ParsingId')->getId($id);
        $oldDonne = unserialize($results[0]["data"]);

        // var_dump($oldDonne['member']['pseudo']);
        // var_dump($oldDonne['member']['age']);
        // var_dump($oldDonne["mainColumn"]["Description"]["data"][0]["value"]);
        // var_dump($oldDonne["mainColumn"]["Shopping List"]["data"][0]["value"]);
        // var_dump($oldDonne["sideColumn"]["map"]["coords"]["memberLat"]);
        // var_dump($oldDonne["sideColumn"]["map"]["coords"]["memberLng"]);


        // var_dump('-----------------------------------------------');


        // var_dump($data['member']['pseudo']);
        // var_dump($data['member']['age']);
        // var_dump($data["mainColumn"]["Description"]["data"][0]["value"]);
        // var_dump($data["mainColumn"]["Shopping List"]["data"][0]["value"]);
        // var_dump($data["sideColumn"]["map"]["coords"]["memberLat"]);
        // var_dump($data["sideColumn"]["map"]["coords"]["memberLng"]);


        $pseudo_compare      = $this->isSimilar($data['member']['pseudo'], $oldDonne['member']['pseudo']);
        $age_compare         = $this->isSimilar($data['member']['age'], $oldDonne['member']['age']);
        $description_compare = $this->isSimilar($data["mainColumn"]["Description"]["data"][0]["value"], $oldDonne["mainColumn"]["Description"]["data"][0]["value"]);
        $recherche_compare   = $this->isSimilar($data["mainColumn"]["Shopping List"]["data"][0]["value"], $oldDonne["mainColumn"]["Shopping List"]["data"][0]["value"]);
        
        $lat_compare         = $this->isSimilar($data["sideColumn"]["map"]["coords"]["memberLat"], $oldDonne["sideColumn"]["map"]["coords"]["memberLat"]);
        $lng_compare         = $this->isSimilar($data["sideColumn"]["map"]["coords"]["memberLng"], $oldDonne["sideColumn"]["map"]["coords"]["memberLng"]);


        $results = [$pseudo_compare,$age_compare, $description_compare, $recherche_compare,$lat_compare, $lng_compare];

        // $test =  $this->isSimilar('2', '');
        // var_dump($results);
        // var_dump($test);

        foreach ($results as $result) {
            if ($result === false) {

                $user = $this->em->getRepository('App:ParsingId')->findOneBy(array('urlid' => $data['member']['id']));
                // on enregistre les nouvelles données
                $this->saveInformations($user, $data);
                //mettre à jour les infos de parsing_id (à faire)

                return true;
            }
        }

        return false;
    }



    public function extractApi($content)
    {
        $re = '/\{"isOpenProfile":(.*)\}/m';
        @preg_match_all($re, $content, $matches, PREG_SET_ORDER, 0);
        $data_json = @$matches[0][0];
        $data = json_decode($data_json, true); 
        return $data; 
    }

    public function getAdopteApi($content, $output, $save)
    {

        $data = $this->extractApi($content);

        // gère le : OUPS Cette utilisatrice n'existe plus (peut-être à supprimer de la base ?)
        if (empty($data)) {
            return;
        }
     
        //$output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [DEBUG] json Api ' . var_dump($data_json) . '</>', OutputInterface::VERBOSITY_DEBUG);
      
        $pseudo    =  $data['member']['pseudo'];
        $age       =  $data['member']['age'];
        $ville     =  $data['member']['city'];
        $isconnect =  $data['member']['online'];
   
        $output->writeln('<notice> [NOTICE]</> pseudo : <vip>' . $pseudo . '</> | âge : ' . $age . ' | ville : ' . $ville . ' | Online : ' . $isconnect );
        $this->saveProfil($data, $output, $save);
        return $data;
    
    }

    function isWomanOrMan($data, $output = false) 
    {
        if ( isset($data['mainColumn']['Boudoir']) ){

            if ($output != false) {
                $output->writeln('<notice>[NOTICE]</> sexe : femme');
            }
            return 1;
        } else {

            if ($output != false) {
                $output->writeln('<notice>[NOTICE]</> sexe : Homme');
            }
            return 0;
        }   
    }

    function getProxy() 
    {

        $data = @file_get_contents(self::PROXY);
        $contents = json_decode($data, true);
        return $contents;

    }


    /**
     * @return bool|string
     */
    function  curl($login, $password, $url, $output = false){

        $isproxy = true;

        $postfields = array();
        $postfields["action"] = "submit";
        $postfields["remember"] = "on";
        $postfields["password"] = $password;
        $postfields["username"] = $login;

        $curl = curl_init();
        $headers[] = "Accept: */*";
        $headers[] = "Connection: Keep-Alive";
        curl_setopt($curl, CURLOPT_URL, self::URI_CONNECTION);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_USERAGENT, self::AGENT);
        curl_setopt($curl, CURLOPT_COOKIEJAR, self::COOKIE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$postfields);
        curl_setopt($curl, CURLOPT_TIMEOUT,1000);


        if ($isproxy) { // si isproxy est actif alors

            $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [NOTICE]</> <third> PROXY ACTIF </third>');

            $content = $this->getProxy();
            curl_setopt($curl, CURLOPT_HTTPPROXYTUNNEL, true); 
            curl_setopt($curl, CURLOPT_PROXY, $content["data"][0]["ip"]);
            curl_setopt($curl, CURLOPT_PROXYPORT, $content["data"][0]["port"]);

            //curl_setopt($curl, CURLOPT_PROXY, "94.177.232.56:3128");
    
        }
 

        curl_exec($curl);
        curl_setopt($curl, CURLOPT_URL, $url);
        $content = curl_exec($curl);

        if(!curl_errno($curl)){
            $info = curl_getinfo($curl);

            //var_dump($info);
            if ($output != false) {
                $output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [DEBUG] La requête envoyé a mis ' . $info['total_time'] . 'secondes à être envoyée à ' . $info['url'] . ' </>', OutputInterface::VERBOSITY_DEBUG);

                switch ($http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE)) {
                    case 200:  
                        $output->writeln('<third>[' . date('Y-m-d H:i:s') . '] [DEBUG]  CODE 200 </>', OutputInterface::VERBOSITY_DEBUG);
                        break;
                    default:
                        $output->writeln('<error>[' . date('Y-m-d H:i:s') . '] [ERROR]  ' . $http_code . '</>', OutputInterface::VERBOSITY_DEBUG);
                }
            }
        
        
        } 
        return $content;
    }





}