<?php
$app = MapasCulturais\App::i();
//plugin Em Cartaz
// TODO: Mover arquivo de template de views/panel/em-cartaz.php para a pasta de plugins

$defaultFrom = new DateTime("first day of next month");
$defaultTo = new DateTime("last day of next month");

$app->hook('GET(panel.em-cartaz)', function() use ($app, $defaultFrom, $defaultTo) {
    $this->requireAuthentication();
    if(!$app->user->is('admin') && !$app->user->is('staff')){
        //throw new MapasCulturais\Exceptions\PermissionDenied;
        $app->pass();
    }
    $this->render('em-cartaz', array(
        'content'=>'',
        'from' => isset($this->getData['from']) ? new DateTime($this->getData['from']) : $defaultFrom,
        'to' => isset($this->getData['to']) ? new DateTime($this->getData['to']) : $defaultTo,
    ));
});

$app->hook('panel.menu:after', function() use ($app){
    if(!$app->user->is('admin') && !$app->user->is('staff'))
        return;

    $a_class = $this->template == 'panel/em-cartaz' ? 'active' : '';

    $url = $app->createUrl('panel', 'em-cartaz');
    echo "<li><a class='$a_class' href='$url'><span class='icon icon-em-cartaz'></span> Em Cartaz</a></li>";
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

    $eventTitleFont = $phpWord->addFontStyle('eventTitle',
        array('name'=>'Arial', 'size'=>12, 'color'=>'880000', 'bold'=>true));

    $linguagemStyle = $phpWord->addFontStyle('linguagemStyle',
        array('name'=>'Arial', 'size'=>12, 'color'=>'FF0000', 'bold'=>true));

    $linguagens = array(
        'Cinema', 'Dança', 'Teatro', 'Música Popular', 'Música Erudita', 'Exposição', 'Curso ou Oficina', 'Palestra, Debate ou Encontro'
    );

    $section->addText('ROTEIRO GERAL (SITE) REVISTA', $documentHead);

    $getEventTextBlock = function($event) use($app){
//        return "TEXT {$event->name}";

        $eventText = trim($event->shortDescription);
        if (!empty($event->classificacaoEtaria)) {
            $eventText .= $event->classificacaoEtaria;
        }
        $eventText .= '. ';

        // Group occurrences by space
        $spaces = array();
        foreach ($event->occurrences as $occurrence) {
            if (!array_key_exists($occurrence->space->id, $spaces)) {
                $spaces[$occurrence->space->id] = [
                    'space' => $occurrence->space,
                    'occurrences' => [],
                    'occurrences_texts' => [],
                ];
            }

            $occurenceDescription = '';
            if (!empty($occurrence->rule->description)) {
                $occurenceDescription .= trim($occurrence->rule->description) . '. ';
            } else {
                $occurenceDescription .= $occurrence->startsOn->format('d \d\e') . ' ' . \MapasCulturais\i::__($occurrence->startsOn->format('F')) . ' às ' . $occurrence->startsAt->format('H:i').'. ';
            }
            if (!empty($occurrence->rule->price)) {
                $occurenceDescription .= trim($occurrence->rule->price) . '. ';
            }

            $spaces[$occurrence->space->id]['occurrences_texts'][] = $occurenceDescription;
        }

        $spaceText = '';
        foreach ($spaces as $space) {
            $spaceText .= trim($space['space']->name) . ' ';

            if($this->action === 'em-cartaz-preview'){
                '<span> (<a href="' . $space['space']->singleUrl . '">link</a>)</span>. ';
            }

            $spaceText = str_replace('..', '.', $spaceText);
            foreach ($space['occurrences_texts'] as $occTxt) {
                $spaceText .= $occTxt;
            }
        }

        $agentText = '';
        foreach ($event->relatedAgents as $group => $relatedAgent) {
            $agentText .= trim($group) . ': ';
            foreach ($relatedAgent as $agent) {
                $agentText .= trim($agent->name) . ', ';
            }
        }
        return $eventText . $agentText . $spaceText;
    };

    $addEventBlockHtml = function($event) use ($section, $defaultFont, $eventTitleFont, $getEventTextBlock) {
        $textRunObj = $section->createTextRun();
        $textRunObj->addText($event->name . ' ', $eventTitleFont);
        $textRunObj->addText('(');
        $textRunObj->addLink($event->singleUrl, 'link', $eventTitleFont, $eventTitleFont);
        $textRunObj->addText(')');
        $textRunObj->addTextBreak();
        $textRunObj->addText($getEventTextBlock($event), $defaultFont);
    };

    $addEventBlockDoc = function($event) use ($section, $defaultFont, $eventTitleFont, $getEventTextBlock) {
        $section->addText('');
        $section->addText(htmlspecialchars($event->name), $eventTitleFont);
        $section->addText(htmlspecialchars($getEventTextBlock($event)), $defaultFont);
    };

    foreach($linguagens as $linguagem){

        $query = array(
            '@verified' => 'IN(1)',
            '@from'=>$from->format('Y-m-d'),
            '@to'=>$to->format('Y-m-d'),
            '@select' => 'id,name,shortDescription,singleUrl,classificacaoEtaria,location,metadata,occurrences,project,relatedAgents',
            '@order' => 'name ASC',
            'term:linguagem'=>'EQ('.$linguagem.')'
        );


        $events = $app->controller('event')->apiQueryByLocation($query);

        foreach($events as $i => $e){
            $events[$i] = (object) $e;
        }


        $section->addText('');
        $section->addText('');
        $section->addText(mb_strtoupper($linguagem, 'UTF-8').'*', $linguagemStyle);

        $projects = array();

        foreach($events as $i => $event){
            if($event->project){
                if(!isset($projects[$event->project->id])){
                    $projects[$event->project->id] = array(
                        'project' => $event->project,
                        'events' => array()
                    );
                }
                $projects[$event->project->id]['events'][] = $event;
            }else{
                if($this->action === 'em-cartaz-preview'){
                    $addEventBlockHtml($event);
                }else{
                    $addEventBlockDoc($event);
                }
            }

        }

        foreach($projects as $project){

            $textRunObj = $section->createTextRun();

            if($this->action === 'em-cartaz-preview'){
                $textRunObj->addText('PROJETO ' . $project['project']->name . ' ', $eventTitleFont);
                $textRunObj->addText('(');
                $textRunObj->addLink($project['project']->singleUrl, 'link', $eventTitleFont, $eventTitleFont);
                $textRunObj->addText(')');
            }else{
                $section->addText('PROJETO '.$project['project']->name, $eventTitleFont);
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

        $this->render('em-cartaz', array(
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
