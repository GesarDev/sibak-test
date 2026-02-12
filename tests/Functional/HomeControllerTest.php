<?php

namespace App\Tests\Functional;

use App\Entity\FeedbackMessage;
use App\Entity\User;
use App\Tests\Support\RecreatesDatabaseSchema;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class HomeControllerTest extends WebTestCase
{
    use RecreatesDatabaseSchema;

    public function testFeedbackListResolvesAuthorNameByEmailForLegacyRows(): void
    {
        $client = $this->createClientWithFreshSchema();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $user = (new User())
            ->setName('Legacy User')
            ->setEmail('legacy@example.com')
            ->setPhone('+79995554433')
            ->setPasswordHash('hashed-password');

        $feedbackWithKnownEmail = (new FeedbackMessage())
            ->setEmail('legacy@example.com')
            ->setMessage('legacy message');

        $feedbackWithUnknownEmail = (new FeedbackMessage())
            ->setEmail('outsider@example.com')
            ->setMessage('outsider message');

        $em->persist($user);
        $em->persist($feedbackWithKnownEmail);
        $em->persist($feedbackWithUnknownEmail);
        $em->flush();

        $crawler = $client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $rows = $crawler->filter('#feedbackList li')->each(
            static fn ($node): string => preg_replace('/\s+/', ' ', trim((string) $node->text())) ?? ''
        );

        self::assertContains('Legacy User: legacy message', $rows);
        self::assertContains('outsider@example.com: outsider message', $rows);
    }

    private function createClientWithFreshSchema(): KernelBrowser
    {
        self::ensureKernelShutdown();
        $client = static::createClient();
        $this->recreateDatabaseSchema();

        return $client;
    }
}
