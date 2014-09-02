<?php
$app = \MapasCulturais\App::i();

function getParentWithAddress($child){
    if($child->parent){
        if($child->parent->endereco){
            return $child->parent;
        }else{
            return getParentWithAddress($child->parent);
        }
    }else{
        return $child;
    }
}

function updateAddressData($sourceSpace, $destinySpace){
    \MapasCulturais\App::i()->log->debug('--- UPDATING SPACE: '.$destinySpace);
    $destinySpace->endereco = $sourceSpace->endereco;
    $destinySpace->location = $sourceSpace->location;
    $destinySpace->sp_regiao = $sourceSpace->sp_regiao;
    $destinySpace->sp_subprefeitura = $sourceSpace->sp_subprefeitura;
    $destinySpace->sp_distrito = $sourceSpace->sp_distrito;
    \MapasCulturais\App::i()->log->debug('--- SPACE LOCATION '.$destinySpace->location);
}

$app->hook('entity(space).save:before', function($args) use ($app) {

    $changedMetadata = $this->getChangedMetadata();
    if( $changedMetadata && key_exists('endereco', $changedMetadata)){
        $oldEndereco = $changedMetadata['endereco']['oldValue'];
        $newEndereco = $changedMetadata['endereco']['newValue'];
    }

    //$oldLocation = get_class($args) === 'Doctrine\ORM\Event\PreUpdateEventArgs' ? $args->getOldValue('location') : null;

    //$app->log->debug('this: '.$this->id.', parentWithAdd: '.getParentWithAddress($this)->id);
    //$app->log->debug('-- OLD LOCATION: '.$oldLocation);

    if(( $this->parent &&
            (!$this->endereco ||
            $this->endereco == $this->parent->endereco ||
            (isset($oldEndereco) && $this->endereco == $oldEndereco) ||
            !$this->location)
    )){
        $app->log->debug('UPDATING SPACE:');
        updateAddressData(getParentWithAddress($this), $this);
    }

   foreach($this->children as $child){
        if( !$child->endereco || $child->endereco == $this->endereco || (isset($oldEndereco) && $child->endereco == $oldEndereco) ){
            $app->log->debug('UPDATING CHILDREN:');
            updateAddressData($this, $child);
        }
    }

});