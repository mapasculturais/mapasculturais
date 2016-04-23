<?php

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\Space;
use MapasCulturais\Entities\File;
use MapasCulturais\Entities\MetaList;

$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return array( 
    'change id of space type 201 to 210' => function() use($conn) {
        $conn->executeQuery("UPDATE space SET type = 210 WHERE type = 201");
    },
    
    'migrate geo metadata' => function() use($conn) {
        $conn->executeQuery("UPDATE space_meta SET key = 'En_Municipio' WHERE key = 'geoMunicipio'");
        $conn->executeQuery("UPDATE agent_meta SET key = 'En_Municipio' WHERE key = 'geoMunicipio'");
        
        foreach(['agent_meta', 'space_meta'] as $table){
            $ids = $conn->fetchAll("SELECT object_id FROM {$table} WHERE key = 'En_Municipio'");
            $ids = array_map(function($e) {
                return $e['object_id'];
            }, $ids);

            foreach($ids as $id){
                $conn->executeQuery("INSERT INTO {$table} (object_id, key, value) VALUES ({$id}, 'En_Estado', 'CE')");
            }
        }
    },
            
    'import ceara data' => function() use( $conn, $app ) {
        return true; // já executou
        /*
          [0] => AtividadePatrm
          [1] => BairroPesq
          [2] => DtPesq
          [3] => ID
          [4] => ID_F2
          [5] => ID_F3
          [6] => ID_F4
          [7] => ID_F5
          [8] => InteresseTomb
          [9] => LigMantened
          [10] => NumFicha
          [11] => Pesquisador
          [12] => Status
          [13] => _edit_last
          [14] => _edit_lock
          [15] => _mpv_inmap
          [16] => _mpv_location
          [17] => _mpv_pin
          [18] => _oembed_076b752f5a584953888cde02433bd096
          [19] => _oembed_11dff52bf7cf51715fafd677bac8ea0a
          [20] => _oembed_16b77d2476b690910e4fec0c34f00c86
          [21] => _oembed_1a4dd169ad087afea449a5d174a05bb6
          [22] => _oembed_1d18fc598e4b6d25d2eb482edc87463f
          [23] => _oembed_1efc6184527734252c79ccc9dffa0c3a
          [24] => _oembed_1fb19d84a25914f2602efa1736c992a2
          [25] => _oembed_278c51f6e2ad4ce2a196d5e6c8313cf0
          [26] => _oembed_2ebbf948946a4e41ca09d67e81e433e0
          [27] => _oembed_361a49ddb4013bb48a807ccda1dbf06b
          [28] => _oembed_43b941dc9456d30878ecc48e9fc3adaa
          [29] => _oembed_5148cee377bc7af6f30427d6c8529e73
          [30] => _oembed_5fac6d3869591451256f8bafffbd1f3a
          [31] => _oembed_6afb031b2d0e0240e85be7d407672bb9
          [32] => _oembed_6d19aa4497f998dc7b8bd097063b6e9d
          [33] => _oembed_7d674e27613a2bbbccf52591918d8525
          [34] => _oembed_d2f88d551cf395bab3e1d79d38cbda90
          [35] => _oembed_d4bfeb34323ebd0433cdf6974d3754b7
          [36] => _oembed_d8c6b9f2e973f6e5b897da007c923a01
          [37] => _oembed_e8932f1df2f052666eaecb8adc96ce77
          [38] => _oembed_eca9c28aa68fcbee403d1053ad17ae55
          [39] => _oembed_f47d4da8202fc247db5f11843d716407
          [40] => _oembed_f64ace249129ed09b3ef4215ab2e30a4
          [41] => _oembed_fe98b7d883173718415f457189aff314
          [42] => _thumbnail_id
          [43] => _wp_old_slug
          [44] => apelido
          [45] => bairro
          [46] => bairro_de_origem
          [47] => cep
          [48] => complemento
          [49] => data_criacao
          [50] => data_tombamento
          [51] => dirigente
          [52] => dirigente_2
          [53] => dirigente_3
          [54] => dirigente_4
          [55] => dirigente_5
          [56] => e-mail
          [57] => endereco
          [58] => files
          [59] => frenda
          [60] => horario_visita
          [61] => local_de_origem
          [62] => num_componentes
          [63] => numero
          [64] => origem_etnica
          [65] => pesquisador
          [66] => post_content
          [67] => post_title
          [68] => post_type
          [69] => publico_amount
          [70] => sinopse
          [71] => site
          [72] => telefone
          [73] => telefone_2
          [74] => telefone_3
          [75] => tipo_de_tombamento
          [76] => tombado
          [77] => visitada
          [78] => youtube_link
         */

        $data = json_decode(file_get_contents('/tmp/ceara-data.json'));

        $admin_user = $app->repo('User')->find(1);
        $admin_profile = $app->repo('Agent')->find(1);

        $space_types = [
            'post' => $app->getRegisteredEntityTypeById('MapasCulturais\Entities\Space', 299),
            'patrimonio-material' => $app->getRegisteredEntityTypeById('MapasCulturais\Entities\Space', 200),
            'patrimonio-imaterial' => $app->getRegisteredEntityTypeById('MapasCulturais\Entities\Space', 201),
            'equipamento' => $app->getRegisteredEntityTypeById('MapasCulturais\Entities\Space', 199),
        ];

        $endereco = function($ed) {
            $result = '';
            if (@$ed->endereco) {
                $result = $ed->endereco;
            }

            if (@$ed->numero && @$ed->endereco) {
                $result .= ' ' . $ed->numero;
            }

            if (@$ed->complemento && $result) {
                $result .= ' ' . $ed->complemento;
            }

            if (@$ed->bairro) {
                $result .= $result ? ', ' . $ed->bairro : $ed->bairro;
            }

            if (@$ed->cep) {
                $result .= $ed->cep ? ', CEP: ' . $ed->cep : ' CEP: ' . $ed->cep;
            }

            return $result;
        };


        $youtube_id_from_url = function($url) {
            $pattern = '%^# Match any youtube URL
                    (?:https?://)?  # Optional scheme. Either http or https
                    (?:www\.)?      # Optional www subdomain
                    (?:             # Group host alternatives
                      youtu\.be/    # Either youtu.be,
                    | youtube\.com  # or youtube.com
                      (?:           # Group path alternatives
                        /embed/     # Either /embed/
                      | /v/         # or /v/
                      | /watch\?v=  # or /watch\?v=
                      )             # End path alternatives.
                    )               # End host alternatives.
                    ([\w-]{10,12})  # Allow 10-12 for 11 char youtube id.
                    $%x'
            ;
            $result = preg_match($pattern, $url, $matches);
            if (false !== $result) {
                return $matches[1];
            }
            return false;
        };

        foreach ($data as $i => $ed) {
            echo "=====> ({$i}) importando {$ed->post_type} de id {$ed->ID} ({$ed->post_title}) \n";

            if ($ed->post_type == 'instituicao') {
                $entity = new Agent;
                $entity->type = $app->getRegisteredEntityTypeById($entity, 2);
                $entity->user = $admin_user;
            } else {
                $entity = new Space;
                $entity->type = $space_types[$ed->post_type];
                $entity->owner = $admin_profile;

                if (@$ed->publico_amount) {
                    $entity->capacidade = $ed->publico_amount;
                }

                if (@$ed->horario_visita) {
                    $entity->horario = $ed->horario_visita;
                }
            }


            $entity->name = $ed->post_title;
            $entity->longDescription = $ed->post_content;

            if (@$ed->sinopse) {
                $entity->shortDescription = $ed->sinopse;
            }

            $entity->endereco = $endereco($ed);

            if (@$ed->telefone) {
                $entity->telefonePublico = $ed->telefone;
            }

            if (@$ed->site) {
                if (!preg_match('#^http#', $ed->site)) {
                    $ed->site = 'http://' . $ed->site;
                }

                $entity->site = $ed->site;
            }

            if (@$ed->telefone2) {
                $entity->telefone1 = $ed->telefone2;
            }

            if (@$ed->telefone3) {
                $entity->telefone2 = $ed->telefone3;
            }

            if (@$ed->{'e-mail'}) {
                $entity->emailPublico = $ed->{'e-mail'};
            }

            //@TODO: data de criação

            if (isset($ed->_mpv_location) && is_object($ed->_mpv_location)) {
                $entity->location = new MapasCulturais\Types\GeoPoint($ed->_mpv_location->lon, $ed->_mpv_location->lat);
            }

            if (@$ed->terms) {
                $tags = ['NÃO REVISADO'];
                $areas = [];

                foreach ($ed->terms as $taxo => $terms) {
                    if ($taxo == 'post_tag') {
                        $prefixo = '';
                    } else {
                        $prefixo = $taxo . ': ';
                    }


                    foreach ($terms as $term) {
                        $term = trim($term);
                        $lterm = strtolower($term);
                        if (!$term) {
                            continue;
                        }

                        if($taxo == 'pratica-cultural'){
                            if($lterm == 'arte e cultura digital'){
                                $areas[] = 'Arte Digital';
                                $areas[] = 'Cultura Digital';
                                continue;

                            }elseif($lterm == 'audiovisual'){
                                $areas[] = 'Audiovisual';
                                continue;

                            }elseif($lterm == 'circo'){
                                $areas[] = 'Circo';
                                continue;

                            }elseif($lterm == 'cultura popular'){
                                $areas[] = 'Cultura Popular';
                                continue;

                            }elseif($lterm == 'cultura popular tradicional'){
                                $areas[] = 'Cultura Popular';
                                $tags[] = $lterm;
                                continue;

                            }elseif($lterm == 'dança'){
                                $areas[] = 'Dança';
                                continue;

                            }elseif($lterm == 'literatura'){
                                $areas[] = 'Literatura';
                                continue;

                            }elseif($lterm == 'música'){
                                $areas[] = 'Música';
                                continue;

                            }elseif($lterm == 'música'){
                                $areas[] = 'Música';
                                continue;

                            }elseif($lterm == 'artes cênicas'){
                                $areas[] = 'Teatro';
                                continue;

                            }elseif($lterm == 'artes cênicas'){
                                $areas[] = 'Teatro';
                                continue;

                            }elseif($lterm == 'artes visuais'){
                                $areas[] = 'Artes Visuais';
                                continue;

                            }elseif($lterm == 'artesanato'){
                                $areas[] = 'Artesanato';
                                continue;

                            }elseif($lterm == 'cultura afro-brasileira'){
                                $areas[] = 'Cultura Negra';
                                $tags[] = 'Cultura Afro-Brasileira';
                                continue;

                            }elseif($lterm == 'design'){
                                $areas[] = 'Design';
                                continue;

                            }elseif($lterm == 'esporte'){
                                $areas[] = 'Esporte';
                                continue;

                            }elseif($lterm == 'fotografia'){
                                $areas[] = 'Fotografia';
                                continue;

                            }elseif($lterm == 'gastronomia'){
                                $areas[] = 'Gastronomia';
                                continue;

                            }
                        }

                        $_term = $prefixo . $term;
                        $tags[] = $_term;

                    }
                }

                $entity->terms['tag'] = array_unique($tags);
                $entity->terms['area'] = array_unique($areas);
            }

            $entity->save();

            $tmp_path = '/tmp/uploads/';

            if (@$ed->files && is_array($ed->files)) {
                foreach ($ed->files as $i => $f) {
                    $tmp_file = $tmp_path . $f->path;
                    $file_info = pathinfo($tmp_file);

                    if (!file_exists($tmp_file)) {
                        continue;
                    }

                    if ($i == 0) {
                        $avatar_basename = 'avatar-' . $file_info['basename'];
                        $avatar_tmp = $file_info['dirname'] . '/' . $avatar_basename;
                        echo " ---- > file " . $avatar_basename . "\n";

                        copy($tmp_file, $avatar_tmp);

                        $file = new File([
                            'name' => $avatar_basename,
                            'type' => $f->type,
                            'tmp_name' => $avatar_tmp,
                            'error' => 0,
                            'size' => filesize($tmp_file)
                        ]);

                        $file->owner = $entity;
                        $file->group = 'avatar';
                        $file->save();
                    }

                    echo " ---- > file " . $file_info['basename'] . "\n";
                    $file = new File([
                        'name' => $file_info['basename'],
                        'type' => $f->type,
                        'tmp_name' => $tmp_file,
                        'error' => 0,
                        'size' => filesize($tmp_file)
                    ]);

                    $file->owner = $entity;
                    $file->group = 'gallery';
                    $file->save();
                }
            }

            if (@$ed->youtube_link) {
                $vidID = $youtube_id_from_url($ed->youtube_link);

                $url = "http://gdata.youtube.com/feeds/api/videos/" . $vidID;
                $doc = new DOMDocument;
                if ($doc->load($url)) {
                    $title = $doc->getElementsByTagName("title")->item(0)->nodeValue;

                    echo " => adicionando vídeo '$title' ($ed->youtube_link)\n";

                    $metalist = new MetaList;

                    $metalist->owner = $entity;
                    $metalist->group = 'videos';
                    $metalist->title = $title;
                    $metalist->value = $ed->youtube_link;
                    $metalist->save();
                }
            }
        }

        return false;
    },

    'importando o banco de dados do sinf' => function() use ($conn){
        return true;
        $data = json_decode(file_get_contents('/tmp/sinf.json'));

        $emails = [];

        $secretarias = [];

        $nu = 0;

        $__areas = [
"Artes Cênicas"                 => ["Teatro"],
"Artes Gráficas"                => ["Design", "Artes Visuais"],
"Audiovisual"                   => ["Audiovisual"],
"Gastronomia"                   => ["Gastronomia"],
"Artes Visuais"                 => ["Artes Visuais"],
"Mídia - Imprensa/Rádio/TV"     => ["Rádio", "Televisão"],
"Artesanato"                    => ["Artesanato"],
"Literatura"                    => ["Literatura"],
"Música"                        => ["Música"],
"Patrimônio Histórico Cultural" => ["Patrimônio Material", "Patrimônio Imaterial"],
"À classificar"                 => ["Outros"],
"Produção e Gestão Cultural"    => ["Produção Cultural", "Gestão Cultural"],
"Prof. da Cultura Tradicional"  => ["Cultura Popular"],
"Turismo Cultural"              => ["Turismo"]
        ];

        foreach($data as $u){
            $nu++;
            echo "criando usuário $nu ($u->email)\n";

            $user = new MapasCulturais\Entities\User;

            $user->email = $u->email ? $u->email : 'REVISAR';
            $user->authProvider = 1;
            $user->authUid = '';

            $user->save();

            $profile = null;

            foreach($u->agents as $e){

                echo "--> criando AGENTE $e->name\n";

                $entity = new MapasCulturais\Entities\Agent($user);

                $areas = [];

                if($e->grupos){
                    foreach($e->grupos as $grupo){
                        $areas = array_merge($areas, $__areas[$grupo]);
                    }

                    $entity->terms['area'] = array_unique($areas);
                }

                if(isset($e->areas) && $e->areas){
                    $entity->terms['tag'] = array_unique($e->areas);
                }

                if(!isset($secretarias[$e->geoMunicipio])){
                    $secretarias[$e->geoMunicipio] = $entity;
                }

                $entity->type = (int) $e->type;
                $entity->name = $e->name;
                $entity->emailPrivado = $u->email;
                $entity->nomeCompleto = isset($e->nomeCompleto) ? $e->nomeCompleto : $e->name;
                $entity->documento = $e->documento;

                $l = (array) $e->location;
                if($l['latitude'] && $l['longitude']) $entity->location = $l;

                $entity->endereco = $e->endereco;
                $entity->geoBairro = $e->geoBairro;
                $entity->geoMunicipio = $e->geoMunicipio;
                if(isset($e->cep)) $entity->cep = $e->cep;

                if(isset($e->telefonePublico)) $entity->telefonePublico = $e->telefonePublico;
                if(isset($e->telefone1)) $entity->telefone1 = $e->telefone1;
                if(isset($e->telefone2)) $entity->telefone2 = $e->telefone2;

                if(isset($e->dataDeNascimento)) $entity->dataDeNascimento = $e->dataDeNascimento;
                if(isset($e->site)) $entity->site = $e->site;
                if(isset($e->genero)) $entity->genero = $e->genero;


                if((int) $e->type === 1){
                    $entity->publicLocation = false;
                }else{
                    $entity->publicLocation = true;
                }

                $entity->save();

                if(!$profile){
                    $profile = $entity;
                    $user->profile = $profile;
                    $user->save();
                }
            }

            continue;

/*
(
    [_space] => 1
    [type] => Biblioteca
    [name] => BIBLIOTECA JORGE AMADO
    [email] => sec.culturaturismo@hotmail.com
    [site] =>
    [endereco] => Rua Migueira Braga, 760, Centro, Miraíma, Ceará
    [cep] => 62530-000
    [telefonePublico] => (88) 3630-1312
    [telefone1] => (88) 9210-9882
    [telefone2] =>
    [geoBairro] => Centro
    [geoMunicipio] => Miraíma
    [id] => 5122
    [location] => stdClass Object
        (
            [latitude] => -3.569051
            [longitude] => -39.966623
        )

)
 *
 * TIPOS:
    [0] => Biblioteca
    [1] => Centro Cultural
    [2] => Cinema
    [3] => Teatro
    [4] => Museu

*/
            $space_types = [
                'Biblioteca' => 21, // biblioteca privada
                'Centro Cultural' => 41, // centro cultural privado
                'Cinema' => 14, // sala de cinema
                'Teatro' => 31, // teatro privado
                'Museu' => 61 // museu privado
            ];
            foreach($u->spaces as $e){
                echo "--> criando ESPAÇO $e->name\n";

                $entity = new MapasCulturais\Entities\Space;
                $entity->type = isset($space_types[$e->type]) ? $space_types[$e->type] : 199;
                $entity->name = $e->name;
                $entity->emailPrivado = $u->email;

                $l = (array) $e->location;
                if($l['latitude'] && $l['longitude']) $entity->location = $l;

                $entity->endereco = $e->endereco;
                $entity->geoBairro = $e->geoBairro;
                $entity->geoMunicipio = $e->geoMunicipio;
                if(isset($e->cep)) $entity->cep = $e->cep;

                if(isset($e->telefonePublico)) $entity->telefonePublico = $e->telefonePublico;
                if(isset($e->telefone1)) $entity->telefone1 = $e->telefone1;
                if(isset($e->telefone2)) $entity->telefone2 = $e->telefone2;

                if(isset($e->site)) $entity->site = $e->site;

                if($profile){
                    $entity->owner = $profile;
                }else{
                    $entity->owner = $secretarias[$e->geoMunicipio];
                }

                $entity->save();
            }
        }
        MapasCulturais\App::i()->em->flush();
    },
            
    'fix sinf users' => function() use ($conn){
        $rs = $conn->fetchAll("select count(ID) as num, email from usr GROUP BY email ORDER BY num DESC");
        
        foreach($rs as $r){
            if($r['num'] > 1){
                $ids = [];
                $first_id = null;
                $users = $conn->fetchAll("SELECT * FROM usr WHERE email = '{$r['email']}'");
                
                foreach ($users as $user){
                    if(!$first_id){
                        $first_id = $user['id'];
                    }else{
                        $ids[] = $user['id'];
                        echo "MOVENDO AGENTES DO USUÁRIO {$user['id']}\n";
                        $conn->executeQuery("UPDATE agent SET user_id = $first_id WHERE user_id = {$user['id']}");
                    }
                }
                $ids = implode(',', $ids);
                echo " ----> DELETANDO USUÁRIOS DE IDS $ids\n\n";
                $conn->executeQuery("DELETE FROM usr WHERE id IN($ids)");
            }
        }
    }
);
