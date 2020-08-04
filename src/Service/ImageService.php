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

use App\Entity\Images;
use App\Entity\Informations;
use App\Entity\ParsingId;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;



class ImageService
{ 

	private $em;
	private $params;

	public function __construct(EntityManagerInterface $em, ParameterBagInterface $params)
	{
		$this->em = $em;
		$this->params = $params;
	}


	// recupere tous les profils qui n'ont pas d'images (requettes SQL)
	public function getUsersWhereEmptyBinaryImages($output)
	{
      $results = $this->em->getRepository('App:ParsingId')->findAllIdsWithoutBinaryImage();
      return $results;
	}


	public function process($output) 
	{
	
	$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Start payload', OutputInterface::VERBOSITY_NORMAL);

      // On récupére tous les profils qui n'ont aucunes photos	
	  //$profils = $this->getUsersWhereEmptyBinaryImages($output);
	  
	  // On va remplir tous les profils avec leur images cover respectives
	  $profils = $this->em->getRepository('App:ParsingId')->findAllIdsWithInformations();

	  $output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Nombre d\'enregistrements à parcourir: <vip>' . count($profils) . '</>', OutputInterface::VERBOSITY_NORMAL);
	  // pour chaque profils on va rercuperer les informations
	  foreach ($profils as $profil) {

		
		$user = $this->em->getRepository('App:ParsingId')->find($profil);

		$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Saving profile : <vip>' . $user->getPseudo() . '</>', OutputInterface::VERBOSITY_NORMAL);
		$nbInformations = count($user->getInformations()) - 1; // On prend les dernières informations
		$data = $user->getInformations(){$nbInformations};
	
	  	$result = $this->saveLocalImageCover($user, $data, $output); 

	  }
	
	}

	public function saveLocalImageCover($user, $data, $output)
	{
		
		$src = $data->getData()['member']['cover'];
		$id  = $data->getData()['member']['id'];

		$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Image to be saved on last Credentials Api: <vip>' . $src . '</>', OutputInterface::VERBOSITY_NORMAL);

		$ext  = pathinfo($src, PATHINFO_EXTENSION); // On recuepre l'extension du lien
		$name = $id . uniqid(). '.' . $ext; 
		                          // On genere un nom d'image
		$repo = \dirname(__DIR__) . "/../public/assets/buckets/". $id;

		$path = $repo . '/' . $name;



		if (!file_exists($repo)) { 
			$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> <vip>' .$user->getPseudo(). '</> doesn\'t have photo' , OutputInterface::VERBOSITY_NORMAL);
			mkdir($repo, 0700);
			file_put_contents($path, file_get_contents($src));
			$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Image save on <third>'.$path.'/</>', OutputInterface::VERBOSITY_NORMAL);

			$return = $this->saveImageCover($user,$name, $path, $output);
			$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> Image path add to <third>database</>', OutputInterface::VERBOSITY_NORMAL);

			
		} else {
	
			// On liste tous les fichier du dossier afin de verifier si il n"y a pas un fichier qui est egale à celui de la base
			$images = glob($repo ."/*.*"); // On recupere la liste de toutes les images
			$compteur = count($images);
			$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO]</> <vip>' .$user->getPseudo(). '</> have '.$compteur. '<vip> local photo(s) </>' , OutputInterface::VERBOSITY_NORMAL);

			foreach ($images as $image) {
				
				$url = (pathinfo($image));
				if ($this->isCoverImage($url['basename'])) { // si le lien existe en base de donnée alors
					$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO] Check Image Sync -> ok !</>', OutputInterface::VERBOSITY_NORMAL);
				} else {
					$return = $this->saveImageCover($user, $url['basename'], $repo . '/' . $url['basename'], $output);
					$output->writeln('<error>[' . date('Y-m-d H:i:s') . '] [WARNING]</> Path missing, do correct sync ... <notice> ok !</>', OutputInterface::VERBOSITY_NORMAL);
				}

			}
			
		}

		$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [INFO] Everything is ok ! </>', OutputInterface::VERBOSITY_NORMAL);
		
	}



	public function isCoverImage($name)
	{
		//vérifier si l'image est bien en base ou quoi
		$coverImage = $this->em->getRepository('App:Images')->findOneBy(array('name' => $name));
		return $coverImage;
	}

	public function saveImageCover($user,$name, $path, $output)
	{
		//$output->writeln('<notice>[' . date('Y-m-d H:i:s') . '] [DEBUG] Enregistrement de l\'image </> ', OutputInterface::VERBOSITY_NORMAL);
		$image = new Images();
        $image->setRelation($user);
        $image->setName($name);
        $image->setPath($path);
        $user->addImage($image);
        $this->em->persist($user);
        $this->em->flush();
	}



}