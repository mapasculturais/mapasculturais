<?php

namespace FAQ;
use MapasCulturais\App;
use MapasCulturais\Themes\BaseV2\Theme;
use stdClass;

class Module extends \MapasCulturais\Module
{

    function __construct($config = [])
    {
        $config += [];

        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        $self  = $this;
    }

    public function register()
    {
        $app = App::i();
        $app->registerController('faq', Controller::class);
        $app->controller('faq')->module = $this;
    }

    function getFAQ($section_slug = null): array {
        $language_code = 'pt_BR';

        $faq = [];

        $sections = glob(__DIR__ . "/questions/{$language_code}/*/");
        sort($sections);

        foreach($sections as $section_path) {
            $raw_section = file_get_contents("{$section_path}README.md");
            $section = $this->parseSection($raw_section, $section_path);

            $contexts = glob("{$section_path}*/");
            sort($contexts);

            foreach($contexts as $context_path) {
                $raw_context = file_get_contents("{$context_path}README.md");
                $context = $this->parseContext($raw_context, $context_path);

                if($section_slug && $section->slug != $section_slug) {
                    continue;
                }

                $questions = glob("{$context_path}*.md");
                sort($questions);

                foreach($questions as $question_path) {
                    if($question_path == "{$context_path}README.md") {
                        continue;
                    }

                    $raw_question = file_get_contents($question_path);
                    $question = $this->parseQuestion($raw_question, $question_path);

                    $context->questions[] = $question;
                }

                $section->contexts[] = $context;
            }

            $faq[] = $section;
        }

        return $faq;
    }

    function parseSection($raw_section, $section_path):object {
        $app = App::i();
        
        $raw_section = trim($raw_section);
        $slug = preg_replace('#^\d+\.#', '', basename($section_path));
        $title = $this->getMarkdownTitle($raw_section);
        $section = [
            'slug' => $slug,
            'title' => $title,
            'description' => $app->view->renderMarkdown($raw_section),
            'contexts' => []
        ];

        return (object) $section;
    }

    function parseContext($raw_context, $context_path):object {
        $app = App::i();
        
        $raw_context = trim($raw_context);
        $slug = preg_replace('#^\d+\.#', '', basename($context_path));
        $title = $this->getMarkdownTitle($raw_context);
        $context = [
            'slug' => $slug,
            'title' => $title,
            'description' => $app->view->renderMarkdown($raw_context),
            'questions' => []
        ];

        return (object) $context;
    }

    function parseQuestion($raw_question, $question_path):object {
        $app = App::i();
        
        $raw_question = trim($raw_question);
        $slug = preg_replace('#^\d+\.#', '', basename($question_path));
        $slug = substr($slug, 0, -3);
        $title = $this->getMarkdownTitle($raw_question);
        $tags = $this->getMarkdownTags($raw_question);
        return (object) [
            'slug' => $slug,
            'tags' => $tags,
            'question' => $title,
            'answer' => $app->view->renderMarkdown($raw_question)
        ];
    }

    /**
     * Retorna o título do markdown informado e remove a linha do título do markdown informado
     * @param string $markdown 
     * @return string
     */
    function getMarkdownTitle (string &$markdown): string {
        $lines = explode("\n", $markdown);
        $title = array_shift($lines);
        $markdown = trim(implode("\n", $lines));

        return preg_replace('/^# */', '', $title);
    }

    function getMarkdownTags(string &$markdown): array {
        $lines = explode("\n", $markdown);
        $tags = [];
        while($lines[0] && preg_match('/^ *- +(.*)/', $lines[0], $matches)) {
            $tag = $matches[1];
            $tags[] = $tag;
            array_shift($lines);
        }

        $markdown = trim(implode("\n", $lines));

        return $tags;
    }
}
