<?php

namespace Sbox\SessionBundle\Command;

use Sbox\SessionBundle\Entity\Session;
use Sbox\SessionBundle\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DeleteExpiredSessionsCommand
 *
 * @package Sbox\SessionBundle\Command
 * @author  Nikita Loges
 */
class DeleteExpiredSessionsCommand extends ContainerAwareCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName('sbox:session:delete-expired');
        $this->setDescription('Deletes all expired sessions.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->getRepository()->purge();

        $output->writeln('Sessions cleared.');
    }

    /**
     * @return SessionRepository
     */
    protected function getRepository(): SessionRepository
    {
        return $this
            ->getContainer()
            ->get('doctrine.orm.entity_manager')
            ->getRepository(Session::class);
    }
}
