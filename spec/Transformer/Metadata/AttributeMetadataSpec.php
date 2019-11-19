<?php

declare(strict_types=1);

namespace spec\Apisearch\SyliusApisearchPlugin\Transformer\Metadata;

use Apisearch\SyliusApisearchPlugin\Configuration\ApisearchConfigurationInterface;
use Apisearch\SyliusApisearchPlugin\Transformer\Metadata\AttributeMetadata;
use Apisearch\SyliusApisearchPlugin\Transformer\Metadata\MetadataInterface;
use Generator;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\ProductInterface;

class AttributeMetadataSpec extends ObjectBehavior
{
    function let(ApisearchConfigurationInterface $configuration): void
    {
        $this->beConstructedWith($configuration);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(AttributeMetadata::class);
    }

    function it_is_implements_interface(): void
    {
        $this->shouldHaveType(MetadataInterface::class);
    }

    function it_get_metadata(ProductInterface $product, ApisearchConfigurationInterface $configuration): void
    {
        $product->getId()->willReturn(1);
        $configuration->getFilters()->willReturn([]);

        $this->getMetadata($product)->shouldBeAnInstanceOf(Generator::class);
    }
}