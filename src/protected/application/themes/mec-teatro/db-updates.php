<?php

$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return [
    'importa dados de teatros' => function() use ( $app, $conn ) {
   
        $fields = array(
            'ID',
            'Nome',
            'Tipo',
            'Áreas',
            'Departamento',
            'Direccion',

            /* Aqui comenzamos el update */

            'acessibilidade',
            'teatros_aforo',
            'teatros_boca_escenario',
            'teatros_profundidad',
            'teatros_altura',
            'teatros_piso',
            'teatros_equipamento_lumnico',
            'teatros_equipamento_sonido',
            
            // Aqui vamos agrupar
            
            'Contactos 1',
            'Contactos 2',
            'Contactos 3',
        );
        
        $metadata_range = array(6,13);
        $aforoIndex = 7;
        $acessibilidadeIndex = 6;
        $contatos_range = array(14,16);
        $check_numeric_indexes = array(8,9,10);
        
        $data = file_get_contents(__DIR__ . '/metadados-teatros.csv');

        $data = explode("\n", $data);

        $labels = explode("\t", $data[0]);

        $teatros = [];
        
        $totalcols = count($fields);

        foreach ($data as $i => $line) {
            if ($i === 0)
                continue;

            $d = explode("\t", $line);

            if (count($d) != $totalcols) {
                echo "pulando linha $i porque aparentemente número de colunas não bate\n";
                continue;
            }

            foreach($d as $i => $val){
                $d[$i] = trim($val);
                if(trim($val) == '-'){
                    $d[$i] = '';
                }
            }

            $obj = new stdClass;

            $obj->__metadata = [];
            
            $obj->ID = $d[0];
            
            foreach ($fields as $key => $field_name) {
                
                if ($key < $metadata_range[0] || $key > $metadata_range[1])
                    continue; // apenas os metadados
                if (!empty($d[$key])) {
                    
                    if ($key == $aforoIndex) { //aforo
                        
                        if (is_numeric($d[$key])) { // se é numerico, guardamos como aforo
                            $obj->__metadata[$field_name] = intval($d[$key]);
                        } else { // se não, guardamos como detalhes de aforo
                            $obj->__metadata['teatros_aforo_detalles'] = $d[$key];
                        }
                        
                        continue;
                        
                    }
                    
                    if ($key == $acessibilidadeIndex) { //acessibilidade
                        
                        // já sabemos que nãoe está vazio, então, SIM
                        $obj->__metadata[$field_name] = 'Sim';
                        continue;
                        
                    }
                    
                    if (in_array($key, $check_numeric_indexes)) {
                        $d[$key] = str_replace(',', '.', $d[$key]);
                        if (is_numeric($d[$key])) { // se é numerico
                            $obj->__metadata[$field_name] = $d[$key];
                        } else {
                            echo "ERRO: Valor inválido para $field_name em teatro {$obj->ID} \n"; 
                            //var_dump($d[$key]);
                        }
                        
                        continue;
                    }
                    
                
                    $obj->__metadata[$field_name] = $d[$key];
                }
                
            }
            
            $obj->__contatos = '';
            
            foreach ($fields as $key => $field_name) {
                
                if ($key < $contatos_range[0] || $key > $contatos_range[1])
                    continue; // apenas os metadados
                
                if (!empty($d[$key])) 
                    $obj->__contatos .= $d[$key] . "\n";
                
            }
            
            if ($obj->__contatos != '') {
                $obj->__metadata['teatros_contactos_adicionales'] = $obj->__contatos;
            }
            
            $teatros[] = $obj;
            
        }

        // importa pro banco de dados
        foreach($teatros as $i => $teatro){
            
            /*
            $id = $conn->fetchColumn("SELECT nextval('space_id_seq'::regclass)");

            echo "$i - inserindo teatro de id ($id) - \"$teatro->name\"\n";

            $teatro->agent_id = $app->config['teatros.ownerAgentId'];
            $teatro->name = $conn->quote($teatro->name);

            $conn->executeQuery("
                INSERT INTO space (
                     id, location,  _geo_location,  name,  status,  type,  agent_id
                ) VALUES (
                    $id, '$teatro->location', $teatro->_geo_location, $teatro->name, 1, $teatro->type, $teatro->agent_id
                )
            ");


            $teatro->__metadata->num_sniic = "ES-$id";
            
            */
            
            echo "Processando metadados do teatro {$teatro->ID}\n";
            
            foreach($teatro->__metadata as $key => $val){
                
                echo "{$key}: $val \n";
                
                if ($val != '')
                    $conn->executeQuery("
                        INSERT INTO space_meta (
                            object_id, key, value
                        ) VALUES (
                            :id, '$key', :val
                        )", ['id' => $teatro->ID, 'val' => $val]);
            }
            
            echo "\n";
            
            /*
            foreach($teatro->__links as $link){
                $conn->executeQuery("
                    INSERT INTO metalist (
                        object_type, object_id, grp, title, value
                    ) VALUES (
                        'MapasCulturais\Entities\Space', '$id', 'links', :val, :val
                    )", ['val' => $link]);
            }
            */
        }
        
    },

];
