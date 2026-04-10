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

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;

final readonly class AllFourTochkiProductsResult
{
    public function __construct(
        private string $id,
        private ?string $product_offer_const,
        private ?string $product_variation_const,
        private ?string $product_modification_const,
        private ?string $product_article,
    ) {}

    public function getId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductOfferConst(): ?ProductOfferConst
    {
        return false === empty($this->product_offer_const) ? new ProductOfferConst($this->product_offer_const) : null;
    }

    public function getProductVariationConst(): ?ProductVariationConst
    {
        return false === empty($this->product_variation_const) ? new ProductVariationConst($this->product_variation_const) : null;
    }

    public function getProductModificationConst(): ?ProductModificationConst
    {
        return false === empty($this->product_modification_const) ? new ProductModificationConst($this->product_modification_const) : null;
    }

    public function getProductArticle(): ?string
    {
        return $this->product_article;
    }
}
