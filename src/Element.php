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

namespace Apisearch\SyliusApisearchPlugin;

final class Element
{
    public const FIELD_TAXON_CODE = 'taxon';
    public const FIELD_PRICE = 'price';
    public const FIELD_LOCALE = 'locale';
    public const FIELD_ID = 'id';

    public const FILTER_OPTION = 'option';
    public const FILTER_ATTRIBUTE = 'attribute';

    public const VERSION_STATIC = 'static';
    public const VERSION_DYNAMIC = 'dynamic';

    public const PRODUCT_TRANSFORMER_KEY = 'product';

    public static $versionTemplate = [
        self::VERSION_STATIC => '@SyliusApisearchPlugin/Taxon/static.html.twig',
        self::VERSION_DYNAMIC => '@SyliusApisearchPlugin/Taxon/dynamic.html.twig',
    ];

    public static $filters = [
        self::FILTER_OPTION,
        self::FILTER_ATTRIBUTE,
    ];
}
