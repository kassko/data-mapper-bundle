<?php

namespace Kassko\Bundle\DataAccessBundle\Logger;

use Psr\Log\LoggerInterface;

/**
 * We want to inject Monolog in a service A by a setter but we cannot so we inject it in the constructor of LoggerProvider.
 * Then we inject Monolog in the service A by a setter with the LoggerProvider factory method (getLogger).
 *
 * @author kko
 */
class LoggerProvider
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }
}
