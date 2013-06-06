<?php
# http://www.rdfabout.com/demo/validator/
# @see http://data-gov.tw.rpi.edu/wiki/ARC2

include_once('arc2/ARC2.php');
header('Content-Type: text/plain');

# load node
$string = file_get_contents("model.exhibit.json");
$json=json_decode($string,true);

# test item
$item = $json['items'][6];
#print_r($item);

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

$out = $header;

$out .= ':'. $item['url'] . ' a :onto/0.1/model;' . PHP_EOL;

foreach ($item as $key => $value) {
    if(is_array($value)) {
      foreach ($value as $val) {
        $out .= "model:$key  \"$val\";" . PHP_EOL;
      }
    } else {
      $out .= "model:$key  \"$value\";" . PHP_EOL;
    }
}

$out .= '.' . PHP_EOL;

print_r($out);
exit;

# convert
$aux = array();

$aux['s'] = "http://beta.liaise-toolbox.eu" . $item['url'];
$aux['s_type'] = 'uri';

//Predicate
$aux['p'] = "rdf:about";

//Object
$aux['o'] = "http://beta.liaise-toolbox.eu/onto/0.1/model";
$aux['o_type'] = "uri";



$triples = array();
array_push($triples, $aux);

$conf = array('ns' => array());
/* custom namespace prefixes */
$ns = array(
  'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',	
  'foaf' => 'http://xmlns.com/foaf/0.1/',
  'dc' => 'http://purl.org/dc/elements/1.1/',  
);
$conf = array('ns' => $ns);

/* Serializer instantiation */
$ser = ARC2::getTurtleSerializer();

/* Serialize a triples array */
$doc = $ser->getSerializedTriples($triples);

 echo $doc;
 echo "\n";





// D6 style query, @deprecated
function exhibit_get_nodes_d6($for_type) {
   $result = db_query('SELECT n.nid FROM {node} n WHERE n.type = :type AND n.status = 1 ORDER BY n.nid', array(':type' => $for_type));
   $nodes = array();
   foreach($result as $res) { 
     $node = node_load($res->nid);
     $nodes[] = $node; 
   }   
   return $nodes; 
 }

// D7 query 
function exhibit_get_item($node) {

  $node_field_names = _get_field_names_for($node->type);  

  $row = array(); // for JSON

  $row['name'] = $row['label'] = $node->title;
  $row['type'] = $node->type;
  $row['author'] = 'user/' . $node->uid;
  $row['created'] = gmstrftime(EXHIBIT_DATE_FORMAT, $node->created);
  $row['changed'] = gmstrftime(EXHIBIT_DATE_FORMAT, $node->changed);
  $row['url']     = url('node/' . $node->nid, array('absolute' => FALSE));

  if ($node->uid != $node->revision_uid) {
    // Let's get the THEMED name of the last editor.
    $user = user_load($node->revision_uid);
    $row['editor'] = 'user/' . $node->revision_uid;
  } 

  foreach($node_field_names as $field_name) {

    // http://api.drupal.org/api/drupal/modules%21field%21field.info.inc/function/field_info_field/7
    $field_info = field_info_field($field_name);
    $field_instance = field_info_instance('node', $field_name, $node->type);
    $field_items = field_get_items('node', $node, $field_name); 
    
    if(empty($field_items)) continue;
    
    $field_value = null;
    $type = $field_info['module'] ;
    
    if ($type == 'text') {
       $field_value = $field_items[0]['value'];
       #$field_value = utf8_encode ( $field_value );     //  http://www.php.net/manual/de/function.utf8-encode.php
       $row[$field_name] = $field_value;  
    } elseif(($type == 'list')) { // boolean
       $field_value = ($field_items[0]['value'] == 0) ? 'yes' : 'no';
       $row[$field_name] = $field_value;
    } elseif(($type == 'taxonomy')) { 
      // parse terms
         $terms = _get_terms_for_field_items($field_items);         
         $term_names = array_keys($terms);     
         $field_value = $term_names;
         $row[$field_name] = $field_value;         
    } else { // parse generic
        $field_value = array();
        // collect field values
        foreach($field_items as $val) :
          if(empty($val)) {
            continue; 
          } else {
            $field_value[] = $val;  
          }        
        endforeach;
        
        if(sizeof($field_value) == 1) { // json output as value
          $row[$field_name] = $field_value[0];
        } elseif(sizeof($field_value) > 1) { // output as array
          $row[$field_name] = $field_value;
        }

    }

    // get rid of field prefix
    /*
    if( (!empty($row[$field_name])) AND (substr($field_name, 0, 6) == 'field_') ) {
       $new_key = substr ( $field_name, 6 );
       $row[$new_key] = $row[$field_name];
       unset($row[$field_name]); 
    }*/
    // unset null values
    if (empty($row[$field_name])) {
      unset($row[$field_name]); 
    } 
   
  } // foreach($node_field_names as $field_name)
  
  return $row;
}

/*
 * return an array of taxonomy field names for bundle
 * module is the module, the field is stored by
 */
 
function _get_field_names_for($bundle_name, $module = null) {
 
    // http://api.drupal.org/api/drupal/modules%21field%21field.info.inc/group/field_info/7
    $instances = field_info_instances('node', $bundle_name);
    $fields = array();
    foreach($instances as &$field) :
      $field_info = field_info_field($field['field_name']);
      //print_r($field_info);
      if($module){
        // echo $field_info['module'] . "\n";
        if($field_info['module'] == $module){    
          $fields[] = $field['field_name'];
        } 
      } else { // no module filter
        $fields[] = $field['field_name'];
      }      
    endforeach;
    return  $fields;

}
 
/*
 * return an hash term_name => term_object  
 */
 
function _get_terms_for_field_items($field_items) { 
    //if(empty($field_items); 
    $terms = array();
    foreach($field_items as $val) :
  
      $term = taxonomy_term_load($val['tid']);
      //$terms[] = $term->name;     
      if(empty($term->name) || empty($term)) {
       continue;
      }      
      $terms[$term->name] = $term;
      
    endforeach;
    return  $terms;

} 

