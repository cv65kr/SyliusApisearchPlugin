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

namespace Apisearch\SyliusApisearchPlugin\Context;

use Apisearch\SyliusApisearchPlugin\Exception\TaxonNotFoundException;
use Sylius\Component\Locale\Context\LocaleContextInterface;
use Sylius\Component\Taxonomy\Model\TaxonInterface;
use Sylius\Component\Taxonomy\Repository\TaxonRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;

class TaxonContext implements TaxonContextInterface
{
    /**
     * @var TaxonRepositoryInterface
     */
    private $taxonRepository;

    /**
     * @var LocaleContextInterface
     */
    private $localeContext;

    /**
     * @param TaxonRepositoryInterface $taxonRepository
     * @param LocaleContextInterface $localeContext
     */
    public function __construct(TaxonRepositoryInterface $taxonRepository, LocaleContextInterface $localeContext)
    {
        $this->taxonRepository = $taxonRepository;
        $this->localeContext = $localeContext;
    }

    /**
     * @param Request $request
     *
     * @return TaxonInterface
     */
    public function findByRequest(Request $request): TaxonInterface
    {
        $slug = $request->get('slug', null);

        if (null === $slug) {
            throw new TaxonNotFoundException();
        }

        $localeCode = $this->localeContext->getLocaleCode();
        $taxon = $this->taxonRepository->findOneBySlug($slug, $localeCode);

        if (null === $taxon) {
            throw new TaxonNotFoundException();
        }

        return $taxon;
    }
}
