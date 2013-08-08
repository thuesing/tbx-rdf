<?php
/**
 * @depends http://www.easyrdf.org/
 * @see examples/graph_direct.php example
 * validate here: http://www.rdfabout.com/demo/validator/
 * php demo.php > tbx:models.$(date +"%Y-%m-%d-%T").turtle
 */

set_include_path(get_include_path() . PATH_SEPARATOR . 'easyrdf/lib/');
require_once "EasyRdf.php";

# load node
$string = file_get_contents("model.exhibit.json");
$json=json_decode($string,true);

# test item 84
# ECME 6
# $item = $json['items'][6];
# $json['items'] = array($item); // test
# print_r(array_keys($json['items']));
# exit;
$mappings = get_mappings();
# set namespace
EasyRdf_Namespace::set('tbx', 'http://beta.liaise-toolbox.eu/');

$graph = new EasyRdf_Graph();

foreach ($json['items'] as $item) {
  map_item($graph, $item);
}

// other formats: ntriples, rdfxml, turtle
$data = $graph->serialise('turtle');
if (!is_scalar($data)) {
    $data = var_export($data, true);
}

print($data);

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
    $resource = 'tbx:'.$item['url']; # single resource URI
    $type = 'tbx:'.$item['type'];
    unset($item['url']);
    unset($item['type']);

    #$graph->setType('tbx:'.$item['url'],  'tbx:'.$item['type']); same as
    $graph->addResource($resource, "rdf:type", $type);
    $graph->addType($resource, $mappings['rdftype']); #here

    # set editor if null
    $item['editor'] = isset($item['editor']) ? $item['editor'] : $item['author'];
    $graph->addResource($resource, "sioc:has_creator", 'tbx:' . $item['editor']);
    unset($item['editor']);
    unset($item['author']);
    
    # map title
    $graph->addResource($resource, 'dc:title', $item['name']);
    unset($item['name']);
    
    # map created
    $graph->addResource($resource, 'dc:date', $item['changed']);
    $graph->addResource($resource, 'dc:created', $item['changed']);
    unset($item['changed']);
    
    if(isset($item['field_website_for_contact'])) {
        $graph->addResource($resource, 'foaf:homepage', $item['field_website_for_contact']);
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



/* map vocabs

$header = <<<EOD
    @prefix rdf:    <http://www.w3.org/1999/02/22-rdf-syntax-ns#>.
    @prefix dc:     <http://purl.org/dc/elements/1.1/#>.
    @prefix foaf:   <http://xmlns.com/foaf/0.1/>.
    @prefix dc:     <http://purl.org/dc/elements/1.1/>. 
    @prefix model:  <http://beta.liaise-toolbox.eu/onto/0.1/model>.
    @base           <http://beta.liaise-toolbox.eu/>.
EOD;

$map = array(
    'field_acronym' => '',
    'field_body' => '',
    'field_contact' =>  '',
    'field_countries' =>  '',
    'field_enduser_documentation' => '',
    'field_example_outputs'  =>  '',
    'field_input' =>  '',
    'field_ipr' =>  '',
    'field_liaise_ownership' =>  '',
    'field_output' =>  '',
    'field_policy_areas' =>  '',
    'field_policy_instrument' =>  '',
    'field_scientific_documentation' =>  '',
    'field_website_for_contact' =>  '',
    'field_economic_impacts' =>  '',
    'field_environmental_impacts' =>  '',
    'field_social_impacts' =>  '',
    'field_e_mail' =>  '',
    'field_spatial_coverage' =>  '',
    'field_economic_sectors' =>  '',
    'field_time_horizon' =>  '',
  );
*/


function get_mappings() {

    # taken from example mapping from node.module:
    # https://api.drupal.org/api/drupal/modules!rdf!rdf.module/group/rdf/7
    return array(
              'rdftype' => array('sioc:Item', 'foaf:Document'),
              'name' => array(
                'predicates' => array('dc:title'),
              ),
              'changed' => array(
                'predicates' => array('dc:date', 'dc:created'),
                'datatype' => 'xsd:dateTime',
                'callback' => 'date_iso8601',
              ),
              'editor' => array(
                'predicates' => array('sioc:has_creator'),
              ),
              'editor_name' => array(
                'predicates' => array('foaf:name'),
              ),
            );

}


function set_namespaces() { // not functional yet
    # https://api.drupal.org/api/drupal/core%21modules%21rdf%21rdf.api.php/function/hook_rdf_namespaces/8
    $namespaces = array(
        'content' => 'http://purl.org/rss/1.0/modules/content/',
        'dc' => 'http://purl.org/dc/terms/',
        'foaf' => 'http://xmlns.com/foaf/0.1/',
        'og' => 'http://ogp.me/ns#',
        'rdfs' => 'http://www.w3.org/2000/01/rdf-schema#',
        'sioc' => 'http://rdfs.org/sioc/ns#',
        'sioct' => 'http://rdfs.org/sioc/types#',
        'skos' => 'http://www.w3.org/2004/02/skos/core#',
        'xsd' => 'http://www.w3.org/2001/XMLSchema#',
      );

      # set namespaces
    EasyRdf_Namespace::set('tbx', 'http://beta.liaise-toolbox.eu/');
    foreach ($namespaces as $key => $value) {
        EasyRdf_Namespace::set($key, $value);
    }
    print_r(EasyRdf_Namespace::namespaces());
}

?>
