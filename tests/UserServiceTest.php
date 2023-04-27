<?php

use PHPUnit\Framework\TestCase;

use App\Service\UserService;
use App\Service\SerializerService;
use App\Service\FileUploaderService;
use App\Entity\User;
use App\Repository\UserBanRepository;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;
    private SerializerService $serializerService;
    private UserPasswordHasherInterface $userPasswordHasher;
    private User $user;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->serializerService = $this->createMock(SerializerService::class);
        $this->userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);

        $this->userService = new UserService(
            $this->userPasswordHasher,
            $this->userRepository,
            $this->createMock(FileUploaderService::class),
            $this->createMock(ParameterBagInterface::class),
            $this->serializerService,
            $this->createMock(TranslatorInterface::class),
            $this->createMock(UserBanRepository::class)
        );

        $this->user = new User();
        $this->user->setUsername('testuser');
        $this->user->setEmail('testuser@example.com');
        $this->user->setPassword('password123');
    }

    public function testCreateUser(): void
    {
        // Set up the mock objects for the dependencies
        $this->serializerService->expects($this->once())->method('checkErrors')->with($this->user);
        $this->userRepository->expects($this->once())->method('save')->with($this->user, true);
        $this->userPasswordHasher->expects($this->once())->method('hashPassword')->with($this->user, 'password123')->willReturn('hashedpassword');

        // Call the method to be tested
        $this->userService->createUser($this->user);

        // Assertions
        $this->assertEquals(['ROLE_USER'], $this->user->getRoles());
        $this->assertEquals('hashedpassword', $this->user->getPassword());
    }
}
