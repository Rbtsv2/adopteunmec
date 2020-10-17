<?php
namespace App\Command;

use App\Service\AdopteService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

/**
* Developpé par Rbts <Charles Fournier>
*/
class SaveCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:getinfo';
    private $adopte;

	public function __construct(AdopteService $adopteService)
	{
		$this->adopte = $adopteService;
		parent::__construct();
	}

    protected function configure()
    {
        $this
        ->setDescription('Get Empty Credentials')
        ->setHelp('
             Exemple : 
             Complète les profils sans informations 
             php bin/console app:getinfo exemple@gmail.com "password"
            ')
        ->addArgument('login', InputArgument::REQUIRED , 'User login')
        ->addArgument('password', InputArgument::REQUIRED, 'User password');
    
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $password = $input->getArgument('password');
        $login = $input->getArgument('login');
 

        $noticeLog = new OutputFormatterStyle('green');
        $actionLog = new OutputFormatterStyle('magenta');
        $errorLog = new OutputFormatterStyle('red');
        $vip = new OutputFormatterStyle('yellow');

        $thirdVerboseLog = new OutputFormatterStyle('blue');

        $output->getFormatter()->setStyle('notice', $noticeLog);
        $output->getFormatter()->setStyle('error', $errorLog);
        $output->getFormatter()->setStyle('third', $thirdVerboseLog);
        $output->getFormatter()->setStyle('vip', $vip);
        $output->getFormatter()->setStyle('action', $actionLog);
   
        $this->adopte->getProfilsWhitoutInformations($login, $password, $output);

		return 1;
    }
}