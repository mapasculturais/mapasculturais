<?php
namespace MapasCulturais\Repositories;

use MapasCulturais\App;

/**
 * Repositório para entidades de arquivo
 * 
 * Este repositório fornece métodos específicos para consulta
 * e manipulação de entidades do tipo File no sistema,
 * com foco em operações relacionadas a grupos de arquivos.
 * 
 * @package MapasCulturais\Repositories
 */
class File extends \MapasCulturais\Repository{

    /**
     * Encontra arquivos por proprietário e grupo
     * 
     * @param \MapasCulturais\Entity $owner Proprietário do arquivo
     * @param string $group Grupo do arquivo
     * @return \MapasCulturais\Entities\File|array|null Arquivo(s) encontrado(s)
     */
    function findByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

        $repo = $app->repo($owner->getFileClassName());
        $result = $repo->findBy(['owner' => $owner, 'group' => $group]);

        $registeredGroup = $app->getRegisteredFileGroup($owner->controllerId, $group);

        if($result && (($registeredGroup && $registeredGroup->unique) || $app->getRegisteredImageTransformation($group) || (!$registeredGroup && !$app->getRegisteredImageTransformation($group))))
            $result = $result[0];


        return $result;
    }

    /**
     * Encontra um único arquivo por proprietário e grupo
     * 
     * @param \MapasCulturais\Entity $owner Proprietário do arquivo
     * @param string $group Grupo do arquivo
     * @return \MapasCulturais\Entities\File|null Arquivo encontrado
     */
    function findOneByGroup(\MapasCulturais\Entity $owner, $group){
        $app = App::i();

        $repo = $app->repo($owner->getFileClassName());
        $result = $repo->findOneBy(['owner' => $owner, 'group' => $group]);

        return $result;
    }

    /**
     * Encontra arquivos por proprietário agrupados por grupo
     * 
     * @param \MapasCulturais\Entity $owner Proprietário dos arquivos
     * @return array Arquivos agrupados por grupo
     */
    function findByOwnerGroupedByGroup(\MapasCulturais\Entity $owner){
        $app = App::i();

        $repo = $app->repo($owner->getFileClassName());
        $files = $repo->findBy(['owner' => $owner]);

        $result = [];

        if($files){
            foreach($files as $file){
                $registeredGroup = $app->getRegisteredFileGroup($owner->controllerId, $file->group);
                if($registeredGroup && $registeredGroup->unique){
                    $result[trim($file->group)] = $file;
                }else{
                    if(!key_exists($file->group, $result))
                        $result[trim($file->group)] = [];

                    $result[trim($file->group)][] = $file;
                }
            }
            ksort($result);
        }


        return $result;
    }
}