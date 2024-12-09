if ($MAPAS.subsite) {
    const subsite = new Entity('subsite', $MAPAS.subsite.id);
    subsite.populate($MAPAS.subsite);

    $MAPAS.subsite = subsite;
}