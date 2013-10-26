<?php
    /**
     * Making a SPARQL SELECT query
     *
     * This example creates a new SPARQL client, pointing at the
     * dbpedia.org endpoint. It then makes a SELECT query that
     * returns all of the countries in DBpedia along with an
     * english label.
     *
     * Note how the namespace prefix declarations are automatically
     * added to the query.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . 'easyrdf/lib/');
    require_once "easyrdf/lib/EasyRdf.php";
    #require_once "html_tag_helpers.php";

    /*
    // Setup some additional prefixes for DBpedia
    ÉasyRdf_Namespace::set('category', 'http://dbpedia.org/resource/Category:');
    EasyRdf_Namespace::set('dbpedia', 'http://dbpedia.org/resource/');
    EasyRdf_Namespace::set('dbo', 'http://dbpedia.org/ontology/');
    EasyRdf_Namespace::set('dbp', 'http://dbpedia.org/property/');

    $sparql = new EasyRdf_Sparql_Client('http://dbpedia.org/sparql');
    */
    EasyRdf_Namespace::set('a', 'http://www.eea.europa.eu/portal_types/Data#');
    EasyRdf_Namespace::set('dcterms', 'http://purl.org/dc/terms/');

    $sparql = new EasyRdf_Sparql_Client('http://semantic.eea.europa.eu/sparql');
?>
<html>
<head>
  <title>EasyRdf Basic Sparql Example</title>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
</head>
<body>
<h1>EasyRdf Basic Sparql Example</h1>

<h2>List of datasets</h2>
<ul>
<?php
    $result = $sparql->query(
        'SELECT DISTINCT ?subj ?issued ?title  ?description WHERE {'.
          '?subj a a:Data ;'.
                'dcterms:title ?title ;'.
                'dcterms:description ?description;'.
                'dcterms:issued ?issued.'.
          'FILTER (regex(?title,"climate") || regex(?description,"climate")).'.
        '} ORDER BY ?effective'
    );
    foreach ($result as $row) {
        echo "<li>".$row->title."</li>\n";
    }
?>
</ul>
<p>Total number of countries: <?= $result->numRows() ?></p>

</body>
</html>
