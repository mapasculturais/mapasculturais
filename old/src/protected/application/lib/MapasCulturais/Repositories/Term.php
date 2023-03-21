<?php
namespace MapasCulturais\Repositories;

class Term extends \MapasCulturais\Repository{

    /**
     * Returns an array with all terms of the given taxonomy slug as strings.
     *
     * If the taxonomy has restricted terms, this method returns the array of the restricted terms defined in the Taxonomy Definition.
     *
     * @see \MapasCulturais\Definitions\Taxonomy
     *
     * @return string[] array of terms
     */
    public function getTermsAsString($taxonomy_slug){
        $taxonomy_definition = \MapasCulturais\App::i()->getRegisteredTaxonomyBySlug($taxonomy_slug);
        if(!$taxonomy_definition){
            throw new \Exception("Invalid taxonomy slug \"$taxonomy_slug\".");
            return;
        }
        if($taxonomy_definition->restrictedTerms){
            $terms = $taxonomy_definition->restrictedTerms;
            sort($terms);
        }else{
            $q = $this->_em->createQuery('SELECT t.term FROM \MapasCulturais\Entities\Term t WHERE t.taxonomy = :taxonomy_id ORDER BY t.term');
            $q->setParameter('taxonomy_id', $taxonomy_definition->slug);
            $_terms = $q->getScalarResult();
            $terms = [];
            foreach($_terms as $term)
                $terms[] = $term['term'];

        }
        return $terms;
    }
}