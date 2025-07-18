<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ProductsRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Cart;

final class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(ProductsRepository $productsRepository, Request $request, EntityManagerInterface $em): Response {
        $type = $request->query->get('type');
        if ($type) {
            $products = $productsRepository->findBy(['type' => $type]);
        } else {
            $products = $productsRepository->findAll();
        }

        $user = $this->getUser();
        $cartCount = 0;
        if ($user) {
            $cart = $em->getRepository(Cart::class)->findOneBy(['client' => $user]);
            if ($cart) {
                foreach ($cart->getCartItems() as $cartItem) {
                    $cartCount += $cartItem->getQuantity();
                }
            }
        }

        return $this->render('products/products.html.twig', [
            'products' => $products,
            'cartCount' => $cartCount,
        ]);
    }
}
