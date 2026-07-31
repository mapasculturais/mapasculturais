<?php

namespace Tests\Mailer;

use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\RawMessage;

class TestTransport extends AbstractTransport
{
    /** @var SentMessage[] */
    protected static array $sentMessages = [];

    protected function doSend(SentMessage $message): void
    {
        self::$sentMessages[] = $message;
    }

    public function __toString(): string
    {
        return 'test://null';
    }

    public static function reset(): void
    {
        self::$sentMessages = [];
    }

    /**
     * @return SentMessage[]
     */
    public static function getSentMessages(): array
    {
        return self::$sentMessages;
    }

    public static function getLastMessage(): ?SentMessage
    {
        $messages = self::$sentMessages;
        return $messages ? end($messages) : null;
    }

    public static function getMessagesCount(): int
    {
        return count(self::$sentMessages);
    }
}
