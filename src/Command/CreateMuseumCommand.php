<?php

namespace App\Command;

use App\Entity\Museum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Twig\Environment;

class CreateMuseumCommand extends Command
{
    protected static $defaultName = 'app:create-museum';
    private $em;
    private $twig;

    protected function configure()
    {
        $this
            ->setDescription('Create a Museum')
            ->addArgument('name', InputArgument::OPTIONAL, 'Museum Name', 'Museo Zacatecano')
            ->addOption('slug', null, InputOption::VALUE_NONE, 'Override default slug')
        ;
    }

    public function __construct(EntityManagerInterface $em, Environment $twig, string $name = null)
    {
        parent::__construct($name);
        // $this->phpWord = $phpWord;
        $this->em = $em;
        $this->twig = $twig;

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $repo = $this->em->getRepository(Museum::class);

        $io = new SymfonyStyle($input, $output);
        $name = $input->getArgument('name');

        if (!$museum = $repo->findOneBy(['name' => $name])) {
            $museum = (new Museum())
                ->setName($name);
            $this->em->persist($museum);
        }

        if ($slug = $input->getOption('slug')) {
            $museum->setSlug($slug);
        }

        $this->em->flush();

        $io->success(sprintf("Museum %s created/updated.", $museum->getSlug())) ;
    }
}
