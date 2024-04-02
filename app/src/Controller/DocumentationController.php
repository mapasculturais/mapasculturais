<?php

declare(strict_types=1);

namespace App\Controller;

class DocumentationController extends AbstractController
{
    public function index(): void
    {
        $this->render($_SERVER['DOCUMENT_ROOT'].'/docs/index.html');
    }
}
