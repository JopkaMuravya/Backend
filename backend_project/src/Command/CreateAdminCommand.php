<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-admin',
    description: 'Создание пользователя с правами администратора',
    aliases: ['admin:create']
)]
class CreateAdminCommand extends Command
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
            ->addArgument('email', InputArgument::OPTIONAL, 'Email администратора')
            ->addArgument('phone', InputArgument::OPTIONAL, 'Телефон администратора')
            ->addArgument('password', InputArgument::OPTIONAL, 'Пароль администратора')
            ->addOption('first-name', 'f', InputOption::VALUE_OPTIONAL, 'Имя', 'Admin')
            ->addOption('last-name', 'l', InputOption::VALUE_OPTIONAL, 'Фамилия', 'Adminov')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Роли', ['ROLE_ADMIN'])
            ->setHelp(<<<'HELP'
Команда <info>%command.name%</info> создает пользователя с правами администратора.

Внимание! Телефон должен быть уникальным и использоваться для аутентификации.

Примеры использования:
  <info>php %command.full_name% admin@example.com +79991234567 secret123</info>
  <info>php %command.full_name% --first-name=Иван --last-name=Иванов</info>
  <info>php %command.full_name% --role=ROLE_ADMIN --role=ROLE_SUPER_ADMIN</info>

Если не указать параметры, команда запросит их интерактивно.
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Создание администратора');

        $email = $input->getArgument('email');
        if (!$email) {
            $email = $io->askQuestion(
                new Question('Введите email администратора: ')
            );
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Неверный формат email');
            return Command::FAILURE;
        }

        $phone = $input->getArgument('phone');
        if (!$phone) {
            $phone = $io->askQuestion(
                new Question('Введите телефон администратора (уникальный, используется для входа): ')
            );
        }

        $existingByEmail = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        $existingByPhone = $this->entityManager->getRepository(User::class)->findOneBy(['phone' => $phone]);
        
        if ($existingByEmail) {
            $io->error("Пользователь с email {$email} уже существует.");
            return Command::FAILURE;
        }
        
        if ($existingByPhone) {
            $io->error("Пользователь с телефоном {$phone} уже существует.");
            return Command::FAILURE;
        }

        $password = $input->getArgument('password');
        if (!$password) {
            $passwordQuestion = new Question('Введите пароль администратора (минимум 6 символов): ');
            $passwordQuestion->setHidden(true);
            $passwordQuestion->setHiddenFallback(false);
            
            $password = $io->askQuestion($passwordQuestion);
            
            $confirmQuestion = new Question('Повторите пароль: ');
            $confirmQuestion->setHidden(true);
            $confirmQuestion->setHiddenFallback(false);
            
            $confirmPassword = $io->askQuestion($confirmQuestion);
            
            if ($password !== $confirmPassword) {
                $io->error('Пароли не совпадают');
                return Command::FAILURE;
            }
        }

        if (strlen($password) < 6) {
            $io->error('Пароль должен содержать минимум 6 символов');
            return Command::FAILURE;
        }

        $user = new User(
            email: $email,
            phone: $phone,
            password: '',
            firstName: $input->getOption('first-name'),
            lastName: $input->getOption('last-name'),
            roles: $input->getOption('role')
        );

        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success('Администратор успешно создан!');
        $io->table(
            ['Параметр', 'Значение'],
            [
                ['ID', $user->getId()],
                ['Email', $user->getEmail()],
                ['Телефон', $user->getPhone()],
                ['Полное имя', $user->getFullName()],
                ['Роли', implode(', ', $user->getRoles())],
            ]
        );

        $io->note([
            'Для входа в админ-панель:',
            '1. Перейдите по адресу: /admin',
            '2. Используйте телефон: ' . $user->getPhone(),
            '3. Введите указанный пароль',
            '',
            'Внимание: Аутентификация происходит по телефону!',
        ]);

        return Command::SUCCESS;
    }
}