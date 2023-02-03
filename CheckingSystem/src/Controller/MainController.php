<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Student;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/', name: 'app_')]
class MainController extends AbstractController
{
    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    #[Route('/', name: 'main')]
    public function index(): Response
    {
        if (!$this->getUser()) { // connection check
            return $this->redirectToRoute('app_login');
        }
        // return to another route
        return $this->redirectToRoute('app_home');
    }
}
