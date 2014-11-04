<?php
namespace MapasCulturais;

$app = App::i();
$em = $app->em;
$conn = $em->getConnection();


return array(
    'alter table user add column profile_id' => function() use ($app, $conn){
        if($conn->fetchAll("SELECT column_name FROM information_schema.columns WHERE table_name = 'usr' AND column_name = 'profile_id'")){
            return true;
        }
            
    
        echo "adicionando coluna profile_id à tabela de usuários\n";
    
        $conn->executeQuery('ALTER TABLE usr ADD COLUMN profile_id INTEGER;');
        
        echo "criando user_profile_fk\n";
        $conn->executeQuery('ALTER TABLE ONLY usr ADD CONSTRAINT user_profile_fk FOREIGN KEY (profile_id) REFERENCES agent(id);');
        
        $agents = $conn->fetchAll("SELECT id, user_id FROM agent WHERE is_user_profile = TRUE");
        
        foreach($agents as $agent){
            echo "setando o user profile do usuário {$agent['user_id']} como o agente de id {$agent['id']}\n";
            $conn->executeQuery('UPDATE usr SET profile_id = ' . $agent['id'] . ' WHERE id = ' . $agent['user_id']);
        }
        
        echo "removendo a coluna is_user_profile da tabela agent\n";
        $conn->executeQuery('ALTER TABLE agent DROP COLUMN is_user_profile;');
    }
);
