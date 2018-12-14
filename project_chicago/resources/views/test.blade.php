<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.3.4/dist/leaflet.css" integrity="sha512-puBpdR0798OZvTTbP4A8Ix/l+A4dHDD0DGqYW6RQ+9jxkRFclaxxQb/SJAWZfWAkuyeQUytO7+7N4QKrDh+drA== "crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.3.4/dist/leaflet.js" integrity="sha512-nMMmRyTVoLYqjP9hrbed9S+FzjZHW5gY1TWCHA5ckwXZBadntCNs8kEqAWdrb9O7rxbCaA4lKTIWjDXZxflOcA==" crossorigin=""></script>

        <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

        <style>
            #mapid { height: 700px; }
        </style>
    </head>
    <body>
        <div id="mapid"></div>
    </body>
    <script>
        $( document ).ready(function() {
            var mymap = L.map('mapid').setView([41.859365, -87.668385], 11);
            L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoiZ2FicnN0b21hcyIsImEiOiJjam91NWZoemYxOGY0M2txaTBoZjZvNTJnIn0.YKBp066jPeTN9AXiiz2cGQ', {
                attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, <a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
                maxZoom: 18,
                id: 'mapbox.streets',
                accessToken: 'your.mapbox.access.token'
            }).addTo(mymap);

            $.get( "/api/neighborhoodcrimes", function( data ) {
                var defaultOpacity = 0.4;
                var orangeStyle = {
                    fillColor: '#ff7800',
                    fillOpacity: defaultOpacity
                };

                var redStyle = {
                    fillColor: '#b20000',
                    fillOpacity: defaultOpacity
                }

                var darkRedStyle = {
                    fillColor: '#330000',
                    fillOpacity: defaultOpacity
                };

                data.forEach(function(neighborhood) {
                    var feature = JSON.parse(neighborhood.json_build_object);
                    L.geoJSON(feature, {
                        onEachFeature: function (feature, layer) {
                            layer.bindPopup(feature.properties.name + ' ' + feature.properties.count);
                            if (feature.properties.count < 10) {
                                layer.setStyle(orangeStyle);
                            } else if (feature.properties.count < 30) {
                                layer.setStyle(redStyle);
                            } else {
                                layer.setStyle(darkRedStyle);
                            }
                        }
                    }).addTo(mymap);
                });
            });

            $.get( "/api/schooldist", function( data ) {
                var blueMarker = {
                    radius: 8,
                    fillColor: "#9999ff",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                };
                var greenMarker = {
                    radius: 8,
                    fillColor: "#99ff99",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                };

                console.log(data);

                data.forEach(function(school) {
                    var feature = JSON.parse(school.json_build_object);
                    L.geoJSON(feature, {
                        onEachFeature: function (feature, layer) {
                            layer.bindPopup(feature.properties.name + ' ' + feature.properties.distance + ' ' + feature.properties.closest_station);
                        },
                        pointToLayer: function (feature, latlng) {
                            if (feature.properties.distance > 300) {
                                return L.circleMarker(latlng, blueMarker);
                            } else {
                                return L.circleMarker(latlng, greenMarker);
                            }
                        }
                    }).addTo(mymap);
                });
            });

            $.get( "/api/stationcrimes", function( data ) {
                var orangeMarker = {
                    radius: 8,
                    fillColor: "#ff7800",
                    color: "#000",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                };

                var redMarker = {
                    radius: 8,
                    fillColor: "#b20000",
                    color: "#000",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                };

                var darkRedMarker = {
                    radius: 8,
                    fillColor: "#330000",
                    color: "#000",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                };

                data.forEach(function(station) {
                    var feature = JSON.parse(station.json_build_object);
                    L.geoJSON(feature, {
                        onEachFeature: function (feature, layer) {
                            layer.bindPopup(feature.properties.name + ' ' + feature.properties.crimes);
                        },
                        pointToLayer: function (feature, latlng) {
                            if (feature.properties.crimes < 10) {
                                return L.circleMarker(latlng, orangeMarker);
                            } else if (feature.properties.crimes < 20) {
                                return L.circleMarker(latlng, redMarker);
                            } else {
                                return L.circleMarker(latlng, darkRedMarker);
                            }
                        }
                    }).addTo(mymap);
                });
            });

            /*
            $.get( "/api/crimes", function( data ) {
                var blackMarker = {
                    radius: 8,
                    fillColor: "#000",
                    color: "#fff",
                    weight: 1,
                    opacity: 1,
                    fillOpacity: 0.8
                };

                data.forEach(function(crime) {
                    var feature = JSON.parse(crime.json_build_object);
                    L.geoJSON(feature, {
                        onEachFeature: function (feature, layer) {
                            layer.bindPopup(feature.properties.type + ' ' + feature.properties.description);
                        },
                        pointToLayer: function (feature, latlng) {
                            return L.circleMarker(latlng, blackMarker);
                        }
                    }).addTo(mymap);
                });
            });
            */
        });
    </script>
</html>
