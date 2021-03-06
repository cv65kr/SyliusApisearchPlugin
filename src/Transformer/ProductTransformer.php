<?php

/*
 * This file is part of the Sylius Apisearch Plugin
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\SyliusApisearchPlugin\Transformer;

use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\SyliusApisearchPlugin\Configuration\ApisearchConfigurationInterface;
use Apisearch\SyliusApisearchPlugin\Element;
use Apisearch\Transformer\ReadTransformer;
use Apisearch\Transformer\WriteTransformer;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductRepository;
use Sylius\Component\Core\Model\ChannelPricingInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Locale\Context\LocaleContextInterface;

class ProductTransformer implements ReadTransformer, WriteTransformer
{
    /**
     * @var ApisearchConfigurationInterface
     */
    protected $configuration;

    /**
     * @var LocaleContextInterface
     */
    private $localeContext;
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * ProductTransformer constructor.
     *
     * @param ApisearchConfigurationInterface $configuration
     * @param LocaleContextInterface $localeContext
     * @param ProductRepository $productRepository
     */
    public function __construct(
        ApisearchConfigurationInterface $configuration,
        LocaleContextInterface $localeContext,
        ProductRepository $productRepository
    ) {
        $this->configuration = $configuration;
        $this->localeContext = $localeContext;
        $this->productRepository = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidObject($object): bool
    {
        return $object instanceof ProductInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidItem(Item $item): bool
    {
        return $item->getType() === Element::PRODUCT_TRANSFORMER_KEY;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function fromItem(Item $item)
    {
        $product = $this->productRepository->findByCode($item->get('code'));
        if (null === $product) {
            throw new \Exception();
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function toItemUUID($object): ItemUUID
    {
        return new ItemUUID(
            $object->getCode(),
            Element::PRODUCT_TRANSFORMER_KEY
        );
    }

    /**
     * {@inheritdoc}
     */
    public function toItem($object): Item
    {
        /** @var ProductInterface $object */
        return Item::create(
            $this->toItemUUID($object),
            [
                'name' => $object->getName(),
                'description' => $object->getDescription(),
                'slug' => $object->getSlug(),
                'images' => $object->getImages(),
                'code' => $object->getCode(),
                'locale' => $this->localeContext->getLocaleCode(),
            ],
            \array_merge(
                [
                    Element::FIELD_LOCALE => $this->localeContext->getLocaleCode(),
                    Element::FIELD_TAXON_CODE => $this->getTaxons($object),
                    Element::FIELD_PRICE => $this->getPrices($object),
                    Element::FIELD_ID => $object->getId(),
                ],
                $this->getOptions($object),
                $this->getAttributes($object)
            ),
            [
                'name' => $object->getName(),
                'description' => $object->getDescription(),
            ]
        );
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getTaxons(ProductInterface $product): array
    {
        $indexTaxons = [];

        $mainTaxon = $product->getMainTaxon();
        if (null !== $mainTaxon) {
            $indexTaxons[] = $mainTaxon->getCode();
        }

        $otherTaxons = $product->getTaxons();
        if ($otherTaxons->count() > 0) {
            foreach ($otherTaxons as $taxon) {
                $indexTaxons[] = $taxon->getCode();
            }
        }

        return $indexTaxons;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getPrices(ProductInterface $product): array
    {
        if (0 === $product->getVariants()->count()) {
            return [];
        }

        $indexPrices = [];
        foreach ($product->getVariants() as $variant) {
            /** @var ChannelPricingInterface $channelPricing */
            foreach ($variant->getChannelPricings() as $channelPricing) {
                $indexPrices[] = $channelPricing->getPrice();
            }
        }

        return $indexPrices;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getOptions(ProductInterface $product): array
    {
        $options = $product->getOptions();
        if (null === $options) {
            return [];
        }

        $configurationOptions = \array_column(
            $this->configuration->getFilters(Element::FILTER_OPTION),
            'name'
        );

        $indexOptions = [];
        foreach ($options as $option) {
            $code = $option->getCode();
            if (\in_array($code, $configurationOptions)) {
                $indexOptions[$code] = $option->getValue();
            }
        }

        return $indexOptions;
    }

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    private function getAttributes(ProductInterface $product): array
    {
        $attributes = $product->getAttributes();
        if (null === $attributes) {
            return [];
        }

        $configurationAttributes = \array_column(
            $this->configuration->getFilters(Element::FILTER_ATTRIBUTE),
            'name'
        );

        $indexAttributes = [];
        foreach ($attributes as $attribute) {
            $code = $attribute->getCode();
            if (\in_array($code, $configurationAttributes)) {
                $indexAttributes[$code] = $attribute->getValue();
            }
        }

        return $indexAttributes;
    }
}
