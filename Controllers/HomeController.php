<?php
/**
 * Controllers/HomeController.php
 * Pages publiques du Front Office : accueil et présentation.
 */

declare(strict_types=1);

require_once __DIR__ . '/Controller.php';

class HomeController extends Controller
{
    public function home(): void
    {
        $this->render('index', [
            'title'       => APP_NAME . ' — Signalez. Suivez. Améliorez votre ville.',
            'currentPage' => 'home',
        ]);
    }

    public function about(): void
    {
        $this->render('about', [
            'title'       => 'À propos — ' . APP_NAME,
            'currentPage' => 'about',
        ]);
    }
}
