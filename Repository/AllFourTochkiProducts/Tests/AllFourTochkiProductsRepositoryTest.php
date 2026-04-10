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

namespace BaksDev\FourTochki\Products\Repository\AllFourTochkiProducts\Tests;

use BaksDev\FourTochki\Products\Repository\AllFourTochkiProducts\AllFourTochkiProductsInterface;
use BaksDev\FourTochki\Products\Repository\AllFourTochkiProducts\AllFourTochkiProductsRepository;
use BaksDev\FourTochki\Products\Repository\AllFourTochkiProducts\AllFourTochkiProductsResult;
use BaksDev\FourTochki\Products\UseCase\NewEdit\Tests\FourTochkiProductNewTest;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('four-tochki-products')]
#[Group('four-tochki-products-repository')]
final class AllFourTochkiProductsRepositoryTest extends KernelTestCase
{
    #[DependsOnClass(FourTochkiProductNewTest::class)]
    public function testRepository(): void
    {
        /** @var AllFourTochkiProductsRepository $AllProductsWithFourTochkiRepository */
        $AllFourTochkiProductsRepository = self::getContainer()->get(AllFourTochkiProductsInterface::class);

        $profile = $_SERVER['TEST_PROFILE'] ?? UserProfileUid::TEST;

        $results = $AllFourTochkiProductsRepository
            ->profile(new UserProfileUid($profile))
            ->findAll();

        /** @var AllFourTochkiProductsResult $AllFourTochkiProductsResult */
        foreach($results as $AllFourTochkiProductsResult)
        {
            self::assertInstanceOf(AllFourTochkiProductsResult::class, $AllFourTochkiProductsResult);

            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(AllFourTochkiProductsResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($AllFourTochkiProductsResult);
                    //                    dump($data);
                }
            }

            break;
        }
    }
}