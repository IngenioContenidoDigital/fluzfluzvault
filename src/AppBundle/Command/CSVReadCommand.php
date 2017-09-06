<?php

namespace AppBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use AppBundle\Entity\Vault;

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
            ->setName('codes:import')
            ->setDescription('Importar Codigos desde un archivos CSV')
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
        
        $reader = Reader::createFromPath('%kernel.root_dir%/../web/uploads/codes.csv')
                ->setHeaderOffset(0)
        ;
        $io->progressStart(iterator_count($reader));
        foreach ($reader as $row) {
            $vault = $this->em->getRepository('AppBundle:Vault')
                ->findByCode($row['code'])
            ;
            $fecha = new \DateTime();
            $intervalo = new \DateInterval('P1Y');
            $fecha->add($intervalo);
            $fecha->format('Y-m-d H:i:s');
            $total=count($vault);
            if ($total == 0) {
                $vault = new Vault();
                $vault->setCode($row['code']);
                $vault->setCodeValue($row['code_value']);
                $vault->setExpiration($fecha);                
                $this->em->persist($vault);
           }
           $io->progressAdvance();
        }

        // save / write the changes to the database
        $this->em->flush();

        $io->progressFinish();
        $io->success('Comando Ejecutado Exitosamente!');
    }
}