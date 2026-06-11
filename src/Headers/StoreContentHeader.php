<?php

declare(strict_types=1);

namespace Spatie\MailcoachMailer\Headers;

use Symfony\Component\Mime\Header\UnstructuredHeader;

class StoreContentHeader extends UnstructuredHeader
{
    public function __construct(bool $value = true)
    {
        parent::__construct('X-Mailcoach-Store-Content', $value ? '1' : '0');
    }
}
