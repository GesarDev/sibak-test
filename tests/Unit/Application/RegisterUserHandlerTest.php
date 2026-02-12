<?php

namespace App\Tests\Unit\Application;

use App\Application\RegisterUserHandler;
use App\Http\Dto\RegisterRequest;
use App\Repository\UserRepository;
use Doctrine\DBAL\Driver\Exception as DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class RegisterUserHandlerTest extends TestCase
{
    public function testReturnsEmailErrorWhenUniqueConstraintHappensOnFlush(): void
    {
        $users = $this->createMock(UserRepository::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $validator = $this->createStub(ValidatorInterface::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $users->expects(self::once())
            ->method('findOneBy')
            ->with(['email' => 'race@example.com'])
            ->willReturn(null);

        $validator->method('validate')->willReturn(new ConstraintViolationList());

        $hasher->expects(self::once())
            ->method('hashPassword')
            ->willReturn('hashed-password');

        $em->expects(self::once())->method('persist');
        $em->expects(self::once())
            ->method('flush')
            ->willThrowException($this->makeUniqueConstraintViolation());

        $handler = new RegisterUserHandler($users, $em, $validator, $hasher);
        $result = $handler->handle(new RegisterRequest(
            name: 'Race',
            email: 'race@example.com',
            phone: '+79991112233',
            password: 'secret123',
            passwordRepeat: 'secret123',
        ));

        self::assertSame(
            ['errors' => ['email' => 'Этот email уже зарегистрирован']],
            $result
        );
    }

    private function makeUniqueConstraintViolation(): UniqueConstraintViolationException
    {
        $driverException = new class('duplicate key', 1062) extends \RuntimeException implements DriverException
        {
            public function getSQLState(): ?string
            {
                return '23000';
            }
        };

        return new UniqueConstraintViolationException($driverException, null);
    }
}
