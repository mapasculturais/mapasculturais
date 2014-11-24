<?php
$app = MapasCulturais\App::i();
$em = $app->em;
$conn = $em->getConnection();

return array(
    'import data' => function () use($app, $conn){
        $owner = $app->repo('Agent')->find(1);
    
        $filename = isset($app->config['rs.importDataFilename']) ? $app->config['rs.importDataFilename'] : '/tmp/cultura-rs.json';
        $posts = json_decode(file_get_contents($filename));
        
        /*  SO PARA DEBUG : agrupa os posts por slug
        $slugs = [];
        foreach($posts as $post){
            foreach($post->categories as $cat){
                if(!isset($slugs[$cat->slug])){
                    $slugs[$cat->slug] = $cat;
                    $slugs[$cat->slug]->titles = [];
                    foreach($posts as $p){
                        foreach($p->categories as $c){
                            if($c->slug === $cat->slug){
                                $slugs[$cat->slug]->titles[] = $p->title;
                            }
                        }
                           
                    }
                }
            }
        }
        */
        
        $space_categories = [
            'arquivos' =>                                   [50, ['Arquivo']],
            'bibliotecas' =>                                [20, ['Leitura', 'Literatura', 'Livro']],
            'centro-cultural' =>                            [40, ['Outros']],
            'bam-tombado' =>                                [200,['Outros']],
            'espacos-publicos-para-a-exibicao-de-filmes' => [13, ['Audiovisual', 'Cinema']],
            'museus' =>                                     [60, ['Outros']],
            'teatros' =>                                    [30, ['Teatro']],
            'cineclube' =>                                  [11, ['Audiovisual', 'Cinema']]
            
//            'Centro de Documentação Pública' => 70, // não achei post com essa categoria
        ];
        
        $agent_categories = [
            'teatro-coletivo-de-artes-cenicas' =>   [2, ['Teatro']],
            'danca' =>                              [2, ['Dança']],
            'circo' =>                              [2, ['Circo']],
            'agremiacoes-de-carnaval' =>            [2, ['Outros']],
            'escolas-de-samba' =>                   [2, ['Outros']]
        ];    
        
        $spaces = [];
        $agents = [];
        
        $rejected = [];
        
        
        $has_category = function($post, $slugs){
            if(!$post->categories){
                return false;
            }
            
            foreach($post->categories as $cat){
                if(is_array($slugs) && in_array($cat->slug, $slugs)){
                    return true;
                }else if(is_string($slugs) && $cat->slug === $slugs){
                    return true;
                }
            }
            
            return false;
        };
        
        $is_space = function($post) use($has_category, $space_categories){
            return $has_category($post, array_keys($space_categories));
        };
        
        $is_agent = function($post) use($has_category, $agent_categories){
            return $has_category($post, array_keys($agent_categories));
        };
        
        $populate_entity = function($entity, $post) use($has_category){
            $entity->name = $post->title;
            $entity->shortDescription = $post->short_description;
            $entity->description = $post->description;

            // localização
            if($post->lat && $post->lng){
                $entity->location = new MapasCulturais\Types\GeoPoint($post->lng, $post->lat);
            }

            // selo da secretaria
            if($has_category($post, 'instituicoes-da-sedac')){
                $entity->isVerified = true;
            }

            // email
            $email = '';

            $matches = null;
            if(preg_match('# *e-?mail: *([a-zA-Z0-9\.-_]+@([a-zA-Z0-9-_]+(\.[a-zA-Z0-9-_]+)+)) *#i', $post->short_description, $matches)){
                $email = $matches[1];
            }

            $matches = null;
            if(preg_match('# *e-?mail: *([a-zA-Z0-9\.-_]+@([a-zA-Z0-9-_]+(\.[a-zA-Z0-9-_]+)+)) *#i', $post->description, $matches)){
                $email = $matches[1];
            }

            if($email){
                $entity->emailPublico = $email;
            }
            
            // endereço
            
            // download de arquivos

        };
        
        foreach($posts as $post){
            $agent = null; $space = null;
            
            $space_owner = $owner;
            
            if($is_agent($post)){
                $agent = new MapasCulturais\Entities\Agent();
                $agent->owner = $owner;
                $agent->type = 2;
                $agent->terms['area'] = [];
                
                foreach($post->categories as $cat){
                    if(isset($agent_categories[$cat->slug])){
                        foreach($agent_categories[$cat->slug][1] as $term){
                            if(!in_array($term, $agent->terms['area'])){
                                $agent->terms['area'][] = $term;
                            }
                        }
                    }
                }
                
                $populate_entity($agent, $post);
                $agent->save();
                
                echo "INSERIDO AGENTE $agent->name ($agent->id)\n";
                
                $space_owner = $agent;
            }
            
            if($is_space($post)){
                $space = new MapasCulturais\Entities\Space();
                $space->owner = $space_owner;
                $types = [];
                if($has_category($post, 'cineclube') && $has_category($post, 'espacos-publicos-para-a-exibicao-de-filmes')){
                    $types[] = $space_categories['cineclube'][0];
                }else{
                    foreach($post->categories as $cat){
                        if(isset($space_categories[$cat->slug])){
                            $types[] = $space_categories[$cat->slug][0];
                        }
                    }
                }
                if(count($types) > 1){
                    echo " ESPAÇO COM MAIS DE UM TIPO, USANDO O PRIMEIRO: ";
                    print_r(['id' => $post-ID, 'title' => $post->title, 'cats' => $post->categories, 'tupes' => $types]);
                }
                
                if(!$types){
                    $rejected[] = $post;
                    continue;
                }
                
                $space->type = $types[0];
                $space->terms['area'] = [];
                
                foreach($post->categories as $cat){
                    if(isset($space_categories[$cat->slug])){
                        foreach($space_categories[$cat->slug][1] as $term){
                            if(!in_array($term, $space->terms['area'])){
                                $space->terms['area'][] = $term;
                            }
                        }
                    }
                }
                
                $populate_entity($space, $post);
                $space->save();
                
                echo "INSERIDO ESPAÇO $space->name ($space->id)\n";
            }
            
            if(is_null($agent) && is_null($space)){
                $rejected[] = ['id' => $post->ID, 'name' => $post->title];
            }
        }
        
        $app->em->flush();
        var_dump($rejected);
    }
);