<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */

return array(
    'metadata' => array(
        'nomeCompleto' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Nome completo ou Razão Social'),
            'validations' => array(
                //'required' => \MapasCulturais\i::__('Seu nome completo ou jurídico deve ser informado.')
            ),
            'available_for_opportunities' => true
        ),

        'nomeSocial' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Nome Social'),
            'available_for_opportunities' => true,
        ),

        'escolaridade' => array(
            'private' => false,
            'label' => \MapasCulturais\i::__('Escolaridade'),
            'type' => 'select',
            'options' => array(
               '' => MapasCulturais\i::__('Não Informar'),
                MapasCulturais\i::__('Fundamental Incompleto'),
                MapasCulturais\i::__('Fundamental Completo'),
                MapasCulturais\i::__('Médio Incompleto'),
                MapasCulturais\i::__('Médio Completo'),
                MapasCulturais\i::__('Superior Completo'),
                MapasCulturais\i::__('Superior Incompleto'),
                MapasCulturais\i::__('Pós-graduação'),
                MapasCulturais\i::__('Sem formação'),
            ),
            'available_for_opportunities' => true,
        ),

        'pessoaDeficiente' => array(
            'label' => 'Pessoa com deficiência',
            'type' => 'multiselect',
            'options' => [
                MapasCulturais\i::__('Visual'),
                MapasCulturais\i::__('Mental'),
                MapasCulturais\i::__('Física'),
                MapasCulturais\i::__('Auditiva'),
            ],
            'available_for_opportunities' => true
        ),

        'comunidadesTradicional' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Comunidades tradicionais'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não sou'),
                MapasCulturais\i::__('Comunidade extrativista'),
                MapasCulturais\i::__('Comunidade ribeirinha'),
                MapasCulturais\i::__('Comunidade rural'),
                MapasCulturais\i::__('Povos indígenas/originários'),
                MapasCulturais\i::__('Comunidades de pescadores(as) artesanais'),
                MapasCulturais\i::__('Povos ciganos'),
                MapasCulturais\i::__('Povos de terreiro'),
                MapasCulturais\i::__('Povos de quilombola'),
            ),
            'available_for_opportunities' => true
        ),

        'comunidadesTradicionalOutros' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Digite sua comunidade tradicional'),
            'available_for_opportunities' => true
        ),

        'documento' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('CPF ou CNPJ'),
            'serialize' => function($value, $entity = null){
                /**@var MapasCulturais\App $this */
                $key = "hook:documento:{$entity}";
                if(!$this->rcache->contains($key)){
                    if($entity->type && $entity->type->id == 1){
                        $entity->cpf = $value;
                    }else if($entity->type && $entity->type->id == 2){
                        $entity->cnpj = $value;

                    }
                   $this->rcache->save($key, 1);
                }

                return $value;
            },
            'available_for_opportunities' => true
        ),

        'cnpj' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('CNPJ'),
            'serialize' => function($value, $entity = null){
                /**@var MapasCulturais\App $this */
                $key = "hook:cnpj:{$entity}";
                if(!$this->rcache->contains($key)){
                    if($entity->type && $entity->type->id == 2){
                        $entity->documento = $value;
                    }
                    $this->rcache->save($key, 1);
                }
                return $value;
            },
            'validations' => array(
                'v::cnpj()' => \MapasCulturais\i::__('O número de CNPJ informado é inválido.')
             ),
            'available_for_opportunities' => true,
        ),
        'cpf' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('CPF'),
            'serialize' => function($value, $entity = null){
                $key = "hook:cpf:{$entity}";
                if(!$this->rcache->contains($key)){
                    /**@var MapasCulturais\App $this */
                    if($entity->type && $entity->type->id == 1){
                        $entity->documento = $value;
                    }
                    $this->rcache->save($key, 1);
                }
                return $value;
            },
            'validations' => array(
                'v::cpf()' => \MapasCulturais\i::__('O número de CPF informado é inválido.')
             ),
            'available_for_opportunities' => true,
        ),

        'raca' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Raça/cor'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Branca' => \MapasCulturais\i::__('Branca'),
                'Preta' => \MapasCulturais\i::__('Preta'),
                'Amarela' => \MapasCulturais\i::__('Amarela'),
                'Parda' => \MapasCulturais\i::__('Parda'),
                'Indígena' => \MapasCulturais\i::__('Indígena')
            ),
            'available_for_opportunities' => true
        ),

        'dataDeNascimento' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Data de Nascimento/Fundação'),
            'type' => 'date',
            'serialize' => function($value, $entity = null){
               $this->hook("entity(<<*>>).save:before", function() use ($entity){
                    /** @var MapasCulturais\Entity $entity */
                    if($this->equals($entity)){
                        $this->idoso = 1; 
                    }
               });
               return (new DateTime($value))->format("Y-m-d");
            },
            'validations' => array(
                'v::date("Y-m-d")' => \MapasCulturais\i::__('Data inválida').'{{format}}',
            ),
            'available_for_opportunities' => true
        ),
        'idoso' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Pessoa idosa'),
            'type' => 'readonly',
            'serialize' => function($value, $entity = null){
                if($entity->dataDeNascimento){
                    $today = new DateTime('now');
                    $calc = (new DateTime($entity->dataDeNascimento))->diff($today);
                    return ($calc->y >= 60) ? "1" : "0";
                }else{
                    return null;
                }
            },
            'available_for_opportunities' => true
        ),

        'localizacao' => array(
            'label' => \MapasCulturais\i::__('Localização'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Pública' => \MapasCulturais\i::__('Pública'),
                'Privada' => \MapasCulturais\i::__('Privada')
            )
        ),

        'genero' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Gênero'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Mulher Cis' => \MapasCulturais\i::__('Mulher Cis'),
                'Homem Cis' => \MapasCulturais\i::__('Homem Cis'),
                'Mulher Trans/travesti' => \MapasCulturais\i::__('Mulher Trans/travesti'),
                'Homem Trans' => \MapasCulturais\i::__('Homem Trans'),
                'Não Binárie/outra variabilidade' => \MapasCulturais\i::__('Não Binárie/outra variabilidade'),
                'Não declarada' => \MapasCulturais\i::__('Não declarada'),
            ),
            'available_for_opportunities' => true,
            'field_type' => 'select'
        ),

        'orientacaoSexual' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Orientação Sexual'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Heterossexual' => \MapasCulturais\i::__('Heterossexual'),
                'Lésbica' => \MapasCulturais\i::__('Lésbica'),
                'Gay' => \MapasCulturais\i::__('Gay'),
                'Bissexual' => \MapasCulturais\i::__('Bissexual'),
                'Assexual' => \MapasCulturais\i::__('Assexual'),
                'Outras' => \MapasCulturais\i::__('Outras')
            ),
            'available_for_opportunities' => true
        ),
        'agenteItinerante' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Agente Itinerante'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Sim' => \MapasCulturais\i::__('Sim'),
                'Não' => \MapasCulturais\i::__('Não'),
            ),
            'available_for_opportunities' => true
        ),
        
        
        
        
        
        
        'emailPublico' => array(
            'label' => \MapasCulturais\i::__('Email Público'),
            'validations' => array(
                'v::email()' => \MapasCulturais\i::__('O endereço informado não é email válido.')
            ),
            'available_for_opportunities' => true,
            'field_type' => 'email'
        ),

        'emailPrivado' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Email Privado'),
            'validations' => array(
                //'required' => \MapasCulturais\i::__('O email privado é obrigatório.'),
                'v::email()' => \MapasCulturais\i::__('O endereço informado não é um email válido.')
            ),
            'available_for_opportunities' => true,
            'field_type' => 'email'
        ),

        'telefonePublico' => array(
            'label' => \MapasCulturais\i::__('Telefone Público'),
            'type' => 'string',
            'validations' => array(
                'v::brPhone()' => \MapasCulturais\i::__('O número de telefone informado é inválido.')
            ),
            'available_for_opportunities' => true,
            'field_type' => 'brPhone'
        ),

        'telefone1' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Telefone 1'),
            'type' => 'string',
            'validations' => array(
                'v::brPhone()' => \MapasCulturais\i::__('O número de telefone informado é inválido.')
            ),
            'available_for_opportunities' => true,
            'field_type' => 'brPhone'
        ),


        'telefone2' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Telefone 2'),
            'type' => 'string',
            'validations' => array(
                'v::brPhone()' => \MapasCulturais\i::__('O número de telefone informado é inválido.')
            ),
            'available_for_opportunities' => true,
            'field_type' => 'brPhone'
        ),

        'endereco' => array(
            'private' => function(){
                return !$this->publicLocation;
            },
            'label' => \MapasCulturais\i::__('Endereço'),
            'type' => 'text'
        ),
                    
        'En_CEP' => [
            'label' => \MapasCulturais\i::__('CEP'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Nome_Logradouro' => [
            'label' => \MapasCulturais\i::__('Logradouro'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Num' => [
            'label' => \MapasCulturais\i::__('Número'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Complemento' => [
            'label' => \MapasCulturais\i::__('Complemento'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Bairro' => [
            'label' => \MapasCulturais\i::__('Bairro'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Municipio' => [
            'label' => \MapasCulturais\i::__('Município'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Estado' => [
            'label' => \MapasCulturais\i::__('Estado'),
            'private' => function(){
                return !$this->publicLocation;
            },
            'type' => 'select',

            'options' => array(
                'AC'=>'Acre',
                'AL'=>'Alagoas',
                'AP'=>'Amapá',
                'AM'=>'Amazonas',
                'BA'=>'Bahia',
                'CE'=>'Ceará',
                'DF'=>'Distrito Federal',
                'ES'=>'Espírito Santo',
                'GO'=>'Goiás',
                'MA'=>'Maranhão',
                'MT'=>'Mato Grosso',
                'MS'=>'Mato Grosso do Sul',
                'MG'=>'Minas Gerais',
                'PA'=>'Pará',
                'PB'=>'Paraíba',
                'PR'=>'Paraná',
                'PE'=>'Pernambuco',
                'PI'=>'Piauí',
                'RJ'=>'Rio de Janeiro',
                'RN'=>'Rio Grande do Norte',
                'RS'=>'Rio Grande do Sul',
                'RO'=>'Rondônia',
                'RR'=>'Roraima',
                'SC'=>'Santa Catarina',
                'SP'=>'São Paulo',
                'SE'=>'Sergipe',
                'TO'=>'Tocantins',
            )
        ],
        'En_Pais' => [
            'label' => \MapasCulturais\i::__('País'),
            'type' => 'select',
            'default' => function(){
                $app = MapasCulturais\App::i();
                return $app->config['app.defaultCountry'];
            },
            'options' => [
                'AD' => 'Andorra',
                'AR' => 'Argentina',
                'BO' => 'Bolivia',
                'BR' => 'Brasil',
                'CL' => 'Chile',
                'CO' => 'Colombia',
                'CR' => 'Costa Rica',
                'CU' => 'Cuba',
                'EC' => 'Ecuador',
                'SV' => 'El Salvador',
                'ES' => 'España',
                'GT' => 'Guatemala',
                'HN' => 'Honduras',
                'MX' => 'México',
                'NI' => 'Nicarágua',
                'PA' => 'Panamá',
                'PY' => 'Paraguay',
                'PE' => 'Perú',
                'PT' => 'Portugal',
                'DO' => 'República Dominicana',
                'UY' => 'Uruguay',
                'VE' => 'Venezuela',
            ]
        ],

        'site' => array(
            'label' => \MapasCulturais\i::__('Site'),
            'validations' => array(
                "v::url()" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
        'facebook' => array(
            'label' => \MapasCulturais\i::__('Facebook'),
            'validations' => array(
                "v::url('facebook.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
        'twitter' => array(
            'label' => \MapasCulturais\i::__('Twitter'),
            'validations' => array(
                "v::url('twitter.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
        'instagram' => array(
            'label' => \MapasCulturais\i::__('Instagram'),
            'validations' => array(
                "v::startsWith('@')" => \MapasCulturais\i::__("O usuário informado é inválido. Informe no formato @usuario e tente novamente")
            ),
            'available_for_opportunities' => true
        ),
        'linkedin' => array(
            'label' => \MapasCulturais\i::__('Linkedin'),
            'validations' => array(
                "v::url('linkedin.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
        'spotify' => array(
            'label' => \MapasCulturais\i::__('Spotify'),
            'validations' => array(
                "v::url('open.spotify.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
        'youtube' => array(
            'label' => \MapasCulturais\i::__('YouTube'),
            'validations' => array(
                "v::url('youtube.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
        'pinterest' => array(
            'label' => \MapasCulturais\i::__('Pinterest'),
            'validations' => array(
                "v::url('pinterest.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ),
            'available_for_opportunities' => true
        ),
    ),
    'items' => array(
        1 => array( 'name' => \MapasCulturais\i::__('Individual' )),
        2 => array( 'name' => \MapasCulturais\i::__('Coletivo') ),
    )
);
