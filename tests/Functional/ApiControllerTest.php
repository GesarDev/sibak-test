<?php

namespace App\Tests\Functional;

use App\Repository\FeedbackMessageRepository;
use App\Tests\Support\RecreatesDatabaseSchema;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ApiControllerTest extends WebTestCase
{
    use RecreatesDatabaseSchema;

    public function testRegisterMismatchReturnsPasswordRepeatErrorKey(): void
    {
        $client = $this->createClientWithFreshSchema();
        $csrf = $this->fetchCsrfToken($client);

        $this->postJson($client, '/api/register', [
            'name' => 'Ivan',
            'email' => 'ivan@example.com',
            'phone' => '+79991234567',
            'password' => 'secret123',
            'password_repeat' => 'secret321',
        ], $csrf);

        self::assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $payload = $this->decodeJsonResponse($client);

        self::assertArrayHasKey('errors', $payload);
        self::assertArrayHasKey('password_repeat', $payload['errors']);
        self::assertArrayNotHasKey('passwordRepeat', $payload['errors']);
    }

    public function testFeedbackAuthorUsesNameOnlyForExistingEmail(): void
    {
        $client = $this->createClientWithFreshSchema();
        $csrf = $this->fetchCsrfToken($client);

        $this->postJson($client, '/api/register', [
            'name' => 'Ilya',
            'email' => 'ilya@example.com',
            'phone' => '+79990000000',
            'password' => 'secret123',
            'password_repeat' => 'secret123',
        ], $csrf);
        self::assertResponseIsSuccessful();

        $this->postJson($client, '/api/feedback', [
            'email' => 'ilya@example.com',
            'message' => 'first message',
        ], $csrf);
        self::assertResponseIsSuccessful();
        $firstFeedback = $this->decodeJsonResponse($client);
        self::assertSame('Ilya', $firstFeedback['feedback']['author'] ?? null);

        $this->postJson($client, '/api/feedback', [
            'email' => 'unknown@example.com',
            'message' => 'second message',
        ], $csrf);
        self::assertResponseIsSuccessful();
        $secondFeedback = $this->decodeJsonResponse($client);
        self::assertSame('unknown@example.com', $secondFeedback['feedback']['author'] ?? null);

        $feedbackRepo = static::getContainer()->get(FeedbackMessageRepository::class);
        $allMessages = $feedbackRepo->findBy([], ['id' => 'ASC']);

        self::assertCount(2, $allMessages);
        self::assertSame('ilya@example.com', $allMessages[0]->getEmail());
        self::assertSame('unknown@example.com', $allMessages[1]->getEmail());
    }

    private function createClientWithFreshSchema(): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $this->recreateDatabaseSchema();

        return $client;
    }

    private function fetchCsrfToken(KernelBrowser $client): string
    {
        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $token = $crawler->filter('meta[name="csrf-token"]')->attr('content');
        self::assertNotNull($token);

        return $token;
    }

    /**
     * @param array<string,mixed> $payload
     */
    private function postJson(KernelBrowser $client, string $url, array $payload, string $csrf): void
    {
        $client->request(
            'POST',
            $url,
            server: [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CSRF_TOKEN' => $csrf,
            ],
            content: (string) json_encode($payload, JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeJsonResponse(KernelBrowser $client): array
    {
        $content = (string) $client->getResponse()->getContent();

        return (array) json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
