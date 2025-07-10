<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductsRepository;

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request, ProductsRepository $productsRepository): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);

        $cartData = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $productsRepository->find($id);
            if ($product) {
                $cartData[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'quantity' => $quantity,
                    'total' => $product->getPrice() * $quantity,
                ];
                $total += $product->getPrice() * $quantity;
            }
        }

        $cartCount = array_sum($cart);

        return $this->render('cart/cart.html.twig', [
            'cart' => $cartData,
            'total' => $total,
            'cartCount' => $cartCount,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add($id, Request $request, ProductsRepository $productsRepository): Response
    {
        $product = $productsRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        // Vérifier le stock
        if ($product->getQuantity() < 1) {
            $this->addFlash('danger', 'Stock unavailable');
            return $this->redirectToRoute('app_cart');
        }

        $session = $request->getSession();
        $cart = $session->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + 1;
        $session->set('cart', $cart);

        $this->addFlash('success', 'Product added to cart');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove($id, Request $request): Response
    {
        $session = $request->getSession();
        $cart = $session->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            $this->addFlash('success', 'Product removed from cart');
        }
        return $this->redirectToRoute('app_cart');
    }
}
