<?php

namespace App\Application;

use App\Entity\User;
use App\Http\Dto\RegisterRequest;
use App\Http\Json\ValidationErrors;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterUserHandler
{
    public function __construct(
        private UserRepository $users,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $hasher,
    ) {}

    /**
     * Регистрирует нового пользователя.
     * TODO: добавить отправку email при регистрации
     *
     * @return array{user?:array, errors?:array<string,string>}
     */
    public function handle(RegisterRequest $req): array
    {
        $fieldMap = ["passwordRepeat" => 'password_repeat'];

        $violations = $this->validator->validate($req);
        if (\count($violations) > 0) {
            return ['errors' => ValidationErrors::fromViolations($violations, $fieldMap)];
        }

        $email = mb_strtolower(trim($req->email));
        if ($this->users->findOneBy(['email' => $email])) {
            return ['errors' => ['email' => 'Этот email уже зарегистрирован']];
        }

        $user = (new User())
            ->setName(trim($req->name))
            ->setEmail($email)
            ->setPhone(trim($req->phone));

        $user->setPasswordHash($this->hasher->hashPassword($user, $req->password));

        $violations = $this->validator->validate($user);
        if (\count($violations) > 0) {
            return ['errors' => ValidationErrors::fromViolations($violations, $fieldMap)];
        }

        $this->em->persist($user);
        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            return ['errors' => ['email' => 'Этот email уже зарегистрирован']];
        }

        return [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'phone' => $user->getPhone(),
            ],
        ];
    }
}
