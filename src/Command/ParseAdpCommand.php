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
class ParseAdpCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:parse';
    private $adopte;

	public function __construct(AdopteService $adopteService)
	{
		$this->adopte = $adopteService;
		parent::__construct();
	}

    protected function configure()
    {
        $this
        ->setDescription('Adopte Target Payload')
        ->setHelp('
             Exemple d\'utililsation : 
             php bin/console app:parse exemple@gmail.com \'password\' 31 femmes "yeaux verts montagnarde" online

            ')
        ->addArgument('login', InputArgument::REQUIRED , 'User login')
        ->addArgument('password', InputArgument::REQUIRED, 'User password')
        ->addArgument('code', InputArgument::REQUIRED, 'Code postal (ex: 31)')
        ->addArgument('sexe', InputArgument::REQUIRED, 'je cherche des femmes ou des hommes')
        ->addArgument('recherche', InputArgument::OPTIONAL, 'recherches speciales (exemple: yeux verts)')
        ->addArgument('online', InputArgument::OPTIONAL, 'online (force la visite sur les profils connectés');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $password = $input->getArgument('password');
        $login = $input->getArgument('login');
        $code = $input->getArgument('code');
        $iswoman = ( ($input->getArgument('sexe')) == "femmes" ) ? true : false;
        $search = $input->getArgument('recherche');
        $isonline = ( ($input->getArgument('online')) == "online" ) ?: 0;

        $noticeLog = new OutputFormatterStyle('green');
        $errorLog = new OutputFormatterStyle('red');
        $vip = new OutputFormatterStyle('yellow');

        $thirdVerboseLog = new OutputFormatterStyle('blue');

        $output->getFormatter()->setStyle('notice', $noticeLog);
        $output->getFormatter()->setStyle('error', $errorLog);
        $output->getFormatter()->setStyle('third', $thirdVerboseLog);
        $output->getFormatter()->setStyle('vip', $vip);
   
        $this->adopte->getStart($login, $password, $code, $search, $output, $isonline, $iswoman);

		return 1;
    }
}