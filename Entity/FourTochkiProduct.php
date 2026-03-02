<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\FourTochki\Products\Entity;

use BaksDev\Core\Entity\EntityState;
use BaksDev\FourTochki\Products\Entity\Code\FourTochkiProductCode;
use BaksDev\FourTochki\Products\Entity\Price\FourTochkiProductPrice;
use BaksDev\FourTochki\Products\Entity\Profile\FourTochkiProductProfile;
use BaksDev\FourTochki\Products\Entity\Refresh\FourTochkiProductRefresh;
use BaksDev\FourTochki\Products\Type\Id\FourTochkiProductUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'four_tochki_product')]
class FourTochkiProduct extends EntityState
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: FourTochkiProductUid::TYPE)]
    private FourTochkiProductUid $id;

    /** ID продукта (не уникальное) */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: ProductUid::TYPE)]
    private ProductUid $product;

    /** Константа ТП */
    #[ORM\Column(type: ProductOfferConst::TYPE, nullable: true)]
    private ?ProductOfferConst $offer = null;

    /** Константа множественного варианта */
    #[ORM\Column(type: ProductVariationConst::TYPE, nullable: true)]
    private ?ProductVariationConst $variation = null;

    /** Константа модификации множественного варианта */
    #[ORM\Column(type: ProductModificationConst::TYPE, nullable: true)]
    private ?ProductModificationConst $modification = null;

    /** Код продукта */
    #[ORM\OneToOne(targetEntity: FourTochkiProductCode::class, mappedBy: 'main', cascade: ['all'])]
    private FourTochkiProductCode $code;

    /** Необходимость в обновлении цены продукта */
    #[ORM\OneToOne(targetEntity: FourTochkiProductPrice::class, mappedBy: 'main', cascade: ['all'])]
    private FourTochkiProductPrice $price;

    /** Необходимость в обновлении остатков продукта на складе */
    #[ORM\OneToOne(targetEntity: FourTochkiProductRefresh::class, mappedBy: 'main', cascade: ['all'])]
    private FourTochkiProductRefresh $refresh;

    /** Идентификатор профиля */
    #[ORM\OneToOne(targetEntity: FourTochkiProductProfile::class, mappedBy: 'main', cascade: ['all'])]
    private FourTochkiProductProfile $profile;

    public function __construct()
    {
        $this->id = new FourTochkiProductUid();
        $this->code = new FourTochkiProductCode($this);
        $this->price = new FourTochkiProductPrice($this);
        $this->refresh = new FourTochkiProductRefresh($this);
        $this->profile = new FourTochkiProductProfile($this);
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): FourTochkiProductUid
    {
        return $this->id;
    }

    /** Гидрирует переданную DTO, вызывая ее сеттеры */
    public function getDto($dto): mixed
    {
        if($dto instanceof FourTochkiProductInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /** Гидрирует сущность переданной DTO */
    public function setEntity($dto): mixed
    {
        if($dto instanceof FourTochkiProductInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
