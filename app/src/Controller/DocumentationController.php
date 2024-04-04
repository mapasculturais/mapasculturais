<?php

declare(strict_types=1);

namespace App\Controller;

class DocumentationController extends AbstractController
{
    public function v1(): void
    {
        $this->render($_SERVER['DOCUMENT_ROOT'].'/docs/v1/index.html');
    }

    public function v2(): void
    {
        $this->render($_SERVER['DOCUMENT_ROOT'].'/docs/v2/index.html');
    }
}
