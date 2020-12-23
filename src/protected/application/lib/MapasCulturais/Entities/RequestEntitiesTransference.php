<?php
namespace MapasCulturais\Entities;

use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\App;
use Respect\Validation\Rules\Instance;

/**
 * @property \MapasCulturais\Entities\Agent $destination The new owner of the origin
 * @property \MapasCulturais\Entities\User $origin The new owner of the origin
 *
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class RequestEntitiesTransference extends Request{

    /**
     * Set origin user
     *
     * @param \MapasCulturais\Entity $user
     * @return void
     */
    public function setOrigin(\MapasCulturais\Entity $user) {
        parent::setOrigin($user);

        $profile = $user->profile;

        $this->metadata['entities'] = [];

        foreach(['agents', 'spaces', 'projects', 'opportunities', 'events'] as $entity_type){
            $entities = $user->$entity_type;
            foreach($entities as $entity){
                if($profile->equals($entity)){
                    continue;
                }
                $this->metadata['entities'][] = (object) [
                    'className' => $entity->getClassName(),
                    'id' => $entity->id,
                    'status' => $entity->status,
                    'changeOwner' => $entity instanceof Agent || $profile->equals($entity->owner)
                ];
            }
        }
    }

    function _doApproveAction() {
        $app = App::i();

        $agent = $this->getDestination();
        $target_user = $agent->user;

        $transfered = [];

        foreach($this->metadata['entities'] as $object){
            $entity = $app->repo($object->className)->find($object->id);
            $entity->status = $object->status;

            $transfered[] = "<a rel='noopener noreferrer' href=\"$entity->singleUrl\">{$entity->entityTypeLabel} {$entity->name}</a>";

            if($entity instanceof Agent){
                $entity->user = $target_user;
            } else if($object->changeOwner){
                $entity->owner = $agent;
            }
            $entity->save(true);
        }

        $notification = new Notification;

        $notification->user = $target_user;
        $notification->message = sprintf(\MapasCulturais\i::__('As seguintes entidades foram transferidas para você: %s'), implode(', ', $transfered));

        $notification->save(true);
    }
}