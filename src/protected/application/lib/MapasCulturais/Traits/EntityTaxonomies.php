<?php
namespace MapasCulturais\Traits;
use MapasCulturais\App;

/**
 * Defines that the entity has taxonomies.
 * 
 * Use this trait only in subclasses of **\MapasCulturais\Entity**.
 *
 * Use the property $terms to set terms to the entity.
 *
 * <code>
 *  // Example of the $terms property
 *  array(
 *      'tag' => ['Music', 'Guitar'],
 *      'category' => ['Jazz', 'Rock']
 *  )
 * </code>
 *
 * @example To remove all tags of the entity set $entity->terms['tag'] = [] and save the entity or $entity->saveTerms().
 * @example To set tags 'music', 'photo' and 'video' set $entity->terms['tag'] = ['music', 'photo', 'video'] and save the entity or $entity->saveTerms()
 * @example To add the tag 'music' just do $entity->terms['tag'][] = 'music' and save the entity or $entity->saveTerms()
 *
 * @property \MapasCulturais\Entities\Term[] $taxonomyTerms Description.
 * @property array $terms array of terms string grouped by taxonomy slug. ex: array('tag' => ['Music', 'Dance'])
 * 
 * @property-read string $termRelationClassName the name of the term relation class for this entity
 */
trait EntityTaxonomies{
    /**
     * This property is used to set terms to the entity.
     *
     *
     * <code>
     *  // Example of the $terms property
     *  array(
     *      'tag' => ['Music', 'Guitar'],
     *      'category' => ['Jazz', 'Rock']
     *  )
     * </code>
     *
     * @example To remove all tags of the entity set $entity->terms['tag'] = [] and save the entity or $entity->saveTerms().
     * @example To set tags 'music', 'photo' and 'video' set $entity->terms['tag'] = ['music', 'photo', 'video'] and save the entity or $entity->saveTerms()
     * @example To add the tag 'music' just do $entity->terms['tag'][] = 'music' and save the entity or $entity->saveTerms()
     *
     * @var array the taxonomy terms
     */
    protected $terms = null;

    /**
     * This entity has taxonomies
     *
     * @return true
     */
    public static function usesTaxonomies(){
        return true;
    }
    
    
    /**
     * Returns the name of the term relation class for this entity
     * 
     * @return string
     */
    static function getTermRelationClassName(){
        $class = get_called_class();
        return $class::getClassName() . 'TermRelation';
    }

    /**
     * Returns the terms of this entity grouped by taxonomy slugs
     *
     * <code>
     *  // Example of returned array
     *  array(
     *      'tag' => ['Music', 'Guitar'],
     *      'category' => ['Jazz', 'Rock']
     *  )
     * </code>
     *
     * @return array
     */
    function getTerms(){
        if(is_null($this->terms)){
            $this->populateTermsProperty();
        }
        return $this->terms;
    }

    /**
     * Populates the terms property with values associated with this entity
     */
    protected function populateTermsProperty(){
        if(is_null($this->terms))
            $this->terms = new \ArrayObject();

        foreach ($this->taxonomyTerms as $taxonomy_slug => $terms){
            $this->terms[$taxonomy_slug] = [];
            foreach($terms as $term)
                $this->terms[$taxonomy_slug][] = $term->term;

        }
    }

    function getTaxonomiesValidationErrors(){
        $taxonomies = App::i()->getRegisteredTaxonomies($this);
        $errors = [];
        foreach($taxonomies as $definition){
            if($definition->required && empty($this->terms[$definition->slug])){
                $errors['term-'.$definition->slug] = [$definition->required];
            }
        }
        return $errors;
    }

    /**
     * Saves the terms in the way they are on the property terms.
     *
     * This method creates or removes the associations with te terms as needed.
     *
     */
    function saveTerms(){
        if(!$this->terms)
            return false;

        $app = App::i();

        // temporary array
        $taxonomy = $this->terms;

        foreach($this->taxonomyTerms as $slug => $terms){
            foreach($terms as $term){
                // if the term is in the terms property and the association already exists,
                if(isset($taxonomy[$slug]) && in_array($term->term, $taxonomy[$slug])){
                    $i = array_search($term->term, $taxonomy[$slug]);
                    // removes the term of the temporary array because is not necessary to add it
                    unset($taxonomy[$slug][$i]);

                // if a term with an existent relation is not in the terms property, removes the relation.
                }else{
                    $tr = $app->repo($this->getTermRelationClassName())->findOneBy(['term' => $term, 'owner' => $this]);
                    if($tr)
                        $tr->delete(true);
                }
            }
        }

        // now creates relations to the terms in the temporary array
        foreach($taxonomy as $slug => $terms){
            foreach($terms as $term){
                $this->addTerm($slug, $term);
            }
        }
    }

    /**
     * Adds a term to the entity. If the term does not exists and the definition of the taxonomy allow insertion, first creates it.
     *
     * @param string $taxonomy_slug the taxonomy slug (like tag)
     * @param string $term the term to add (like music)
     * @param string $description (optional) the description of the term. Used only on insertion of new term.
     *
     * @return bool true if the term was added to the entity, false if not.
     */
    protected function addTerm($taxonomy_slug, $term, $description = ''){
        $app = App::i();

        $term = trim($term);

        $term_relation_class = $this->getTermRelationClassName();
        
        // if this entity uses this taxonomy
        if($definition = $app->getRegisteredTaxonomy($this, $taxonomy_slug)){
            $t = $app->repo('Term')->findOneBy(['taxonomy' => $definition->id, 'term' => $term]);
            $tr = $app->repo($this->getTermRelationClassName())->findOneBy(['term' => $t, 'owner' => $this]);

            // if the term is already associated to this entity return
            if($tr){
                return true;

            // else if the term exists, create de association
            }elseif($t){
                $tr = new $term_relation_class;
                $tr->term = $t;
                $tr->owner = $this;

                $tr->save();
                return true;

            // else if the term does not exists but the taxonomy definition allow insertion, create de term and the association
            }elseif($definition->allowInsert || key_exists(strtolower(trim($term)), $definition->restrictedTerms) ){

                // if not allowed to insert terms, get the term in the way as defined in restrictedTerms
                if(!$definition->allowInsert)
                    $term = $definition->restrictedTerms[strtolower(trim($term))];

                $t = new \MapasCulturais\Entities\Term;
                $t->term = $term;
                $t->taxonomy = $definition->id;
                $t->description = $description;

                $t->save();

                $tr = new $term_relation_class;
                $tr->term = $t;
                $tr->owner = $this;

                $tr->save();
                return true;

            // else if the term not exists and the taxonomy definition not allow insertion, return false
            }else{
                return false;
            }
        // if this entity not uses this taxonomy
        }else{
            return false;
        }
    }


    /**
     * Return the term entities associated to this entity.
     *
     * @return \MapasCulturais\Entities\Term[] array of terms
     */
    function getTaxonomyTerms($taxonomy_slug = null){
        $app = App::i();
        $result = [];

        $taxonomies = $app->getRegisteredTaxonomies($this);
        foreach($taxonomies as $tax){
            $result[$tax->slug] = [];
        }
        
        if(!$this->id){
            return $result;
        }
        
        foreach($this->__termRelations as $tr){
            $term = $tr->term;
            if($term->taxonomySlug && isset($result[$term->taxonomySlug])){
                $result[$term->taxonomySlug][] = $term;
            }
        }
        
        foreach($result as $k => $r){
            sort($r);
            $result[$k] = $r;
        }
        
        if($taxonomy_slug){
            return key_exists($taxonomy_slug, $result) ? $result[$taxonomy_slug] : [];
        }else{
            return $result;
        }
    }
}