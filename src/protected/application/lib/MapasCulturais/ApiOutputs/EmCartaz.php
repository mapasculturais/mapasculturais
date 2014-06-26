<?php
namespace MapasCulturais\ApiOutputs;
use \MapasCulturais\App;
use \PhpOffice\PhpWord\PhpWord;

class EmCartaz extends \MapasCulturais\ApiOutput{
    protected function getContentType() {
        return 'text/html';
    }

    protected function _outputArray(array $data, $singular_object_name = 'Entity', $plural_object_name = 'Entities') {
        $first = true;

        $phpWord = new \PhpOffice\PhpWord\PhpWord();

        // Every element you want to append to the word document is placed in a section.
        // To create a basic section:
        $section = $phpWord->addSection();

        // After creating a section, you can append elements:
        $section->addText('Teste de saída da API usando PhpOffice\PhpWord'."\r\n");

        // You can directly style your text by giving the addText function an array:
        $section->addText('Texto estilizado com tahoma 16 bold.',
            array('name'=>'Tahoma', 'size'=>16, 'bold'=>true));

        // If you often need the same style again you can create a user defined style
        // to the word document and give the addText function the name of the style:
        $phpWord->addFontStyle('myOwnStyle',
            array('name'=>'Verdana', 'size'=>14, 'color'=>'FF0000'));
        $section->addText('Outro texto formatado',
            'myOwnStyle');

        // You can also put the appended element to local object like this:
        $fontStyle = new \PhpOffice\PhpWord\Style\Font();
        $fontStyle->setBold(true);
        $fontStyle->setName('Verdana');
        $fontStyle->setSize(22);


        $section->addText('');

        $logLine = $section->addText(sprintf(App::txts("%s $singular_object_name found.", "%s $plural_object_name found.", count($data)), count($data)));
        $logLine->setFontStyle($fontStyle);

        foreach($data as $item){
            $line = '';
            foreach($item as $k => $v){
                $line .=  $v.' ';
            }
            $section->addText($line);
        }

        header('Content-Type: application/vnd.ms-word');
        header('Content-Disposition: attachment;filename="apiEmCartazOutput.docx"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save("php://output");

        /* PARA OUTROS FORMATOS E SALVAR, NÃO ENVIAR PARA O BROWSER:
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('../apiEmCartazOutput.docx');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'ODText');
        $objWriter->save('../apiEmCartazOutput.odt');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'RTF');
        $objWriter->save('../apiEmCartazOutput.rtf');
        */
    }

    function _outputItem($data, $object_name = 'entity') {
        var_dump($data);
    }

    protected function _outputError($data) {
        var_dump('ERROR', $data);
    }
}
