<?php
namespace App\Command;

use App\Service\ImageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
* Developpé par Rbts <Charles Fournier>
*/
class getImageCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:getimages';
    private $image;

	public function __construct(ImageService $image)
	{
		$this->image = $image;
		parent::__construct();
	}

    protected function configure()
    {
        $this
        ->setDescription('Get images cover of last credentials taking')
        ->setHelp('
             Exemple d\'utilisation : 
             Enregistre la dernière image du scrapping des informations credentials de l\' API Adopte en local et du path en base
             php bin/console app:getimages
            ');
        // ->addArgument('login', InputArgument::REQUIRED , 'User login')
        // ->addArgument('password', InputArgument::REQUIRED, 'User password');

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // $password = $input->getArgument('password');
        // $login = $input->getArgument('login');


        $noticeLog = new OutputFormatterStyle('green');
        $errorLog = new OutputFormatterStyle('red');
        $vip = new OutputFormatterStyle('yellow');

        $thirdVerboseLog = new OutputFormatterStyle('blue');

        $output->getFormatter()->setStyle('notice', $noticeLog);
        $output->getFormatter()->setStyle('error', $errorLog);
        $output->getFormatter()->setStyle('third', $thirdVerboseLog);
        $output->getFormatter()->setStyle('vip', $vip);
   
        $this->image->process($output);

		return 1;
    }
}