<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Service\Anonymize\Anonymizer;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Math\Random;
use Lof\Gdpr\Service\Anonymize\AnonymizerInterface;

final class AlphaLower implements AnonymizerInterface
{
    private const DEFAULT_LENGTH = 5;

    /**
     * @var Random
     */
    private $mathRandom;

    /**
     * @var int
     */
    private $length;

    public function __construct(
        Random $mathRandom,
        int $length = self::DEFAULT_LENGTH
    ) {
        $this->mathRandom = $mathRandom;
        $this->length = $length;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function anonymize($value): string
    {
        return $this->mathRandom->getRandomString($this->length, Random::CHARS_LOWERS);
    }
}
