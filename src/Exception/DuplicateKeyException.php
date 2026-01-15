<?php

declare(strict_types=1);

/*
 * This file is part of DataMapperBundle.
 *
 * Copyright 2025 kassko
 *
 * For the full copyright and license information,
 * please view the LICENSE and NOTICE files that were distributed with this source code.
 */

namespace Kassko\Bundle\DataMapperBundle\Exception;

/**
 * Exception thrown when a duplicate key is detected.
 *
 * This exception is used when custom hydrators or custom object mappers
 * have duplicate keys between semantic configuration and tagged services.
 */
final class DuplicateKeyException extends \RuntimeException
{
}
