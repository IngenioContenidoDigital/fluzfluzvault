<?php

namespace AppBundle\Command;

use AppBundle\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CSVReadCommand extends Command{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CsvImportCommand constructor.
     *
     * @param EntityManagerInterface $em
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(EntityManagerInterface $em){
        parent::__construct();
        $this->em = $em;
    }

    /**
     * Configure
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(){
        $this
            ->setName('csv:import')
            ->setDescription('Importar datos desde un archivos CSV')
        ;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $io = new SymfonyStyle($input, $output);
        $io->title('Intentando Importar el archivo...');
        
        $reader = Reader::createFromPath('%kernel.root_dir%/../web/uploads/members.csv')
                ->setHeaderOffset(0)
        ;
        $io->progressStart(iterator_count($reader));
        foreach ($reader as $row) {
            $member = $this->em->getRepository('AppBundle:Member')
                ->findMemberByEmail($row['member_email']);
            $total=count($member);
                
            if ($total == 0) {
                $member = (new Member())
                    ->setMemberName($row['member_name'])
                    ->setMemberEmail($row['member_email'])
                    ->setMobilePhone($row['mobile_phone'])
                    ->setIdentification($row['identification'])
                    ->setDateAdd(new \DateTime())
                ;

                $this->em->persist($member);
           }
           $io->progressAdvance();
        }

        // save / write the changes to the database
        $this->em->flush();

        $io->progressFinish();
        $io->success('Comando Ejecutado Exitosamente!');
    }
}