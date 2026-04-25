<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Creates a new user.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user')
            ->addArgument('password', InputArgument::REQUIRED, 'The plain password')
            ->addArgument('fullname', InputArgument::REQUIRED, 'The full name of the user')
            ->addArgument('nationalId', InputArgument::REQUIRED, 'The National ID of the user')
            ->addArgument('role', InputArgument::OPTIONAL, 'The role of the user (e.g., SUPERUSER, ADMINISTRATOR, MANAGEMENT, SALES_STAFF, CLIENT)', 'ADMINISTRATOR')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $fullname = $input->getArgument('fullname');
        $nationalId = $input->getArgument('nationalId');
        $roleName = strtoupper($input->getArgument('role'));

        try {
            $role = UserRole::from("ROLE_" . $roleName);
        } catch (\ValueError $e) {
            // Check if they passed the case name instead of the value
            try {
                $role = constant(UserRole::class . '::' . $roleName);
            } catch (\Error $e) {
                $io->error(sprintf('Invalid role: %s. Available roles: SUPERUSER, ADMINISTRATOR, MANAGEMENT, SALES_STAFF, CLIENT', $roleName));
                return Command::FAILURE;
            }
        }

        $user = new User();
        $user->setEmail($email);
        $user->setFullName($fullname);
        $user->setNationalId($nationalId);
        $user->setDateOfBirth(new \DateTime('1990-01-01')); // Default dob
        
        // Use Enum for the specified role
        $user->setRoles([$role->value]);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('User %s successfully created with role %s.', $email, $role->name));

        return Command::SUCCESS;
    }
}
