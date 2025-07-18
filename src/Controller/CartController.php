<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Cart;
use App\Entity\CartItem;
use App\Entity\User; 

final class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to view your cart.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['client' => $user]);
        $cartData = [];
        $total = 0;
        $cartCount = 0;

        if ($cart) {
            foreach ($cart->getCartItems() as $cartItem) {
                $product = $cartItem->getProduct();
                $quantity = $cartItem->getQuantity();
                $cartData[] = [
                    'id' => $product->getId(),
                    'name' => $product->getName(),
                    'price' => $product->getPrice(),
                    'quantity' => $quantity,
                    'total' => $product->getPrice() * $quantity,
                ];
                $total += $product->getPrice() * $quantity;
                $cartCount += $quantity;
            }
        }

        return $this->render('cart/cart.html.twig', [
            'cart' => $cartData,
            'total' => $total,
            'cartCount' => $cartCount,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add($id, Request $request, ProductsRepository $productsRepository, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in to add to cart.');
            return $this->redirectToRoute('app_login');
        }

        $product = $productsRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        if ($product->getQuantity() < 1) {
            $this->addFlash('danger', 'Stock unavailable');
            return $this->redirectToRoute('app_products');
        }

        // Récupérer ou créer le panier
        $cart = $em->getRepository(Cart::class)->findOneBy(['client' => $user]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setClient($user);
            $cart->setCreatedAt(new \DateTimeImmutable());
            $em->persist($cart);
        }

        // Chercher un CartItem existant
        $cartItem = $em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartItem) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
        } else {
            $cartItem = new CartItem();
            $cartItem->setCart($cart);
            $cartItem->setProduct($product);
            $cartItem->setQuantity(1);
            $em->persist($cartItem);
        }

        // Décrémenter le stock produit
        $product->setQuantity($product->getQuantity() - 1);

        $em->flush();

        $this->addFlash('success', 'Product added to cart');
        return $this->redirectToRoute('app_products');
    }

    #[Route('/cart/increase/{id}', name: 'cart_increase')]
    public function increaseQuantity($id, EntityManagerInterface $em, ProductsRepository $productsRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['client' => $user]);
        $product = $productsRepository->find($id);

        if (!$cart || !$product) {
            return $this->redirectToRoute('app_cart');
        }

        $cartItem = $em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartItem && $product->getQuantity() > 0) {
            $cartItem->setQuantity($cartItem->getQuantity() + 1);
            $product->setQuantity($product->getQuantity() - 1);
            $em->flush();
        } else {
            $this->addFlash('danger', 'Stock unavailable');
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrease/{id}', name: 'cart_decrease')]
    public function decreaseQuantity($id, EntityManagerInterface $em, ProductsRepository $productsRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['client' => $user]);
        $product = $productsRepository->find($id);

        if (!$cart || !$product) {
            return $this->redirectToRoute('app_cart');
        }

        $cartItem = $em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartItem) {
            if ($cartItem->getQuantity() > 1) {
                $cartItem->setQuantity($cartItem->getQuantity() - 1);
                $product->setQuantity($product->getQuantity() + 1);
                $em->flush();
            } else {
                // Si quantité == 1, on supprime la ligne
                $product->setQuantity($product->getQuantity() + 1);
                $em->remove($cartItem);
                $em->flush();
            }
        }

        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove($id, EntityManagerInterface $em, ProductsRepository $productsRepository): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('danger', 'You must be logged in.');
            return $this->redirectToRoute('app_login');
        }

        $cart = $em->getRepository(Cart::class)->findOneBy(['client' => $user]);
        $product = $productsRepository->find($id);

        if (!$cart || !$product) {
            return $this->redirectToRoute('app_cart');
        }

        $cartItem = $em->getRepository(CartItem::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartItem) {
            // On restitue le stock
            $product->setQuantity($product->getQuantity() + $cartItem->getQuantity());
            $em->remove($cartItem);
            $em->flush();
            $this->addFlash('success', 'Product removed from cart');
        }

        return $this->redirectToRoute('app_cart');
    }
}
