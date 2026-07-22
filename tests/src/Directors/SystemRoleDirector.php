<?php

namespace Tests\Directors;

use MapasCulturais\App;
use MapasCulturais\Entities\Agent;
use UserManagement\Entities\SystemRole;
use Tests\Abstract\Director;
use Tests\Builders\SystemRoleBuilder;
use Tests\Traits\UserDirector;

class SystemRoleDirector extends Director
{
    use UserDirector;

    protected SystemRoleBuilder $systemroleBuilder;

    protected function __init()
    {
        $this->systemroleBuilder = new SystemRoleBuilder;
    }

    function createSystemRole(string $name, array $permissions, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): SystemRole
    {
        $builder = $this->systemroleBuilder;
        $app = App::i();

        $builder->reset();

        $builder->setName($name);
    
        if ($fill_requered_properties) {
            $builder->fillRequiredProperties();
        }

        foreach($permissions as $permission) {
            $this->systemroleBuilder->addPermission($permission);
        }

        if ($save) {
            if($disable_access_control) $app->disableAccessControl();
            $builder->save($flush);
            if($disable_access_control) $app->enableAccessControl();
        }

        return $builder->getInstance();
    }

    function createUserAdminRole(?string $name = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): SystemRole
    {
        $system_role = $this->createSystemRole(
            name: $name ?: 'User Admin',
            permissions: [
                "user.@control",
                "user.create",
                "user.deleteAccount",
                "user.destroy",
                "user.modify",
                "user.modifyReadonlyData",
                "user.remove",
                "user.viewPrivateData",
                "user.viewPrivateFiles",
            ],
            fill_requered_properties: $fill_requered_properties,
            save: $save,
            flush: $flush,
            disable_access_control: $disable_access_control
        );

        return $system_role;
    }

    function createAgentAdminRole(?string $name = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): SystemRole
    {
        $system_role = $this->createSystemRole(
            name: $name ?: 'Agent Admin',
            permissions: [
                "agent.@control",
                "agent.archive",
                "agent.changeOwner",
                "agent.changeType",
                "agent.changeUserProfile",
                "agent.create",
                "agent.createAgentRelation",
                "agent.createAgentRelationWithControl",
                "agent.createSealRelation",
                "agent.destroy",
                "agent.lock",
                "agent.modify",
                "agent.modifyReadonlyData",
                "agent.publish",
                "agent.remove",
                "agent.removeAgentRelation",
                "agent.removeAgentRelationWithControl",
                "agent.removeSealRelation",
                "agent.unarchive",
                "agent.viewPrivateData",
                "agent.viewPrivateFiles",
            ],
            fill_requered_properties: $fill_requered_properties,
            save: $save,
            flush: $flush,
            disable_access_control: $disable_access_control
        );

        return $system_role;
    }

    function createSpaceAdminRole(?string $name = null, bool $fill_requered_properties = true, bool $save = true, bool $flush = true, $disable_access_control = false): SystemRole
    {
        $system_role = $this->createSystemRole(
            name: $name ?: 'Space Admin',
            permissions: [
                "space.@control",
                "space.archive",
                "space.changeOwner",
                "space.create",
                "space.createAgentRelation",
                "space.createAgentRelationWithControl",
                "space.createSealRelation",
                "space.destroy",
                "space.lock",
                "space.modify",
                "space.modifyReadonlyData",
                "space.publish",
                "space.remove",
                "space.removeAgentRelation",
                "space.removeAgentRelationWithControl",
                "space.removeSealRelation",
                "space.unarchive",
                "space.viewPrivateData",
                "space.viewPrivateFiles",
            ],
            fill_requered_properties: $fill_requered_properties,
            save: $save,
            flush: $flush,
            disable_access_control: $disable_access_control
        );

        return $system_role;
    }
}
