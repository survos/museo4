<?php

namespace App\Command;

use App\Entity\Exhibit;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use FFMpeg;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Twig\Environment;

class Mp3Command extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    protected static $defaultName = 'app:mp3';

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addOption('cacheDir', null, InputOption::VALUE_REQUIRED, 'Text AudioGuias Directory', "./public/mp3/")
            ->addOption('speed', null, InputOption::VALUE_REQUIRED, 'Speech Speed', 200)
            ->addOption('voice', null, InputOption::VALUE_REQUIRED, 'Speech Voice', 'f2')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Force re-creation')
            ->addOption('s3', null, InputOption::VALUE_NONE, 'Upload to S3')
        ;
    }

    private $em;
    private $s3;
    private $params;
    public function __construct(EntityManagerInterface $em, Environment $twig, S3Client $s3, ParameterBagInterface $params, string $name = null)
    {
        parent::__construct($name);
        // $this->phpWord = $phpWord;
        $this->em = $em;
        $this->twig = $twig;
        $this->s3 = $s3;
        $this->params = $params;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $s3 = $this->s3;
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
                $lang = 'es-la+' . $input->getOption('voice');
                $speed = $input->getOption('speed'); // default is 175
                // for mp3 espeak -f myfile --stdout | ffmpeg -i - -ar 44100 -ac 2 -ab 192k -f mp3 final.mp3
                $output = shell_exec($cmd = "espeak -s $speed -f $txtFilename -v $lang --stdout | ffmpeg -i - -ar 44100 -ac 2 -ab 32k -f mp3 $filename");

                // upload to s3

                # $output = shell_exec($cmd = "espeak $txtFilename -w $filename -v$lang ");
                $io->success($cmd . $output);
            }

            $bucket = $this->container->getParameter('s3_bucket');
            if ($input->getOption('s3')) {
                $s3Filename = $exhibit->getCode() . '.mp3';

                $ffprobe = FFMpeg\FFProbe::create();
                $duration = $ffprobe
                    ->format($filename) // extracts file informations
                    ->get('duration');             // returns the duration property

                $exhibit->setDuration(round($duration));

                $fileSize = filesize($filename);

                // ffprobe -v error -show_entries format=duration   -of default=noprint_wrappers=1:nokey=1 ../data/museo/home-tac-museo-data-informacion-adicional-doc.mp3
                $io->text(sprintf("Uploading $s3Filename %2.1f KB", $fileSize / 1024) );


                $result = $s3->upload($bucket, $s3Filename, fopen($filename, 'rb'), 'public-read');

                // $result = $this->s3->upload($bucket, $s3Filename, file_get_contents($filename));
                // dump($result); die();
                $this->em->flush();
            }


        }


        $io->success('audio files created');
    }
}
