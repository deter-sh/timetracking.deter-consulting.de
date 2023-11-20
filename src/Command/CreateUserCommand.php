<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Add a short description for your command',
)]
class CreateUserCommand extends Command
{

    public function __construct(private CustomerRepository $customerRepository, private UserPasswordHasherInterface $passwordHasher, private EntityManagerInterface $entityManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('password', InputArgument::REQUIRED, 'Argument description')
            ->addArgument('customerId', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $customerID = $input->getArgument('customerId');
        $admin = (bool) $input->getOption('admin');
        $customer = null;
        if ($customerID) {
            $customer = $this->customerRepository->find($customerID);
        }


        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword($this->passwordHasher->hashPassword($user, $password))
            ->setCustomer($customer)
            ->setRoles($admin ? ['ROLE_ADMIN'] : ['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
