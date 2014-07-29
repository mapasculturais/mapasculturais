<?php

//plugin Em Cartaz
// TODO: Mover arquivo de template de views/panel/part-em-cartaz.php para a pasta de plugins

$defaultFrom = new DateTime("first day of next month");
$defaultTo = new DateTime("last day of next month");

$app->hook('GET(panel.em-cartaz)', function() use ($app, $defaultFrom, $defaultTo) {
    $this->requireAuthentication();
    if(!$app->user->is('admin') && !$app->user->is('staff')){
        //throw new MapasCulturais\Exceptions\PermissionDenied;
        $app->pass();
    }
    $this->render('part-em-cartaz', array(
        'content'=>'',
        'from' => isset($this->getData['from']) ? new DateTime($this->getData['from']) : $defaultFrom,
        'to' => isset($this->getData['to']) ? new DateTime($this->getData['to']) : $defaultTo,
    ));
});

$app->hook('view.partial(panel/part-nav):after', function($template, &$html) use ($app){
    if(!$app->user->is('admin') && !$app->user->is('staff'))
        return;

    $a_class = $this->template == 'panel/em-cartaz' ? 'active' : '';
    $url = $app->createUrl('panel', 'em-cartaz');
    $menu = "<li><a class='$a_class' href='$url'><span class='icone icon_star'></span> Em Cartaz</a></li>";
    $html = str_replace('</ul>', $menu . '</ul>', $html);
});


$app->hook('GET(panel.em-cartaz-<<download|preview>>)', function() use ($app, $defaultFrom, $defaultTo) {
    if(!$app->user->is('admin') && !$app->user->is('staff')){
        //throw new MapasCulturais\Exceptions\PermissionDenied;
        $app->pass();
    }
    $from = isset($this->getData['from']) ? new DateTime($this->getData['from']) : $defaultFrom;
    $to = isset($this->getData['to']) ? new DateTime($this->getData['to']) : $defaultTo;


    $phpWord = new \PhpOffice\PhpWord\PhpWord();

    // Every element you want to append to the word document is placed in a section.
    // To create a basic section:
    $section = $phpWord->addSection();

    $defaultFont = $phpWord->addFontStyle('defaultFont',
        array('name'=>'Arial', 'size'=>12));

    $documentHead = $phpWord->addFontStyle('documentHead',
        array('name'=>'Arial', 'size'=>18, 'color'=>'44AA88', 'bold'=>true));

    $eventTitle = $phpWord->addFontStyle('eventTitle',
        array('name'=>'Arial', 'size'=>12, 'color'=>'880000', 'bold'=>true));

    $linguagemStyle = $phpWord->addFontStyle('linguagemStyle',
        array('name'=>'Arial', 'size'=>12, 'color'=>'FF0000', 'bold'=>true));

    $linguagens = array(
        'Cinema', 'Dança', 'Teatro', 'Música Popular', 'Música Erudita', 'Exposição', 'Curso ou Oficina', 'Palestra, Debate ou Encontro'
    );

    $section->addText('ROTEIRO GERAL (SITE) REVISTA', $documentHead);

    $addEventBlockHtml = function($event) use ($app, $section, $defaultFont, $eventTitle){
        $textRunObj = $section->createTextRun();
        $textRunObj->addLink($event['singleUrl'], $event['name'], $eventTitle, $eventTitle);
        $textRunObj->addTextBreak();
        $eventText = $event['shortDescription'];
        if(!empty($event['classificacaoEtaria'])){
            $eventText .= '+'.$event['classificacaoEtaria'].'. ';
        }

        $spaces = array();
        $occurenceDescription = '';
        foreach($event['occurrences'] as $occurrence){
            if(!empty($occurrence->rule->description)){
                $occurenceDescription .= $occurrence->rule->description.'. ';
            }else{
                $occurenceDescription .= $occurrence->startsOn->format('d \d\e') . ' ' . $app->txt($occurrence->startsOn->format('F')) . ' às ' . $occurrence->startsAt->format('H:i').'. ';
            }
            if(!empty($occurrence->rule->price)){
                $occurenceDescription .= $occurrence->rule->price.'. ';
            }
            if (!array_key_exists($occurrence->space->id, $spaces)){
                $spaces[$occurrence->space->id] = $occurrence->space;
            }
        }
        $spaceText = '';
        foreach($spaces as $space){
            $spaceText .= $space->name . ', '. $space->endereco.'. ';
        }
        $agentText = '';
        foreach($event['relatedAgents'] as $group=>$relatedAgent){
            $agentText .= $group.': ';
            foreach($relatedAgent as $agent){
                $agentText .= $agent->name.', ';
            }
        }

        $textRunObj->addText($eventText.' '.$agentText.' '.$spaceText.$occurenceDescription, $defaultFont);
    };

    $addEventBlockDoc = function($event) use ($section, $defaultFont, $eventTitle){
        $section->addText('');
        $section->addText($event['name'], $eventTitle);
        //$section->addText($event['shortDescription'], $defaultFont);
        $spaces = array();
        $occurenceDescription = '';
        foreach($event['occurrences'] as $occurrence){
            if(isset($occurrence->rule->description)){
                $occurenceDescription .= trim($occurrence->rule->description).'. ';
            }
            if(isset($occurrence->rule->price)){
                $occurenceDescription .= trim($occurrence->rule->price).'. ';
            }
            if (!array_key_exists($occurrence->space->id, $spaces)){
                $spaces[$occurrence->space->id] = $occurrence->space;
            }
        }
        $spaceText = '';
        foreach($spaces as $space){
            $spaceText .= trim($space->name) . ', '. trim($space->endereco).'. ';
        }
        $agentText = '';
        foreach($event['relatedAgents'] as $group=>$relatedAgent){
            $agentText .= trim($group).': ';
            foreach($relatedAgent as $agent){
                $agentText .= ($agent->name).', ';
            }
        }

        $section->addText(trim($event['shortDescription']).'. '.trim($agentText).' '.trim($spaceText).' '.trim($occurenceDescription), $defaultFont);
    };


    foreach($linguagens as $linguagem){

        $query = array(
            'isVerified' => 'eq(true)',
            '@from'=>$from->format('Y-m-d'),
            '@to'=>$to->format('Y-m-d'),
            '@select' => 'id,name,shortDescription,singleUrl,classificacaoEtaria,location,metadata,occurrences,project,relatedAgents',
            '@order' => 'name ASC',
            'term:linguagem'=>'EQ('.$linguagem.')'
        );

        $events = $app->controller('event')->apiQueryByLocation($query);

        $section->addText('');
        $section->addText('');
        $section->addText(mb_strtoupper($linguagem, 'UTF-8').'*', $linguagemStyle);

        $projects = array();

        foreach($events as $event){
            if($event['project']){
                if(!isset($projects[$event['project']->id])){
                    $projects[$event['project']->id] = array(
                        'project' => $event['project'],
                        'events' => array()
                    );
                }
                $projects[$event['project']->id]['events'][] = $event;
                continue;
            }

            if($this->action === 'em-cartaz-preview'){
                $addEventBlockHtml($event);
            }else{
                $addEventBlockDoc($event);
            }
        }

        foreach($projects as $project){
            $textRunObj = $section->createTextRun();

            if($this->action === 'em-cartaz-preview'){
                $textRunObj->addText('PROJETO '.$project['project']->name, $eventTitle);
            }else{
                $section->addText('PROJETO '.$project['project']->name, $eventTitle);
            }
            foreach($project['events'] as $event){
                if($this->action === 'em-cartaz-preview'){

                    $addEventBlockHtml($event);
                }else{
                    $addEventBlockDoc($event);
                }
            }
        }
    }

    if($this->action === 'em-cartaz-preview'){
        //$content = '<a href="'.$app->createUrl('panel', 'em-cartaz-download').'">Salvar Documento Em Formato Microsoft Word</a>';

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'HTML');

        $this->render('part-em-cartaz', array(
            'content'=>$objWriter->getWriterPart('Body')->write(),
            'from' => $from,
            'to' => $to
        ));

    }else{
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save("php://output");

        $app->response()->header('Content-Type', 'application/vnd.ms-word');
        $app->response()->header('Content-Disposition', 'attachment;filename="Em Cartaz de '.$from->format('d-m-Y').' a '.$to->format('d-m-Y').'.docx"');
        $app->response()->header('Cache-Control', 'max-age=0');
    }

});