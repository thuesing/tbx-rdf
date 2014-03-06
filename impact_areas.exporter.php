<?php
/**
 * @depends http://www.easyrdf.org/
 * @see examples/graph_direct.php example
 * validate here: 
 * http://www.w3.org/RDF/Validator/
 * http://www.rdfabout.com/demo/validator
 *
 * drush php-script impact_areas.exporter.php -r ~/www/tbx.dev/ > tbx:impact_areas.$(date +"%Y-%m-%d-%T").rdf
 *
 */

#set_include_path(get_include_path() . PATH_SEPARATOR . 'easyrdf/lib/');
require_once "easyrdf/lib/EasyRdf.php";

$taxos = array('economic_impacts','environmental_impacts','social_impacts');

// serialize to
// other formats: ntriples, rdfxml, turtle
$format = 'rdfxml';

# set namespace
#EasyRdf_Namespace::set('tbx', 'http://beta.liaise-toolbox.eu/');
EasyRdf_Namespace::set('ia', 'http://beta.liaise-toolbox.eu/impact_assessment#');

$graph = new EasyRdf_Graph();


#$taxos = array('economic_impacts');
//$taxo_machine_name = 'economic_impacts';
_graph_setup($graph, $taxos); // top concept, hierarchy

foreach ($taxos as $taxo_machine_name) {
  _graph_add_taxo($graph, $taxo_machine_name);
}
// serialize to
// other formats: ntriples, rdfxml, turtle
$data = $graph->serialise($format);
if (!is_scalar($data)) {
    $data = var_export($data, true);
}
print($data);


function _graph_add_taxo(&$graph, $taxo_machine_name) {
    $voc = taxonomy_vocabulary_machine_name_load($taxo_machine_name);
    
    $tree = taxonomy_get_tree($voc->vid);
    $tree = entity_key_array_by_property($tree, 'tid');
    $items = array();

    foreach ($tree as $term) {

      $resource_name = _get_resource_name($term->name);
      $resource = 'ia:'. $resource_name; # single resource URI

      // $graph->addResource($resource, "rdf:type", 'owl:Class');
      $graph->addType($resource, array('skos:Concept','owl:Class')); #here
      $graph->addLiteral($resource, 'skos:prefLabel', $term->name, 'en');

      // add doc for term as descr
      $descr = _get_description_for($term);
      if(!empty($descr)) {
        $def = "<![CDATA[" . $descr . "]]";
        $graph->addLiteral($resource, 'skos:definition', $def , 'en');
      }

      foreach ($term->parents as $parent_id) {
        if($parent_id == 0) { // root item  set relation to top Concept, per convention taxo_machine_name s.U.
            $graph->addResource($resource, "skos:broader", 'ia:' . $taxo_machine_name);
        } else {  // set relation to Parent concept
            $parent = $tree[$parent_id];
            $parent_resource_name = _get_resource_name($parent->name);
            $graph->addResource($resource, 'skos:broader', 'ia:'.$parent_resource_name); 
        }

      }
    }  
}

function _get_resource_name($term_name) {
  $resource_name = str_replace(' ', '-', $term_name);
  $resource_name = strtolower($resource_name);
  return $resource_name;
}


function _get_description_for(&$term_from_tree) {

    $desc = null;

    $term = taxonomy_term_load($term_from_tree->tid); // term from tree has no fields included

    if(isset($term->field_manual_for_term['und'][0]['target_id'])) {
      $node_id_description = $term->field_manual_for_term['und'][0]['target_id'];
    }

    if($node_id_description) {
     $node = node_load($node_id_description);
     $desc = $node->body['und'][0]['value'];
    }
    return $desc;
}

function _graph_setup(&$graph, $taxos) {

/*
<> a skos:ConceptScheme ;
  skos:prefLabel "impact assessment"@en ;
  skos:hasTopConcept <impact areas> .
*/

 $resource = 'ia:'. 'http://beta.liaise-toolbox.eu/impact_assessment#';
 $graph->addType($resource, array('skos:ConceptScheme'));
 $graph->addResource($resource, "skos:hasTopConcept", 'ia:impact_areas');

/*
<impact areas>
  a  skos:Concept;
  skos:prefLabel "impact areas";
  skos:altLabel "impact areas";
  skos:definition "Description of impact areas.";
  skos:narrower <social impacts> ;
  skos:narrower <environmental impacts> ;
  skos:narrower <economic impacts>;
  skos:inScheme <> .
*/


 $resource = 'ia:impact_areas';
 $graph->addType($resource, array('skos:Concept'));
 $graph->addLiteral($resource, 'skos:prefLabel', 'Impact areas', 'en');
 #$graph->addLiteral($resource, 'skos:definition', 'TODO Description of impact areas.', 'en');

/*
 $graph->addResource($resource, "skos:narrower", 'ia:economic_impacts']);
 $graph->addResource($resource, "skos:narrower", 'ia:environmental_impacts']);
 $graph->addResource($resource, "skos:narrower", 'ia:social_impacts']);
*/
/* single taxos */


foreach ($taxos as $machine_name) {

     $voc = taxonomy_vocabulary_machine_name_load($machine_name);
     $def = "<![CDATA[" . $voc->description . "]]";

     $resource = 'ia:' . $machine_name;
     $graph->addType($resource, array('skos:Concept'));
     $graph->addLiteral($resource, 'skos:prefLabel', $voc->name , 'en');
     $graph->addLiteral($resource, 'skos:definition', $def, 'en');
     $graph->addResource($resource, "skos:broader", 'ia:impact_areas');
}



}
?>
