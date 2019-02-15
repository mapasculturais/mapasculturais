<?php
namespace MapasCulturais\Definitions;

/**
 * This class defines a Taxonomy.
 *
 * You need to register the taxonomy definition in the application to it take effects.
 *
 * If you want to restrict the terms that the user can use, pass an array of terms to the $restrictedTerms constructor param.
 *
 * @property-read int $id Taxonomy Id
 * @property-read string $slug Taxonomy Slug
 * @property-read bool $allowInsert Can user insert terms in this taxonomy?
 * @property-read bool $required Is this taxonomy required?
 * @property-read array $restrictedTerms List of terms allowed to this taxonomy. If this list is empty any term is allowed.
 * @property-read string $description Taxonomy Description
 */
class Taxonomy extends \MapasCulturais\Definition{

    /**
     * The taxonomy Id saved in Term Entity.
     * @var int
     */
    protected $id;

    /**
     * The toxonomy slug (like "tag")
     * @var string
     */
    protected $slug;

    /**
     * If allowInsert is setted to true, the users will be allowed to creates new terms for this taxonomy.
     * @var bool
     */
    protected $allowInsert;

    /**
     * The description of this taxonomy.
     * @var string
     */
    protected $description;

    /**
     * List of terms allowed to this taxonomy. If this list is empty any term is allowed.
     * @var array
     */
    protected $restrictedTerms = [];


    protected $required = false;

    /**
     * Creates the new Taxonomy Definition.
     *
     * If you just creates a new Taxonomy Definition it will do nothing.
     *
     * To the new taxonomy take effects, you need to register it in the application.
     *
     * If you want to restrict the terms that the user can use, pass an array of terms to the $restrictedTerms param.
     *
     * @param int $id The taxonomy Id saved in Term Entity.
     * @param string $slug The taxonomy slug (like "tag")
     * @param string $description The description of this taxonomy.
     * @param array|boolean $restrictedTerms array with the terms allowed to this taxonomy or false to allow terms to be inserted by the user.
     *
     * @see \MapasCulturais\App::registerTaxonomy()
     * @see \MapasCulturais\App::getRegisteredTaxonomyById()
     * @see \MapasCulturais\App::getRegisteredTaxonomyBySlug()
     * @see \MapasCulturais\App::getRegisteredTaxonomies()
     * @see \MapasCulturais\App::getRegisteredTaxonomy()
     *
     */
    public function __construct($id, $slug, $description, $restrictedTerms = false, $taxonomy_required = false) {
        $this->id = $id;
        $this->slug = $slug;
        $this->allowInsert = empty($restrictedTerms);
        $this->required = $taxonomy_required;
        if(is_array($restrictedTerms))
            foreach($restrictedTerms as $term)
                $this->restrictedTerms[strtolower (trim($term))] = trim($term);
        $this->description = $description;
    }
}
