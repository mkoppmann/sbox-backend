<?php

namespace Sbox\UserBundle\Command;

use Sbox\UserBundle\Manager\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserCommand extends ContainerAwareCommand
{

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('sbox:user:create')
            ->setDescription('Create an sbox user.')
            ->setDefinition([
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('name', InputArgument::REQUIRED, 'The Full Name'),
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputOption('super-admin', null, InputOption::VALUE_NONE, 'Set the user as super admin'),
            ])
            ->setHelp(
                "The <info>sbox:user:create</info> command creates a user interactively."
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');
        $superadmin = $input->getOption('super-admin');

        /** @var UserManagerInterface $userManager */
        $userManager = $this->getContainer()->get('sbox_user.user_manager');
        $userManager->createUser($username, $password, $email, $name, $superadmin);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $questions = [];

        $validator = function (?string $argument): string {
            if (empty($argument)) {
                throw new \Exception('Argument can not be empty.');
            }
            return $argument;
        };

        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator($validator);
            $questions['username'] = $question;
        }

        if (!$input->getArgument('name')) {
            $question = new Question('Please enter full name:');
            $question->setValidator($validator);
            $questions['name'] = $question;
        }

        if (!$input->getArgument('email')) {
            $question = new Question('Please choose an email:');
            $question->setValidator($validator);
            $questions['email'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please choose a password:');
            $question->setValidator($validator);
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
    }
}
