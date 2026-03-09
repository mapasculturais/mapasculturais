<?php
namespace MapasCulturais\Definitions;

/**
 * Define uma Taxonomia.
 *
 * Você precisa registrar a definição da taxonomia na aplicação para que ela tenha efeito.
 *
 * Se você quiser restringir os termos que o usuário pode usar, passe um array de termos para o parâmetro $restrictedTerms do construtor.
 *
 * @property-read int $id ID da Taxonomia
 * @property-read string $slug Slug da Taxonomia
 * @property-read string $name Nome da Taxonomia
 * @property-read bool $allowInsert O usuário pode inserir termos nesta taxonomia?
 * @property-read bool $required Esta taxonomia é obrigatória?
 * @property-read array $restrictedTerms Lista de termos permitidos para esta taxonomia. Se esta lista estiver vazia, qualquer termo é permitido.
 * @property-read string $description Descrição da Taxonomia
 * 
 * @package MapasCulturais\Definitions
 */
class Taxonomy extends \MapasCulturais\Definition{

    /**
     * ID da taxonomia salvo na Entidade Term.
     * @var int
     */
    public $id;

    /**
     * Slug da taxonomia (ex: "tag")
     * @var string
     */
    public $slug;

    /**
     * Nome da taxonomia (ex: "Área de Atuação")
     * @var string
     */
    public $name;

    /**
     * Se allowInsert for true, os usuários poderão criar novos termos para esta taxonomia.
     * @var bool
     */
    public $allowInsert;

    /**
     * Descrição desta taxonomia.
     * @var string
     */
    public $description;

    /**
     * Lista de termos permitidos para esta taxonomia. Se esta lista estiver vazia, qualquer termo é permitido.
     * @var array
     */
    public $restrictedTerms = [];


    /**
     * Lista de entidades permitidas para esta taxonomia. Se a lista estiver vazia, qualquer entidade é permitida.
     * @var array
     */
    public $entities = [];


    /**
     * Indica se a taxonomia é obrigatória
     * @var bool
     */
    public $required = false;

    /**
     * Cria uma nova Definição de Taxonomia.
     *
     * Se você apenas criar uma nova Definição de Taxonomia, ela não fará nada.
     *
     * Para que a nova taxonomia tenha efeito, você precisa registrá-la na aplicação.
     *
     * Se você quiser restringir os termos que o usuário pode usar, passe um array de termos para o parâmetro $restrictedTerms.
     *
     * @param int $id ID da taxonomia salvo na Entidade Term.
     * @param string $slug Slug da taxonomia (ex: "tag")
     * @param string $description Descrição desta taxonomia.
     * @param array|boolean $restrictedTerms array com os termos permitidos para esta taxonomia ou false para permitir que termos sejam inseridos pelo usuário.
     * @param bool $taxonomy_required Indica se a taxonomia é obrigatória
     * @param array $entities Lista de entidades permitidas para esta taxonomia
     *
     * @see \MapasCulturais\App::registerTaxonomy()
     * @see \MapasCulturais\App::getRegisteredTaxonomyById()
     * @see \MapasCulturais\App::getRegisteredTaxonomyBySlug()
     * @see \MapasCulturais\App::getRegisteredTaxonomies()
     * @see \MapasCulturais\App::getRegisteredTaxonomy()
     *
     */
    public function __construct($id, $slug, $description, $restrictedTerms = false, $taxonomy_required = false, $entities =  []) {
        $this->id = $id;
        $this->slug = $slug;
        $this->allowInsert = empty($restrictedTerms);
        $this->required = $taxonomy_required;
        $this->entities = $entities;
        if(is_array($restrictedTerms))
            foreach($restrictedTerms as $term)
                $this->restrictedTerms[mb_strtolower (trim($term))] = trim($term);
        $this->description = $description;
    }
}
