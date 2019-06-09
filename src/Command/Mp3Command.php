<?php

namespace App\Command;

use App\Entity\Exhibit;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class Mp3Command extends Command
{
    protected static $defaultName = 'app:mp3';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('cacheDir', null, InputOption::VALUE_REQUIRED, 'Text AudioGuias Directory', "./public/mp3/")
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force re-creation')
        ;
    }

    private $em;
    public function __construct(EntityManagerInterface $em, Environment $twig, string $name = null)
    {
        parent::__construct($name);
        // $this->phpWord = $phpWord;
        $this->em = $em;
        $this->twig = $twig;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $force = $input->getOption('force');

        $repo = $this->em->getRepository(Exhibit::class);
        foreach ($repo->findAll() as $exhibit) {

            $txtFilename = $input->getOption('cacheDir') . $exhibit->getCode() . '.txt';
            if ($force || !file_exists($txtFilename)) {
                $io->warning("Creating $txtFilename");
                file_put_contents($txtFilename, $exhibit->getTranscript());
            }

            $filename = $input->getOption('cacheDir') . $exhibit->getCode() . '.mp3';
            if ($force || !file_exists($filename)) {
                if ($force) {
                    @unlink($filename);
                }
                $io->warning("Creating $filename");
                $lang = 'es-la';
                // for mp3 espeak -f myfile --stdout | ffmpeg -i - -ar 44100 -ac 2 -ab 192k -f mp3 final.mp3
                $output = shell_exec($cmd = "espeak -f $txtFilename -v $lang --stdout | ffmpeg -i - -ar 44100 -ac 2 -ab 32k -f mp3 $filename");
                # $output = shell_exec($cmd = "espeak $txtFilename -w $filename -v$lang ");
                $io->success($cmd . $output);
            }

        }


        $io->success('audio files created');
    }
}
