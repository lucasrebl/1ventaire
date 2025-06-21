<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'dashboard')]
    public function index(): Response
    {
        $objets_par_piece = [
            'Cuisine' => [['nom' => 'Casserole'], ['nom' => 'Mixeur']],
            'Salon' => [['nom' => 'Télévision'], ['nom' => 'Canapé']],
        ];

        $total_objets = array_reduce($objets_par_piece, fn($carry, $objets) => $carry + count($objets), 0);

        return $this->render('dashboard/index.html.twig', [
            'objets_par_piece' => $objets_par_piece,
            'total_objets' => $total_objets,
        ]);
    }
}