<?php
/*
 * Copyright 2026.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\FourTochki\Products\Repository\AllFourTochkiProducts;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\FourTochki\Entity\Event\FourTochkiAuthEvent;
use BaksDev\FourTochki\Entity\FourTochkiAuth;
use BaksDev\FourTochki\Entity\Profile\FourTochkiAuthProfile;
use BaksDev\FourTochki\Products\Entity\FourTochkiProduct;
use BaksDev\FourTochki\Products\Entity\Profile\FourTochkiProductProfile;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

final class AllFourTochkiProductsRepository implements AllFourTochkiProductsInterface
{
    private ?UserProfileUid $profile = null;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage
    ) {}

    public function profile(UserProfileUid $profile): self
    {
        $this->profile = $profile;
        return $this;
    }

    public function findAll(): Generator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(FourTochkiAuth::class, 'four_tochki_auth');

        $dbal
            ->join(
                'four_tochki_auth',
                FourTochkiAuthEvent::class,
                'four_tochki_auth_event',
                'four_tochki_auth_event.id = four_tochki_auth.event',
            );

        $dbal
            ->join(
                'four_tochki_auth',
                FourTochkiAuthProfile::class,
                'four_tochki_auth_profile',
                '
                    four_tochki_auth_profile.event = four_tochki_auth.event 
                    AND four_tochki_auth_profile.value = :profile
                ',
            )
            ->setParameter(
                key: 'profile',
                value: false === empty($this->profile) ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );

        $dbal
            ->select('product.id')
            ->join(
                'four_tochki_auth',
                Product::class,
                'product',
                'product.id != four_tochki_auth.id',
            );


        /** Только активные продукты */
        $dbal
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND
                    product_active.active IS TRUE',
            );


        /** Артикул карточки */
        $dbal
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );


        /** ТОРГОВОЕ ПРЕДЛОЖЕНИЕ */
        $dbal
            ->addSelect('product_offer.const as product_offer_const')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );


        /** ВАРИАНТЫ торгового предложения */
        $dbal
            ->addSelect('product_variation.const as product_variation_const')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );


        /** МОДИФИКАЦИИ множественного варианта */
        $dbal
            ->addSelect('product_modification.const as product_modification_const')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id ',
            );


        /** Артикул продукта */
        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');



        /** Продукт 4tochki */
        $dbal
            ->leftJoin(
                'product_modification',
                FourTochkiProduct::class,
                'four_tochki_product',
                '
                    four_tochki_product.product = product.id 

                    AND
                        
                        CASE 
                            WHEN product_offer.const IS NOT NULL 
                            THEN four_tochki_product.offer = product_offer.const
                            ELSE four_tochki_product.offer IS NULL
                        END
                        
                    AND 
                
                        CASE
                            WHEN product_variation.const IS NOT NULL 
                            THEN four_tochki_product.variation = product_variation.const
                            ELSE four_tochki_product.variation IS NULL
                        END
                    
                    AND
                
                        CASE
                            WHEN product_modification.const IS NOT NULL 
                            THEN four_tochki_product.modification = product_modification.const
                            ELSE four_tochki_product.modification IS NULL
                        END
                ');


        /** Продукт 4tochki по профилю пользователя */
        $dbal
            ->leftJoin(
                'product_modification',
                FourTochkiProductProfile::class,
                'four_tochki_product_profile',
                '
                   four_tochki_product_profile.main = four_tochki_product.id AND
                   four_tochki_product_profile.value = four_tochki_auth_profile.value',
            );

        $dbal->where('four_tochki_product_profile.value IS NOT NULL');


        /** Категория */
        $dbal
            ->join(
                'product',
                ProductCategory::class,
                'product_category',
                '
                    product_category.event = product.event AND 
                    product_category.root = true',
            );


        $dbal->join(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category',
        );


        /** Только активные разделы */
        $dbal
            ->join(
                'product_category',
                CategoryProductInfo::class,
                'category_info',
                '
                    category.event = category_info.event AND
                    category_info.active IS TRUE',
            );

        $dbal->allGroupByExclude();

        return $dbal->fetchAllHydrate(AllFourTochkiProductsResult::class);
    }
}