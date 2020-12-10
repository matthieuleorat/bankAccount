<?php

/**
 * This file is part of the BankAccount project.
 *
 * (c) Matthieu Leorat <matthieu.leorat@pm.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Command;

use App\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UpdateUserCommand extends Command
{
    protected static $defaultName = 'app:update-user';
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $manager, string $name = null)
    {
        parent::__construct($name);
        $this->passwordEncoder = $passwordEncoder;
        $this->manager = $manager;
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $question1 = new Question('Please provide the username: ');
        $username = $helper->ask($input, $output, $question1);

        $user = $this->manager->getRepository(User::class)->findOneBy(['username' => $username]);

        if ($user instanceof User) {
            $io->success('User found');

            $question2 = new Question('Please provide the user password: ');
            $question2->setHidden(true);
            $question2->setHiddenFallback(false);

            $password = $helper->ask($input, $output, $question2);

            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $password
            ));

            $this->manager->flush();
        } else {
            $io->error('User '.$username.' not found');
        }

        return 0;
    }
}
