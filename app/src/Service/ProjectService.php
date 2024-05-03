<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ProjectRepository;
use MapasCulturais\Entities\Project;

class ProjectService
{
    protected ProjectRepository $projectRepository;

    public function __construct()
    {
        $this->projectRepository = new ProjectRepository();
    }

    public function create($data): Project
    {
        $project = new Project();
        $project->setName($data->name);
        $project->setShortDescription($data->shortDescription);
        $project->setType($data->type);

        $this->projectRepository->save($project);

        return $project;
    }
}
