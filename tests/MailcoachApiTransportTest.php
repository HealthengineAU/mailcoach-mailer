<?php

use Spatie\MailcoachMailer\Exceptions\NoHostSet;
use Spatie\MailcoachMailer\Headers\FakeHeader;
use Spatie\MailcoachMailer\Headers\GoogleAnalyticsCampaignHeader;
use Spatie\MailcoachMailer\Headers\GoogleAnalyticsDomainsHeader;
use Spatie\MailcoachMailer\Headers\MailerHeader;
use Spatie\MailcoachMailer\Headers\ReplacementHeader;
use Spatie\MailcoachMailer\Headers\StoreContentHeader;
use Spatie\MailcoachMailer\Headers\TransactionalMailHeader;
use Spatie\MailcoachMailer\MailcoachApiTransport;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\HttpClient\ResponseInterface;

it('can be converted to string', function () {
    $transport = (new MailcoachApiTransport('dummy-token'))->setHost('domain.mailcoach.app');

    expect((string) $transport)->toBe('mailcoach+api://domain.mailcoach.app');
});

it('can send an email', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        expect($method)->toBe('POST');
        expect($url)->toBe('https://domain.mailcoach.app/api/transactional-mails/send');

        expect($options['headers'][1])->toContain('fake-api-token');

        $body = json_decode($options['body'], true);

        expect($body['from'])->toBe('"From name" <from@example.com>');
        expect($body['to'])->toBe('"To name" <to@example.com>');
        expect($body['subject'])->toBe('My subject');
        expect($body['text'])->toBe('The text content');
        expect($body['html'])->toBe('The html content');

        return new MockResponse('{"uuid":"0d834df6-c8c8-49b6-a84a-142e627b5b8f"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $response = $transport->send($mail);

    expect($response)->toBeInstanceOf(SentMessage::class);
    expect($response->getMessageId())->toBeString();
});

it('can send a plaintext email', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        expect($method)->toBe('POST');
        expect($url)->toBe('https://domain.mailcoach.app/api/transactional-mails/send');

        expect($options['headers'][1])->toContain('fake-api-token');

        $body = json_decode($options['body'], true);

        expect($body['from'])->toBe('"From name" <from@example.com>');
        expect($body['to'])->toBe('"To name" <to@example.com>');
        expect($body['subject'])->toBe('My subject');
        expect($body['text'])->toBe('The text content');
        expect($body['html'])->toBeNull();

        return new MockResponse('{"uuid":"f131da53-fa12-4971-9b03-38113977eff7"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content');

    $response = $transport->send($mail);

    expect($response)->toBeInstanceOf(SentMessage::class);
    expect($response->getMessageId())->toBeString();
});

it('can process the transactional mail header', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['mail_name'])->toBe('my_template');

        return new MockResponse('{"uuid":"b0269e39-4086-436d-87d1-e600fd013e86"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new TransactionalMailHeader('my_template'));

    $transport->send($mail);
});

it('can process the mailer header', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['mailer'])->toBe('transactional-mailer');

        return new MockResponse('{"uuid":"4b8be10e-946d-4082-add3-b168532344a7"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new MailerHeader('transactional-mailer'));

    $transport->send($mail);
});

it('can process the fake header', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['fake'])->toBe('1');

        return new MockResponse('{"uuid":"e2ee7ba1-f54d-430b-bc92-f3eb2161e55e"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new FakeHeader);

    $transport->send($mail);
});

it('throws when trying to define it twice', function () {
    $client = new MockHttpClient(function (): ResponseInterface {
        return new MockResponse('{"uuid":"07da0c8a-9ad2-4910-b931-3f7a4e255b5b"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new TransactionalMailHeader('my_template'));
    $mail->getHeaders()->add(new TransactionalMailHeader('another_template'));

    $this->expectExceptionMessage('Mailcoach only allows a single transactional mail to be defined.');

    $transport->send($mail);
});

it('can pass through replacements', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['replacements']['first_name'])->toBe('John');
        expect($body['replacements']['last_name'])->toBe('Doe');
        expect($body['replacements']['array'])->toBe(['foo', 'bar']);

        return new MockResponse('{"uuid":"6413b7c9-a730-47a8-83ac-ad5a14777e43"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new ReplacementHeader('first_name', 'John'));
    $mail->getHeaders()->add(new ReplacementHeader('last_name', 'Doe'));
    $mail->getHeaders()->add(new ReplacementHeader('array', ['foo', 'bar']));

    $transport->send($mail);
});

it('will throw an exception if the host is not set', function () {
    $transport = (new MailcoachApiTransport('fake-api-token'));

    $mail = (new Email)
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content');

    $transport->send($mail);
})->throws(NoHostSet::class);

it('can process the store content header', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['store_content'])->toBe('0');

        return new MockResponse('{"uuid":"f6196d80-61b2-421e-90a6-71a3770b9df1"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new StoreContentHeader(false));

    $transport->send($mail);
});

it('can process the google analytics campaign header', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['google_analytics_campaign'])->toBe('summer_sale');

        return new MockResponse('{"uuid":"9bb3d1d0-3f2a-4c0e-9c5f-2a6cbe1f1a4d"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new GoogleAnalyticsCampaignHeader('summer_sale'));

    $transport->send($mail);
});

it('can process the google analytics domains header', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body['google_analytics_domains'])->toBe(['example.com', 'example.org']);

        return new MockResponse('{"uuid":"c4d2e0f8-6b1a-4e7d-9c3b-5a8f2d1e0b76"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $mail->getHeaders()->add(new GoogleAnalyticsDomainsHeader(['example.com', 'example.org']));

    $transport->send($mail);
});

it('does not add the google analytics keys to the payload when the headers are absent', function () {
    $client = new MockHttpClient(function (string $method, string $url, array $options): ResponseInterface {
        $body = json_decode($options['body'], true);

        expect($body)->not->toHaveKeys(['google_analytics_campaign', 'google_analytics_domains']);

        return new MockResponse('{"uuid":"1a0d3d4f-1f5b-4a5e-8a6b-0d5f3c2b1e90"}', ['http_code' => 200]);
    });

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->subject('My subject')
        ->to(new Address('to@example.com', 'To name'))
        ->from(new Address('from@example.com', 'From name'))
        ->text('The text content')
        ->html('The html content');

    $transport->send($mail);
});

it('sets Mailcoach UUID as message ID', function (): void {
    $expectedUuid = '5077e872-21bf-40c0-a58a-abe2aa16ba54';

    $client = new MockHttpClient(
        fn (): ResponseInterface => new MockResponse('{"uuid": "' . $expectedUuid . '"}', ['http_code' => 200])
    );

    $transport = (new MailcoachApiTransport('fake-api-token', $client))->setHost('domain.mailcoach.app');

    $mail = (new Email)
        ->from('from@from.com')
        ->subject('Subject line')
        ->text('Body text')
        ->to('to@to.com');

    $sentMessage = $transport->send($mail);

    expect($sentMessage->getMessageId())->toBe($expectedUuid);
});
