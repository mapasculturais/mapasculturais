<?php

namespace Tests\Builders\Traits;

use MapasCulturais\App;
use MapasCulturais\Definitions\Taxonomy;
use MapasCulturais\Entities;
use Tests\Traits\Faker;

/** @property Entities\Agent|Entities\Space|Entities\Project|Entities\Event|Entities\Opportunity $instance */
trait Taxonomies
{
    use Faker;

    private function _getTaxonomyDefinition(string $taxonomy_slug): Taxonomy {
        $app = App::i();    
        $definition = $app->getRegisteredTaxonomy($this->instance, $taxonomy_slug);
        if(!$definition) {
            throw new \Exception(sprintf("A entidade %s não usa a taxonomia %s", $this->instance->className, $taxonomy_slug));
        }

        return $definition;
    }

    function addRandomTerms(string $taxonomy_slug, $number_of_terms = 3): self
    {
        $definition = $this->_getTaxonomyDefinition($taxonomy_slug);

        if($definition->restrictedTerms) {
            $terms = (array) array_rand($definition->restrictedTerms, $number_of_terms);
        } else {
            $terms = [];
            for($i=0; $i<$number_of_terms; $i++) {
                $this->faker->words(rand(1,3));
            }
        }

        return $this->addTerms($taxonomy_slug, $terms);
    }

    function addTerms(string $taxonomy_slug, array $terms): self {
        $this->_getTaxonomyDefinition($taxonomy_slug);

        foreach($terms as $term) {
            $this->instance->terms[$taxonomy_slug][] = $term;
        }

        return $this;
    }
}
