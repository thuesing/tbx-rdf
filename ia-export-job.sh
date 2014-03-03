#!/bin/bash
rm /Users/m1182/files/impact-areas.rdf
drush php-script impact_areas.exporter.php > /Users/m1182/files/impact-areas.rdf
