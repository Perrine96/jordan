<?php

namespace App\DataFixtures;

use App\Entity\Products;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $productsTab = [];
        for ($i = 0; $i < 20; $i++) {
            $products = new Products();
            $products->setName('Product ' . $i);
            $products->setModel('Model ' . $i);
            $products->setPrice('59.99');
            $products->setQuantity(10);
            $products->setImage('https://cdn-images.farfetch-contents.com/20/25/48/45/20254845_51157689_1000.jpg');

        $manager->persist($products);
        }

        $manager->flush();
    }
}
