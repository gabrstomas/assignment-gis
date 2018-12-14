@extends('layouts.layout')

@section('content')
    <div class='col-md-9' id ='map'>
    </div>
    <div class='col-md-3'>
    </div>
@endsection

@section('script')
    <script>
        var token = '{{ config('services.mapbox.token') }}'
        mapboxgl.accessToken = token;

        var map = new mapboxgl.Map({
            container: 'map',
            style: 'mapbox://styles/mapbox/streets-v10',
            center: [-87.668385, 41.859365],
            zoom: 10,
            accessToken: token
        })

        var stationsLayerDefaultValues = {
            'name': 'stations',
            'type': 'circle'
        }

        var crimesLayerDefaultValues = {
            'name': 'crimes',
            'type': 'circle'
        }

        var styles = {
            'intervalColors': {
                'property': 'count',
                'type': 'interval',
                'stops': [
                    [0, '#ff6600'],
                    [5, '#dd5500'],
                    [10, '#bb4400'],
                    [20, '#993300'],
                    [30, '#772200'],
                    [40, '#551000'],
                    [50, '#330000']
                ]
            },
            'radius': {
                'property': 'count',
                'type': 'exponential',
                'stops': [
                    [0, 2],
                    [5, 4],
                    [10, 6],
                    [20, 8],
                    [30, 10],
                    [40, 12],
                    [50, 14]
                ]
            }
        }

        var hoveredStateId = null;

        map.on('load', function() {
            $.get('/api/stations', function(data) {
                var featureCollection = JSON.parse(data);
                map.addLayer({
                    'id': stationsLayerDefaultValues.name,
                    'type': stationsLayerDefaultValues.type,
                    'source': {
                        'type': 'geojson',
                        'data': featureCollection,
                    },
                    'paint': {
                        'circle-radius': styles.radius,
                        'circle-color': styles.intervalColors,
                        "circle-opacity": ["case",
                            ["boolean", ["feature-state", "hover"], false],
                            1,
                            0.5
                        ]
                    }
                });
            });

            map.on('click', stationsLayerDefaultValues.name, function (e) {
                var coordinates = e.features[0].geometry.coordinates.slice();

                while (Math.abs(e.lngLat.lng - coordinates[0]) > 180) {
                    coordinates[0] += e.lngLat.lng > coordinates[0] ? 360 : -360;
                }

                new mapboxgl.Popup()
                    .setLngLat(coordinates)
                    .setHTML('<b>' + e.features[0].properties.title + '</b> ' + e.features[0].properties.count)
                    .addTo(map);
            });

            map.on("mousemove", stationsLayerDefaultValues.name, function(e) {
                if (e.features.length > 0) {
                    if (hoveredStateId) {
                        map.setFeatureState({source: stationsLayerDefaultValues.name, id: hoveredStateId}, { hover: false});
                    }
                    var oldHoveredId = hoveredStateId;
                    hoveredStateId = e.features[0].id;

                    if (hoveredStateId != oldHoveredId) {
                        $.get('/api/crimes_for_station', {
                            'stationId': hoveredStateId
                        }, function(data) {
                            var featureCollection = JSON.parse(data);

                            if (map.getLayer(crimesLayerDefaultValues.name) == null) {
                                map.addLayer({
                                    'id': crimesLayerDefaultValues.name,
                                    'type': crimesLayerDefaultValues.type,
                                    'source': {
                                        'type': 'geojson',
                                        'data': featureCollection,
                                    },
                                    'paint': {
                                        'circle-radius': 5,
                                        'circle-color': 'black'
                                    }
                                });
                            } else {
                                map.getSource(crimesLayerDefaultValues.name).setData(featureCollection);
                            }
                            map.setLayoutProperty(crimesLayerDefaultValues.name, 'visibility', 'visible');
                        });
                    }

                    map.setFeatureState({source: stationsLayerDefaultValues.name, id: hoveredStateId}, { hover: true});
                }
            });

            map.on("mouseleave", stationsLayerDefaultValues.name, function() {
                if (hoveredStateId) {
                    map.setFeatureState({source: stationsLayerDefaultValues.name, id: hoveredStateId}, { hover: false});
                }
                hoveredStateId = null;

                if (map.getLayer(crimesLayerDefaultValues.name) != null) {
                    map.setLayoutProperty(crimesLayerDefaultValues.name, 'visibility', 'none');
                }
            });
        });
    </script>
@endsection