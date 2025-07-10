<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductsRepository $productsRepository, Request $request): Response {
        $type = $request->query->get('type');
        if ($type) {
            $products = $productsRepository->findBy(['type' => $type]);
        } else {
            $products = $productsRepository->findAll();
        }

        return $this->render('home/home.html.twig', [
            'products' => $products,
        ]);
    }
}

