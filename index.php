<!DOCTYPE html>
<html>
<head>
    <title>MapThatTrash</title>
    <meta charset="utf-8"/>
    <link rel="stylesheet" href="http://cdn.leafletjs.com/leaflet-0.7/leaflet.css"/>
    <link rel="stylesheet" href="assets/css/style.css">
    <?php
    // get needed files
    $all = "additional/image_to_process/"; //path where all images are stored
    $processedFile = fopen("additional/import/processed.json",'r'); //path and NAME of the JSON file which contains the number of trash per photos cf:README
    if($processedFile !=null){
        $processed = json_decode(fread($processedFile,filesize("additional/import/processed.json")));
    }
    error_reporting(0); //disable php debug errors
    $files = scandir($all); // scan the additional/image_to_process/ directory to find all the subs directory
    unset($files[0]); // remove the line which contains the . file
    unset($files[1]); // remove the line which contains the .. file
    $files = array_values($files); // reindex the array
    $photos = []; // array which will contains all the paths for the photos
    $nbrePhotos = 0;// photos counter
    for ($k = 0; $k < sizeof($files); $k++) {  // for each sub directory
        $listPict = scandir($all . $files[$k] . '/'); //scan to get all pictures
        unset($listPict[0]); // remove the line which contains the . file
        unset($listPict[1]);// remove the line which contains the .. file
        $listPict = array_values($listPict);// reindex the array
        $photos[$files[$k]] = $listPict; //add the photos to the array
        $nbrePhotos += sizeof($listPict); // increment counter
    }
    $coordinates = []; // array which will contain the list of the gps coordinates
    $export = []; // array which will be exported as JSON
    for ($i = 0; $i < sizeof($photos); $i++) { // for each sub directory
        for ($j = 0; $j < sizeof($photos[$files[$i]]); $j++) { // for each photos
            $nbreTrash = 1;
            $path = $all . $files[$i] . '/' . $photos[$files[$i]][$j]; // entire path to the photo

            $data = exif_read_data($path, 0, true); // read the metadata of the picture
            if (array_key_exists('GPS', $data)) {   // if GPS metadata are found

                foreach ($processed as $img) {
                    if ($img->path == $photos[$files[$i]][$j]) { // make a correlation between all the pictures and the processed.json files (add the number of trash for each pictures)
                        if ($img->NB_trash != 0) {
                            $nbreTrash = (int)$img->NB_trash;
                        }
                    }
                }

                // GPS metadata processing
                $latDMS = $data['GPS']['GPSLatitude'];
                $lattmp1 = explode("/", $latDMS[0]);
                $latDMS[0] = (float)$lattmp1[0] / (float)$lattmp1[1];
                $lattmp2 = explode("/", $latDMS[1]);
                $latDMS[1] = (float)$lattmp2[0] / (float)$lattmp2[1];
                $lattmp3 = explode("/", $latDMS[2]);
                $latDMS[2] = (float)$lattmp3[0] / (float)$lattmp3[1];


                $longDMS = $data['GPS']['GPSLongitude'];

                $longtmp1 = explode("/", $longDMS[0]);
                $longDMS[0] = (float)$longtmp1[0] / (float)$longtmp1[1];
                $longtmp2 = explode("/", $longDMS[1]);
                $longDMS[1] = (float)$longtmp2[0] / (float)$longtmp2[1];
                $longtmp3 = explode("/", $longDMS[2]);
                $longDMS[2] = (float)$longtmp3[0] / (float)$longtmp3[1];

                // conversion between DMS format to DD format
                $latDD = (float)$latDMS[0] + ((float)$latDMS[1] / 60) + ((float)$latDMS[2] / 3600);
                $longDD = (float)$longDMS[0] + ((float)$longDMS[1] / 60) + ((float)$longDMS[2] / 3600);

                // export data for the javascript script
                $pos = ['lat' => $latDD, 'long' => $longDD, 'n' => $files[$i], 'nb' => $nbreTrash, "path" => $path];
                array_push($coordinates, $pos);
                $exportedData = [$path => ["neighborhood" => $files[$i], "latDMS" => ["Degrees_D" => $latDMS[0], "Minutes_M" => $latDMS[1], "Seconds_S" => $latDMS[2]], "latDD" => $latDD, "longDMS" => ["Degrees_D" => $longDMS[0], "Minutes_M" => $longDMS[1], "Seconds_S" => $longDMS[2]], "longDD" => $longDD, "nbTrash" => $nbreTrash]];
                array_push($export, $exportedData);

            }
        }
    }

    $jsonCoordinates = json_encode($coordinates);
    $exportedJson = json_encode($export);
    $fp = fopen('additional/export/data.json', 'w');
    fwrite($fp, $exportedJson);
    fclose($fp);

    ?>
</head>
<body id="body">
<header>
    <img src="assets/image/logo.png" height="180px" width="180px">
    <p><a>Nombre de fichiers: <?php echo $nbrePhotos; ?></a><a>Nombre de fichiers
            utilisables: <?php echo sizeof($coordinates); ?></a></p>

</header>
<input type="text" id="hidden" value='<?php echo $jsonCoordinates ?>' hidden>
<div id="map"></div>
<script
        src="http://cdn.leafletjs.com/leaflet-0.7/leaflet.js">
</script>

<script>

    // get the json data stored in a hidden html input
    var rawData = document.getElementById("hidden").value;
    //convert JSON to javascript objects
    var data = JSON.parse(rawData);

    /* neighborhoud area */
    var area1 = [[52.3817287, 4.890137], [52.3767996, 4.8852289], [52.3675876, 4.8821311], [52.3626085, 4.8880487], [52.3615002, 4.8869921], [52.3601272, 4.8873465], [52.360814, 4.8887818], [52.3635358, 4.9024113], [52.3646945, 4.9117575], [52.3694958, 4.9195856], [52.374634, 4.913649], [52.380117, 4.895579]];
    var area2 = [[52.3723747, 4.8429297], [52.3803887, 4.8455755], [52.3849278, 4.8453949], [52.3856418, 4.8814675], [52.3828858, 4.8903134], [52.3741366, 4.8833848], [52.3757895, 4.8674628], [52.3742839, 4.8551986]];
    var area3 = [[52.374226, 4.883825], [52.374213, 4.876486], [52.375738, 4.867456], [52.374337, 4.855246], [52.373367, 4.852757], [52.372896, 4.848723], [52.372201, 4.845955], [52.372214, 4.842114], [52.357893, 4.842479], [52.358470, 4.852736], [52.357723, 4.855010], [52.363620, 4.878828], [52.365035, 4.878657], [52.365991, 4.882476], [52.366489, 4.882111]];
    var area4 = [[52.3661174, 4.882843], [52.362734, 4.875269], [52.357822, 4.854925], [52.357000, 4.854537], [52.349784, 4.857438], [52.347464, 4.857438], [52.346312, 4.867129], [52.346933, 4.879512], [52.349746, 4.886373], [52.360057, 4.886820], [52.360583, 4.885845], [52.362731, 4.888044]];
    var area5 = [[52.3609593, 4.8886935], [52.3614786, 4.8959424], [52.3625279, 4.902052], [52.3472792, 4.9118562], [52.3462506, 4.9051472], [52.3420087, 4.8925762], [52.3442339, 4.8906252], [52.3445126, 4.8858826], [52.3600504, 4.8872314]];
    var area6 = [[52.369783, 4.918634], [52.364647, 4.911917], [52.363048, 4.902605], [52.356169, 4.905979], [52.349327, 4.911268], [52.344569, 4.913714], [52.344235, 4.919261], [52.346050, 4.926396], [52.355356, 4.942661], [52.357479, 4.936985], [52.360600, 4.932818], [52.363430, 4.931043], [52.366876, 4.925850]];
   /* end of neighborhoud area */

   //display the map
    var map = L.map('map').setView([52.370216, 4.895168], 14);
    mapLink = '<a href="http://openstreetmap.org">OpenStreetMap</a>';

    // crappy design :  mapper to get the index of the colors array
    var mapper = ["neighborhood1", "neighborhood2", "neighborhood3", "neighborhood4", "neighborhood5", "neighborhood6"];
    // color array for each neighborhood
    var colors = ['orange', 'red', 'yellow', 'green', 'purple', 'blue'];


    /* neighborhood drawing initialization */
    var polygon1 = L.polygon(area1, {color: colors[0]}).bindPopup("Neighborhood 1");
    var polygon2 = L.polygon(area2, {color: colors[1]}).bindPopup("Neighborhood 2");
    var polygon3 = L.polygon(area3, {color: colors[2]}).bindPopup("Neighborhood 3");
    var polygon4 = L.polygon(area4, {color: colors[3]}).bindPopup("Neighborhood 4");
    var polygon5 = L.polygon(area5, {color: colors[4]}).bindPopup("Neighborhood 5");
    var polygon6 = L.polygon(area6, {color: colors[5]}).bindPopup("Neighborhood 6");
    /* end of neighborhood drawing initialization */

    /* grahical elements groups */
    var markers = [];
    var circles = [];
    var l1 = [polygon1]; // list neighborhood 1
    var l2 = [polygon2];
    var l3 = [polygon3];
    var l4 = [polygon4];
    var l5 = [polygon5];
    var l6 = [polygon6];

    // for each pictures
    for (var i in data) {

        var lat = data[i]["lat"];
        var long = data[i]["long"];
        var nbre = data[i]["nb"];
        var mapped = mapper.indexOf(data[i]["n"]);
        var path = data[i]["path"];

        /* marker initialization */
        var marker = L.marker([lat, long]).bindPopup("lat:" + lat + ",long:" + long);
        markers.push(marker); // add marker to group of marker

        /* circles initialization */
        var circle = L.circleMarker([lat, long], {
            radius: 5+(nbre*1.5),
            fillOpacity:0.2 +(nbre/10),
            color: colors[mapped]
        }).bindPopup("lat:" + lat + ",long:" + long+", Nbre trash:"+nbre+"\n<img height='170px' witdh='170px' style='margin-left: 25px;' src='"+path+"'>");
        circles.push(circle); // add circles to group of circles

        /* add circles to neighborhood groups*/
        switch (mapped) {
            case 0:
                l1.push(circle);
                break;

            case 1:
                l2.push(circle);
                break;

            case 2:
                l3.push(circle);
                break;

            case 3:
                l4.push(circle);
                break;

            case 4:
                l5.push(circle);
                break;

            case 5:
                l6.push(circle);
                break;
        }

    }

    /* map layers initialization */
    var areaLayer = L.layerGroup([polygon1, polygon2, polygon3, polygon4, polygon5, polygon6]);
    var markerLayer = L.layerGroup(markers);
    var circleLayer = L.layerGroup(circles);
    var l1Layer = L.layerGroup(l1);
    var l2Layer = L.layerGroup(l2);
    var l3Layer = L.layerGroup(l3);
    var l4Layer = L.layerGroup(l4);
    var l5Layer = L.layerGroup(l5);
    var l6Layer = L.layerGroup(l6);

    L.tileLayer(
        'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; ' + mapLink + ' Contributors',
            maxZoom: 16,
            layers: [areaLayer, markerLayer, circleLayer, l1Layer, l2Layer, l3Layer, l4Layer, l5Layer, l6Layer]
        }).addTo(map);
    /* end of map layers initialization */

    /* top rigth corner map overlay initialization */
    var overlayMarker = {
        "Marker": markerLayer,
        "Circle": circleLayer
    }

    var overlayArea = {
        "Neighborhood area": areaLayer,
        "Only n1": l1Layer,
        "Only n2": l2Layer,
        "Only n3": l3Layer,
        "Only n4": l4Layer,
        "Only n5": l5Layer,
        "Only n6": l6Layer,
    }
    /* end of top rigth corner map overlay initialization */

    L.control.layers(overlayMarker, overlayArea).addTo(map);// add overlay to the map

</script>
</body>
</html>

