<?php
 
//database login info
# Connect to PostgreSQL database
$conn = new PDO('pgsql:host=YOURSERVER;dbname=YOURDATABASE','USER','PASSWORD'); 

$lat = $_GET['lat'];
$long = $_GET['lng'];


$sql = "SELECT name, ST_AsGeoJSON(ST_Transform((geom),4326),6) AS geojson FROM county2020 WHERE
     ST_Intersects (
         county2020.geom, ST_Transform(ST_SetSRID(ST_Point( $long, $lat),4326), 26915))";

$rs = $conn->query($sql);
if (!$rs) {
    echo 'An SQL error occured.\n';
    exit;
}
# Build GeoJSON feature collection array
$geojson = array(
   'type'      => 'FeatureCollection',
   'features'  => array()
);

# Loop through rows to build feature arrays
while ($row = $rs->fetch(PDO::FETCH_ASSOC)) {
    $properties = $row;
    # Remove geojson and geometry fields from properties
    unset($properties['geojson']);
    unset($properties['the_geom']);
    $feature = array(
         'type' => 'Feature',
         'geometry' => json_decode($row['geojson'], true),
         'properties' => $properties
    );
    # Add feature arrays to feature collection array
    array_push($geojson['features'], $feature);
}

header('Content-type: application/json');
echo json_encode($geojson, JSON_NUMERIC_CHECK  );
$conn = NULL;
?>
