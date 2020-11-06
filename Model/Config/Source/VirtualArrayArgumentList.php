<?php
/**
 * Copyright © Landofcoder, All rights reserved.
 * See LICENSE bundled with this library for license details.
 */
declare(strict_types=1);

namespace Lof\Gdpr\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\ObjectManager\ConfigInterface;
use Magento\Framework\Phrase;
use function array_keys;

final class VirtualArrayArgumentList implements OptionSourceInterface
{
    /**
     * @var ConfigInterface
     */
    private $objectManagerConfig;

    /**
     * @var string
     */
    private $className;

    /**
     * @var string
     */
    private $argumentName;

    /**
     * @var array
     */
    private $options;

    public function __construct(
        ConfigInterface $objectManagerConfig,
        string $className,
        string $argumentName
    ) {
        $this->objectManagerConfig = $objectManagerConfig;
        $this->className = $className;
        $this->argumentName = $argumentName;
    }

    public function toOptionArray(): array
    {
        if (!$this->options) {
            foreach (array_keys($this->retrieveItems()) as $item) {
                $this->options[] = ['value' => $item, 'label' => new Phrase($item)];
            }
        }

        return $this->options;
    }

    /**
     * Retrieve the items from the di settings
     *
     * @return string[]
     */
    private function retrieveItems(): array
    {
        $arguments = $this->objectManagerConfig->getArguments(
            $this->objectManagerConfig->getPreference($this->className)
        );

        return $arguments[$this->argumentName]['_v_'] ?? $arguments[$this->argumentName] ?? [];
    }
}
