<?php
/**
 * @depends http://www.easyrdf.org/
 * @see examples/graph_direct.php example
 * validate here: 
 * http://www.w3.org/RDF/Validator/
 * http://www.rdfabout.com/demo/validator
 *
 * php demo.php > tbx:models.$(date +"%Y-%m-%d-%T").turtle
 */

#set_include_path(get_include_path() . PATH_SEPARATOR . 'easyrdf/lib/');
require_once "easyrdf/lib/EasyRdf.php";

# set namespace
EasyRdf_Namespace::set('tbx', 'http://beta.liaise-toolbox.eu/');
EasyRdf_Namespace::set('ia', 'http://beta.liaise-toolbox.eu/impact_assessment#');

$graph = new EasyRdf_Graph();

$taxos = array('economic_impacts','environmental_impacts','social_impacts');
//$taxo_machine_name = 'economic_impacts';
_graph_setup(&$graph, $taxos); // top concept, hierarchy

foreach ($taxos as $taxo_machine_name) {
  _graph_add_taxo($graph, $taxo_machine_name);
}
// serialize to
// other formats: ntriples, rdfxml, turtle
$data = $graph->serialise('rdfxml');
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

      $term->name = str_replace(' ', '-', $term->name);

      $resource = 'tbx:'.$term->name; # single resource URI
      // $graph->addResource($resource, "rdf:type", 'owl:Class');
      $graph->addType($resource, array('skos:Concept','owl:Class')); #here
      $graph->addLiteral($resource, 'skos:prefLabel', $term->name, 'en');
         
      foreach ($term->parents as $parent_id) {
        if($parent_id == 0) { // root item  set relation to top Concept, per convention taxo_machine_name s.U.
            $graph->addResource($resource, "skos:broader", 'ia:' . $taxo_machine_name);
        } else {  // set relation to Parent concept
            $parent = $tree[$parent_id];
            $parent->name = str_replace(' ', '-', $parent->name);
            $graph->addResource($resource, 'skos:broader', 'tbx:'.$parent->name); 
        }

      }
    }  
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
 $graph->addLiteral($resource, 'skos:prefLabel', 'impact areas', 'en');
 $graph->addLiteral($resource, 'skos:definition', 'TODO Description of impact areas.', 'en');

/*
 $graph->addResource($resource, "skos:narrower", 'ia:economic_impacts']);
 $graph->addResource($resource, "skos:narrower", 'ia:environmental_impacts']);
 $graph->addResource($resource, "skos:narrower", 'ia:social_impacts']);
*/
/* single taxos */


foreach ($taxos as $name) {
     $resource = 'ia:' . $name;
     $graph->addType($resource, array('skos:Concept'));
     $graph->addLiteral($resource, 'skos:prefLabel', $name , 'en');
     $graph->addLiteral($resource, 'skos:definition', 'TODO Description of impact areas.', 'en');
     $graph->addResource($resource, "skos:broader", 'ia:impact_areas');
}




}
?>
