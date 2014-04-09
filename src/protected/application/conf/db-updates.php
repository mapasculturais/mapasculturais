<?php
use MapasCulturais\App;
return array(
    'virada cultural - eventos fakes'=> function(){
        if($this->config('mode') != 'development')
            return false;

        $mussum_ipsum = array(
            "Mussum ipsum cacilds, vidis litro abertis. Consetis adipiscings elitis. Pra lá , depois divoltis porris, paradis. Paisis, filhis, espiritis santis. Mé faiz elementum girarzis, nisi eros vermeio, in elementis mé pra quem é amistosis quis leo. Manduma pindureta quium dia nois paga. Sapien in monti palavris qui num significa nadis i pareci latim. Interessantiss quisso pudia ce receita de bolis, mais bolis eu num gostis.",
            "Suco de cevadiss, é um leite divinis, qui tem lupuliz, matis, aguis e fermentis. Interagi no mé, cursus quis, vehicula ac nisi. Aenean vel dui dui. Nullam leo erat, aliquet quis tempus a, posuere ut mi. Ut scelerisque neque et turpis posuere pulvinar pellentesque nibh ullamcorper. Pharetra in mattis molestie, volutpat elementum justo. Aenean ut ante turpis. Pellentesque laoreet mé vel lectus scelerisque interdum cursus velit auctor. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam ac mauris lectus, non scelerisque augue. Aenean justo massa.",
            "Casamentiss faiz malandris se pirulitá, Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Lorem ipsum dolor sit amet, consectetuer Ispecialista im mé intende tudis nuam golada, vinho, uiski, carirí, rum da jamaikis, só num pode ser mijis. Adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat.",
            "Cevadis im ampola pa arma uma pindureta. Nam varius eleifend orci, sed viverra nisl condimentum ut. Donec eget justis enim. Atirei o pau no gatis. Viva Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Delegadis gente finis. In sit amet mattis porris, paradis. Paisis, filhis, espiritis santis. Mé faiz elementum girarzis. Pellentesque viverra accumsan ipsum elementum gravidis.",
            "Forevis aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Copo furadis é disculpa de babadis, arcu quam euismod magna, bibendum egestas augue arcu ut est. Etiam ultricies tincidunt ligula, sed accumsan sapien mollis et. Delegadis gente finis. In sit amet mattis porris, paradis. Paisis, filhis, espiritis santis. Mé faiz elementum girarzis. Pellentesque viverra accumsan ipsum elementum gravida. Quisque vitae metus id massa tincidunt iaculis sed sed purus. Vestibulum viverra lobortis faucibus. Vestibulum et turpis.",
            "Vitis e adipiscing enim. Nam varius eleifend orci, sed viverra nisl condimentum ut. Donec eget justo enim. Atirei o pau no gatis. Quisque dignissim felis quis sapien ullamcorper varius tempor sem varius. Vivamus lobortis posuere facilisis. Sed auctor eros ac sapien sagittis accumsan. Integer semper accumsan arcu, at aliquam nisl sollicitudin non. Nullam pellentesque metus nec libero laoreet vitae vestibulum ante ultricies. Phasellus non mollis purus. Integer vel lacus dolor. Proin eget mi nec mauris convallis ullamcorper vel ac nulla. Nulla et semper metus."
        );


        $json = '
[
	{
		"name": "Praça Pedro Lessa",
		"events":
			[
				{ "hour": "22:00", "name": "Cido Garoto e Cururu de Sorocaba" },
				{ "hour": "23:00", "name": "Batuque de Umbigada" },
				{ "hour": "00:00", "name": "Nega Duda e o Samba do Recomcavo Bahiano" },
				{ "hour": "01:00", "name": "Batuntã" },
				{ "hour": "02:00", "name": "Instituto África Viva" },
				{ "hour": "03:00", "name": "Sambaqui" },
				{ "hour": "04:00", "name": "Teatro Popular Solano Trindade" },
				{ "hour": "10:00", "name": "Orquestra de Berimbaus" },
				{ "hour": "11:00", "name": "Os Favoritos da Catira" },
				{ "hour": "12:00", "name": "Fandango de Chilenas dos Irmãos Lara" },
				{ "hour": "13:00", "name": "Fandango de Tamanco de Cuitelo" },
				{ "hour": "14:00", "name": "Urucungos, Puítas e Quinjengues" }
			]
	},
	{
		"name": "Libero Badaró x Av. São João",
		"events":
			[
				{ "hour": "18:00", "name": "A Cobra vai Fumar-Teatro União e Olho Vivo" },
				{ "hour": "21:00", "name": "Este lado para cima-isto não é um espetáculo-Brava Companhia" },
				{ "hour": "10:00", "name": "A Exceção e a Regra-Cia Estável" },
				{ "hour": "13:00", "name": "A Casa da Farinha do Gonzagão - Cia Teatro da Investigação" },
				{ "hour": "15:00", "name": "Aqui não, Senhor Patrão! - Núcleo Pavanelli" }
			]
	},
	{
		"name": "Largo do Arouche",
		"events":
			[
				{ "hour": "19:00", "name": "Luê" },
				{ "hour": "21:00", "name": "Sara Jane" },
				{ "hour": "23:00", "name": "Sidney Magal" },
				{ "hour": "01:00", "name": "Kaoma" },
				{ "hour": "03:00", "name": "Luiz Caldas" },
				{ "hour": "05:00", "name": "Banda Uó" },
				{ "hour": "07:00", "name": "Movimento Roda de Curimbó - Chico Malta" },
				{ "hour": "09:00", "name": "Lia Sophia" },
				{ "hour": "11:00", "name": "Bloco Carnavalesco Ilê Aiyê (show e saída do cortejo)" },
				{ "hour": "13:00", "name": "Gerônimo" },
				{ "hour": "15:00", "name": "Felipe Cordeiro + Manoel" },
				{ "hour": "17:00", "name": "Fafá de Belém" }
			]
	},
	{
		"name": "Mercado Municipal de São Paulo",
		"events":
			[
				{ "hour": "18:00", "name": "Conjunto Retratos" },
				{ "hour": "20:00", "name": "Ana Bernardo & Choro Expresso" },
				{ "hour": "22:00", "name": "Regional do Véio" },
				{ "hour": "00:00", "name": "Barão e Sua Gente – As últimas do lado B" },
				{ "hour": "02:00", "name": "Choro, Seresta & Cia. – participação: Bruna Rodrigues" },
				{ "hour": "04:00", "name": "Roberto Seresteiro & Regional Imperial" },
				{ "hour": "06:00", "name": "Nosso Clube" },
				{ "hour": "08:00", "name": "Conjunto PBC – Praça Benedito Calixto" },
				{ "hour": "10:00", "name": "Choro do Alemão" },
				{ "hour": "12:00", "name": "Choro da Contemporânea" },
				{ "hour": "14:00", "name": "Trio que Chora" },
				{ "hour": "16:00", "name": "Izaías e Seus Chorões" }
			]
	},
	{
		"name": "Theatro Municipal",
		"events":
			[
				{ "hour": "18:00", "name": "Orquestra Sinfônica Municipal" },
				{ "hour": "21:00", "name": "Manera Fru-Fru Manera, 1973 - Fagner" },
				{ "hour": "00:00", "name": "O Filho de José e Maria, 1977 - Odair José" },
				{ "hour": "03:00", "name": "Angela Rô Rô, 1979 - Angela Rô Rô" },
				{ "hour": "06:00", "name": "Revolver, 1975 - Walter Franco" },
				{ "hour": "09:00", "name": "...Maravilhosa, 1972 - Wanderléa" },
				{ "hour": "12:00", "name": "Jorge Mautner, 1974 - Jorge Mautner" },
				{ "hour": "15:00", "name": "Deodato 2, 1973 - Eumir Deodato" },
				{ "hour": "18:00", "name": "Matança do Porco, 1973 - Som Imaginário (Auorização Trem Mineiro)" }
			]
	},
	{
		"name": "Pátio do Colégio | Palco Vanzolini",
		"events":
			[
				{ "hour": "18:00", "name": "Ligiana Costa e Leitieres Leite" },
				{ "hour": "20:00", "name": "Surdomundo Imposible Orchestra" },
				{ "hour": "22:00", "name": "Iconili" },
				{ "hour": "00:00", "name": "The Fringe (EUA)" },
				{ "hour": "02:00", "name": "Wordsong (Portugal)" },
				{ "hour": "04:00", "name": "Osso Vaidoso (Portugal)" },
				{ "hour": "06:00", "name": "Trio Corrente" },
				{ "hour": "08:00", "name": "Sizão Machado - homenagem a Moacir Santos" },
				{ "hour": "10:00", "name": "Grupo Dagadana (Polonia)" },
				{ "hour": "12:00", "name": "Pharoah Sanders & São Paulo Underground (EUA - Brasil)" },
				{ "hour": "14:00", "name": "O Samba do Rei do Baião - Socorro Lira, Oswaldinho da Cuíca e do Acordeon" },
				{ "hour": "16:00", "name": "Paulo Vanzollini - homenagem" },
				{ "hour": "19:00", "name": "Renato Braz + Proveta e Edson Alves" }
			]
	},
	{
		"name": "Rio Branco | A Rua é Show",
		"events":
			[
				{ "hour": "18:00", "name": "Dj Ninja e Mc Jack" },
				{ "hour": "18:40", "name": "Pepeu" },
				{ "hour": "19:10", "name": "1° Rodada de Batalha dos Beats." },
				{ "hour": "20:10", "name": "A Turma da São Bento" },
				{ "hour": "21:20", "name": "Nelson Triunfo e o Grupo Funk & Cia" },
				{ "hour": "22:30", "name": "Emicida" },
				{ "hour": "23:50", "name": "KL. Jay" },
				{ "hour": "01:00", "name": "Dj. Hum e Convidados" },
				{ "hour": "02:10", "name": "Edy Rock" },
				{ "hour": "03:40", "name": "kaion" },
				{ "hour": "05:00", "name": "Dj Gringo" },
				{ "hour": "06:20", "name": "Dom Billy e o grupo de Repente + Dj. Jack" },
				{ "hour": "07:20", "name": "Thiago Predador" },
				{ "hour": "08:00", "name": "Sombra" },
				{ "hour": "09:10", "name": "Prodígio" },
				{ "hour": "10:10", "name": "João Paraíba" },
				{ "hour": "11:10", "name": "2° Rodada de Batalha dos Beats" },
				{ "hour": "11:50", "name": "Banda Zuluz" },
				{ "hour": "13:20", "name": "Região Abissal" },
				{ "hour": "14:20", "name": "MC e DJ Rizada e convidados" },
				{ "hour": "15:00", "name": "Causa P." },
				{ "hour": "16:00", "name": "Oliveira de Panelas" },
				{ "hour": "17:00", "name": "Grupo Inquérito" },
				{ "hour": "18:00", "name": " Carlos Dafé e Banda" }
			]
	},
	{
		"name": "Pista Largo São Francisco | Noite Viva",
		"events":
			[
				{ "hour": "18:00", "name": "Paulo Boghosian" },
				{ "hour": "20:00", "name": "Anderson Noise" },
				{ "hour": "21:00", "name": "Eli Iwasa" },
				{ "hour": "22:00", "name": "Dre Guazzelli" },
				{ "hour": "23:00", "name": "Du Serena" },
				{ "hour": "00:00", "name": "Paula Chalup" },
				{ "hour": "01:00", "name": "Victor Ruiz" },
				{ "hour": "02:00", "name": "DJ Mau Mau" },
				{ "hour": "04:00", "name": "Renato Lopes" },
				{ "hour": "05:00", "name": "DJ Thomash" },
				{ "hour": "06:00", "name": "Boss in Drama" },
				{ "hour": "07:00", "name": "Database" },
				{ "hour": "08:00", "name": "Killer On The Dancefloor" },
				{ "hour": "09:00", "name": "E-cologyk" },
				{ "hour": "10:00", "name": "DISCOBABY" },
				{ "hour": "13:00", "name": "DJ Nuts" },
				{ "hour": "14:00", "name": "DJ Zegon" },
				{ "hour": "15:00", "name": "Michel Saad" },
				{ "hour": "16:00", "name": "Junior C" },
				{ "hour": "17:00", "name": "Renato Cohen" }
			]
	},
	{
		"name": "Pista Major Sertório",
		"events":
			[
				{ "hour": "18:00", "name": "Danilo Moraes" },
				{ "hour": "20:00", "name": "Layne Salles" },
				{ "hour": "22:00", "name": "Julião" },
				{ "hour": "00:00", "name": "Welldex" },
				{ "hour": "02:00", "name": "Mandraks" },
				{ "hour": "04:00", "name": "Bunnys" },
				{ "hour": "06:00", "name": "Kadosh" },
				{ "hour": "08:00", "name": "Basset" },
				{ "hour": "10:00", "name": "Yes America" },
				{ "hour": "12:00", "name": "Rocky Live (Israel)" },
				{ "hour": "14:00", "name": "Snoop" },
				{ "hour": "16:00", "name": "Dai Ferreira" }
			]
	},
	{
		"name": "Coreto Rua Antonio Prado - Bolsa",
		"events":
			[
				{ "hour": "18:00", "name": "Dona Inah e Cadeira de Balanço" },
				{ "hour": "19:30", "name": "Giana Viscardi e Quinteto" },
				{ "hour": "21:00", "name": "Juliana Amaral" },
				{ "hour": "22:30", "name": "Kolombolo e Toinho Melodia" },
				{ "hour": "00:00", "name": "Adriana Moreira" },
				{ "hour": "01:30", "name": "Ione Papas - sambas e ijexás" },
				{ "hour": "03:00", "name": "Railidia - Batuques e Cantorias" },
				{ "hour": "10:30", "name": "João Borba" },
				{ "hour": "12:00", "name": "Carmem Queiroz" },
				{ "hour": "13:30", "name": "Anaí Rosa e Cochichando" },
				{ "hour": "15:00", "name": "Bula na Cumbuca" },
				{ "hour": "16:30", "name": " Inimigos do Batente" }
			]
	},
	{
		"name": "Praça das Artes",
		"events":
			[
				{ "hour": "18:00", "name": "Cia Luis Ferron, Sapatos Brancos" },
				{ "hour": "19:30", "name": "EDSP, Variações sobre Tema de Paganini e Steps in The Streets" },
				{ "hour": "21:00", "name": "Balé da Cidade de São Paulo, T.A.T.O" },
				{ "hour": "22:00", "name": "Cia Focus de Dança (RJ), As Canções Que Você Dançou Pra Mim" },
				{ "hour": "23:15", "name": "Discipulos do Ritmo, Urbanóides" },
				{ "hour": "00:30", "name": "Projeto Mov_ola e Divinadança, Coup de Grace e Predicativo do Sujeito" },
				{ "hour": "01:30", "name": "Cia Sansacroma, Marchas" },
				{ "hour": "03:00", "name": "Chemical Funk, Orbit" },
				{ "hour": "04:00", "name": "D-Efeitos, D- Versos" },
				{ "hour": "05:30", "name": "Raça Cia de Dança, Caminho da Seda" },
				{ "hour": "07:30", "name": "Cia Independente de Dança de SP, Triunfal" },
				{ "hour": "08:30", "name": "Balé Capão Cidadão, Sonhos" },
				{ "hour": "09:30", "name": "Projeto Núcleo Luz, Rito de Passagem" },
				{ "hour": "11:00", "name": "Sopro Cia de Dança, Senha" },
				{ "hour": "12:30", "name": "TF Style Cia de Dança, Tempo" },
				{ "hour": "14:00", "name": "Cia de Danças de Diadema, Paranoia" },
				{ "hour": "16:00", "name": "São Paulo Cia de Dança, Pas de Deux Dom Quixote" },
				{ "hour": "16:30", "name": "Cia Primeiro Ato (MG), Quebra Cabeça" },
				{ "hour": "17:30", "name": "Cortejo Jam Diáspora e Abayomi (SC)" }
			]
	},
	{
		"name": "Largo São Bento",
		"events":
			[
				{ "hour": "18:40", "name": "Sarau Di Favela" },
				{ "hour": "20:20", "name": "DBS" },
				{ "hour": "22:00", "name": "Sarau da Cooperifa" },
				{ "hour": "23:40", "name": "Sarau da Cooperifa" },
				{ "hour": "00:20", "name": "Kamau" },
				{ "hour": "02:00", "name": "Sarau Suburbano convicto" },
				{ "hour": "05:40", "name": "ZÁfrica Brasil" },
				{ "hour": "06:20", "name": "Sarau dos Umbigos" },
				{ "hour": "08:00", "name": "Grupo MPA" },
				{ "hour": "09:40", "name": "Slam a Guilhermina" },
				{ "hour": "11:20", "name": "Crônica Mendes" },
				{ "hour": "13:00", "name": "Banda Nhocuné Soul" },
				{ "hour": "14:40", "name": "Zinho Trindade" },
				{ "hour": "16:20", "name": "Grupo Linha Dura" },
				{ "hour": "18:00", "name": "Sandrão RZO" }
			]
	},
	{
		"name": "Largo São Bento",
		"events":
			[
				{ "hour": "18:40", "name": "Sarau Di Favela" },
				{ "hour": "20:20", "name": "DBS" },
				{ "hour": "22:00", "name": "Sarau da Cooperifa" },
				{ "hour": "23:40", "name": "Sarau da Cooperifa" },
				{ "hour": "00:20", "name": "Kamau" },
				{ "hour": "02:00", "name": "Sarau Suburbano convicto" },
				{ "hour": "05:40", "name": "ZÁfrica Brasil" },
				{ "hour": "06:20", "name": "Sarau dos Umbigos" },
				{ "hour": "08:00", "name": "Grupo MPA" },
				{ "hour": "09:40", "name": "Slam a Guilhermina" },
				{ "hour": "11:20", "name": "Crônica Mendes" },
				{ "hour": "13:00", "name": "Banda Nhocuné Soul" },
				{ "hour": "14:40", "name": "Zinho Trindade" },
				{ "hour": "16:20", "name": "Grupo Linha Dura" },
				{ "hour": "18:00", "name": "Sandrão RZO" }
			]
	},
	{
		"name": "Cine Olido | Sessão Trash do Comodoro",
		"events":
			[
				{ "hour": "20:00", "name": "Santa Sangre - Dir.: Alejandro Jodorowsky (exibido em 2004)" },
				{ "hour": "22:00", "name": "Thriller, a Cruel Picture - Dir.: Alejandro Jodorowsky (exibido em 2004)" },
				{ "hour": "00:00", "name": "De Repente a Escuridão – Dir.: Robert Fuest (nunca exibido)" },
				{ "hour": "02:00", "name": "Emanuelle na América - Dir.: Joe D’Amato (nunca exibido)" },
				{ "hour": "04:00", "name": "A Noite do Terror Cego - Dir.: Amando de Ossorio (nunca exibido)" },
				{ "hour": "06:00", "name": "Visitor Q - Dir.: Takashi Miike (exibido em 2006)" },
				{ "hour": "08:00", "name": "Rock n’ Roll High School - Dir.: Alan Arkush (exibido em 2007)" },
				{ "hour": "10:00", "name": "Fascinação - Dir.: Jean Rollin (nunca exibido)" },
				{ "hour": "12:00", "name": "Confissões de um Comissário de Polícia - Dir.: Damiano Damiani (exibido em 2009)" },
				{ "hour": "14:00", "name": "Terror nas Trevas - Dir.: Lucio Fulci (nunca exibido)" },
				{ "hour": "16:00", "name": "Cartas de Amor de uma Freira Portuguesa - Dir.: Jesus Franco (nunca exibido)" },
				{ "hour": "18:00", "name": "Banho de Sangue - Dir.: Mario Bava (exibido em 2012)" }
			]
	}
]';

        $app = App::i();

        $data = json_decode($json);

        $agent = $this->repo('Agent')->find(1);

        $agent->name = 'Secretaria Municipal de Cultura';
        $agent->save();


        $virada = new MapasCulturais\Entities\Project;
        $virada->owner = $agent;
        $virada->name = 'Virada Cultural';
        $virada->type = 1;
        $virada->save(true);

        $virada2014 = new MapasCulturais\Entities\Project;
        $virada2014->owner = $agent;
        $virada2014->name = 'Virada Cultural 2014';
        $virada2014->type = 1;
        $virada2014->parent = $virada;
        $virada2014->save(true);


        foreach($data as $sdata){
            $space = new \MapasCulturais\Entities\Space;
            $space->owner = $agent;
            $space->name = $sdata->name;
            $space->type = 501;
            $space->terms['area'] = array('Virada Cultural');
            $space->save(true);
            $app->log->info('SPACE > ' . $space->name);

            foreach($sdata->events as $edata){

                $event = new \MapasCulturais\Entities\Event;
                $event->owner = $agent;
                $event->project = $virada2014;

                $event->name = $edata->name;
                $event->shortDescription = $edata->name . "\n\n" . $mussum_ipsum[rand(0,5)];
                $event->save(true);

                $app->log->info('EVENT > ' . $event->name);


                $startsOn = (intval($edata->hour) >= 18) ? '2014-05-17' : '2014-05-18';

                $eoccurrence = new \MapasCulturais\Entities\EventOccurrence();
                $eoccurrence->space = $space;
                $eoccurrence->event = $event;


                $rule = '{
                    "spaceId":"' . 1 . '",
                    "startsAt": "' . $edata->hour . '",
                    "endsAt": "' . $edata->hour . '",
                    "frequency": "daily",
                    "startsOn": "' . $startsOn . '",
                    "until": "' . $startsOn . '"
                }';
                $app->log->info(print_r($rule,true));
                $eoccurrence->rule = json_decode($rule);
                $eoccurrence->save(true);
            }
        }
        return true;
    },


    'create-occurrence_id_seq' => function (){
        $app = \MapasCulturais\App::i();
        $em = $app->em;
        $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
        $q = $em->createNativeQuery("
            CREATE SEQUENCE occurrence_id_seq
                START WITH 100000
                INCREMENT BY 1
                MINVALUE 100000
                NO MAXVALUE
                CACHE 1
                CYCLE;", $rsm);


        $q->execute();

        return true;
    },

    'remove agents and spaces with error - 2014-02-07' => function(){
        $spaces = $this->em->createQuery("SELECT e FROM MapasCulturais\Entities\Space e WHERE LOWER(TRIM(e.name)) LIKE 'erro%'")->getResult();
        $num_spaces = count($spaces);

        foreach ($spaces as $i => $s){
            $i++;
            $this->log->info("DB UPDATE > Removing space ({$i}/{$num_spaces}) \"{$s->name}\"");
            $s->delete();
        }

        $agents = $this->em->createQuery("SELECT e FROM MapasCulturais\Entities\Agent e WHERE LOWER(TRIM(e.name)) LIKE 'erro%'")->getResult();
        $num_agents = count($agents);

        foreach ($agents as $i => $a){
            $i++;
            $this->log->info("DB UPDATE > Removing agent ({$i}/{$num_agents}) \"{$a->name}\"");
            $a->destroy();
        }

        $users = $this->em->createQuery("SELECT e FROM MapasCulturais\Entities\User e WHERE SIZE(e.agents) = 0")->getResult();
        $num_users = count($users);
        $this->log->info("USUÁRIOS SEM AGENTES: $num_users");

        foreach ($users as $i => $u){
            $i++;
            $this->log->info("DB UPDATE > Removing user ({$i}/{$num_users}) \"{$u->id} ($u->email)\"");
            $u->delete();
        }

        return true;
    },

    '0001' => function(){
        $users = $this->repo('User')->findAll();
        foreach($users as $u){
            $profile = $u->getProfile();
            if(!$profile->isUserProfile){
                $this->log->info("DB UPDATE > Setting profile of the User \"{$u->id}\": Agent \"{$profile->name}\" ({$profile->id}).");
                $profile->setAsUserProfile();
            }
        }

        return true;
    }
);