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

$graph = new EasyRdf_Graph();

$taxo_machine_name = 'economic_impacts';

_graph_add_taxo($graph, $taxo_machine_name);

// serialize to
// other formats: ntriples, rdfxml, turtle
$data = $graph->serialise('rdfxml');
if (!is_scalar($data)) {
    $data = var_export($data, true);
}
print($data);


function _graph_add_taxo(&$graph, $taxo_machine_name) {
    $voc = taxonomy_vocabulary_machine_name_load($taxo_machine_name);
    // $voc->hierarchy 1/0
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
        if($parent_id == 0) continue; // root item    
        $parent = $tree[$parent_id];
        $parent->name = str_replace(' ', '-', $parent->name);
        $graph->addResource($resource, 'skos:broader', 'tbx:'.$parent->name); 

      }
    }  
}


/*
 * @params $graph easyrdf graph obj
 * @params $item json item
 */
function map_item(&$graph, &$item) { 
    global $namespaces, $mappings;
 

    # unset unused text etc
    unset($item['label']);
    unset($item['created']); # we use changed instead
    unset($item['field_body']);
    unset($item['field_enduser_documentation']);
    unset($item['field_scientific_documentation']);
    unset($item['field_input']);
    unset($item['field_output']);
    unset($item['field_contact']);

    # process item 

    // replace Drupal namespace
    $item['url'] = str_replace('/models/','', $item['url']);

    $resource = 'tbx:'.$item['url']; # single resource URI
    $type = 'tbx:'.$item['type'];
    unset($item['url']);
    unset($item['type']);

    #$graph->setType('tbx:'.$item['url'],  'tbx:'.$item['type']); same as
    $graph->addResource($resource, "rdf:type", $type);
    $graph->addType($resource, array('sioc:Item', 'foaf:Document')); #here

    # set editor if null
    $item['editor'] = isset($item['editor']) ? $item['editor'] : $item['author'];
    $graph->addResource($resource, "sioc:has_creator", 'tbx:' . $item['editor']);
    unset($item['editor']);
    unset($item['author']);
    
    # map title
    $graph->addLiteral($resource, 'dc:title', $item['name']);
    unset($item['name']);
    
    # map created
    $graph->addLiteral($resource, 'dc:date', $item['changed']);
    $graph->addLiteral($resource, 'dc:created', $item['changed']);
    unset($item['changed']);
    
    if(isset($item['field_website_for_contact'])) {
        $graph->addLiteral($resource, 'foaf:homepage', $item['field_website_for_contact']);
        unset($item['field_website_for_contact']);
    }
    
  # end preprocess item 

  # process plain literals
    foreach ($item as $key => &$values) {
        if(!is_array($values)) {
            $values = array($values); 
        } 
        foreach ($values as $val) {
            $graph->addLiteral($resource, 'tbx:'.$key, $val);
        }
    }

}



/*
 * @return array of hierarchical term items in simile exhibit format
 */
function _terms_for($taxo_machine_name){

    $voc = taxonomy_vocabulary_machine_name_load($taxo_machine_name);
    // $voc->hierarchy 1/0
    $tree = taxonomy_get_tree($voc->vid);
    $tree = entity_key_array_by_property($tree, 'tid');
    $items = array();

    foreach ($tree as $term) {
      foreach ($term->parents as $parent_id) {
        if($parent_id == 0) continue; // root item
        $parent = $tree[$parent_id];
        $item = array();
        $item['type'] = $voc->machine_name;
        $item['label'] = $term->name;
        $item['subtopicOf'] = $parent->name;
        if(empty($item['subtopicOf'])) unset($item['subtopicOf']); 
        $items[] = $item;
      }
    }  

    return $items;

}

?>
