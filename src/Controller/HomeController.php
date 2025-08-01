<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Cart;


final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductsRepository $productsRepository, Request $request, EntityManagerInterface $em): Response {
        $type = $request->query->get('type');
        if ($type) {
            $products = $productsRepository->findBy(['type' => $type]);
        } else {
            $products = $productsRepository->findBy([], ['id' => 'DESC'], 3);
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

        return $this->render('home/home.html.twig', [
            'products' => $products,
            'cartCount' => $cartCount,
        ]);
    }
}

