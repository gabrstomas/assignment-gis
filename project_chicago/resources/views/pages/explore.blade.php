@extends('layouts.layout')

@section('content')
    <div class="row">
        <div class='col-md-9' id ='map'>
        </div>
        <div class='col-md-3'>
            <div style="display: none;" id="neighborhoodFilter">
                <h4>Kategorie</h4>
                <div class="checkbox">
                    <label><input checked type="checkbox" value="verySafe" onclick="updateLayer(this)">Velmi bezpecne</label>
                </div>
                <div class="checkbox">
                    <label><input checked type="checkbox" value="safe" onclick="updateLayer(this)">Bezpecne</label>
                </div>
                <div class="checkbox">
                    <label><input checked type="checkbox" value="medium" onclick="updateLayer(this)">Stredne</label>
                </div>
                <div class="checkbox">
                    <label><input checked type="checkbox" value="dangerous" onclick="updateLayer(this)">Nebezpecne</label>
                </div>
                <div class="checkbox">
                    <label><input checked type="checkbox" value="veryDangerous" onclick="updateLayer(this)">Velmi nebezpecne</label>
                </div>
            </div>
            <div style="display: none;" id="resetButton">
                <button type="button" class="btn btn-primary" onclick="backToNeighborhoods()">Naspat na prehlad susedstiev</button>
            </div>
            <div style="display: none;" id="neighborhoodInfo">
                <h4>Susedstvo</h4>
                <b>Nazov:</b> <span id="neighborhoodTitle">Chicago</span><br>
                <b>Uroven bezpecnosti:</b> <span id="neighborhoodSafe">Stredna</span><br>
                <b>Pocet kriminalnych cinov:</b> <span id="neighborhoodCrimes">30</span><br>
                <b>Pocet skol:</b> <span id="neighborhoodSchools">8</span><br>
                <b>Pocet stanic:</b> <span id="neighborhoodStations">12</span><br>
            </div>

            <div style="display: none;" id="schoolInfo">
                <h4>Skola</h4>
                <b>Nazov:</b> <span id="schoolTitle">Elementary School</span><br>
                <b>Najblizsia stanica v susedstve:</b> <span id="schoolStation">Station number 4</span><br>
                <b>Vzdialenost od stanice:</b> <span id="schoolDistance">124m</span><br>
            </div>

            <div style="display: none;" id="stationInfo">
                <h4>Stanica</h4>
                <b>Nazov:</b> <span id="stationTitle">Station number 4</span><br>
                <b>Pocet kriminalnych cinov v okruhu 1000m:</b> <span id="stationCrimes">39</span><br>
            </div>
        </div>
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
        });

        var routeData = {
            'name': 'route',
            'type': 'line',
            'lineWidth': 3
        }

        var crimesData = {
            'name': 'crimes',
            'type': 'circle',
            'circleRadius': 5,
            'circleColor': 'black'
        }

        var schoolsData = {
            'name': 'schools',
            'type': 'circle',
            'circleColor': {
                'property': 'station_distance',
                'type': 'interval',
                'stops': [
                    [0, 'red'],
                    [300, 'blue']
                ]
            },
            'circleRadius': 8,
            'circleStrokeColor': ["case", ["boolean", ["feature-state", "active"], false], 'white', 'black'],
            'circleStrokeWidth': 2,
            'activeId': null
        };

        var stationsData = {
            'name': 'stations',
            'type': 'circle',
            'circleColor': {
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
            'circleRadius': {
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
            },
            'circleOpacity': ["case", ["boolean", ["feature-state", "hover"], false], 1, 0.5],
            'circleStrokeColor': ["case", ["boolean", ["feature-state", "active"], false], 'white', 'black'],
            'circleStrokeWidth': ["case", ["boolean", ["feature-state", "active"], false], 4, 2],
            'activeId': null,
            'hoverId': null
        }

        var neighborhoodsData = {
            'commons': {
                'fillOpacity': 0.4,
                'fillOpacityHover': 0.6,
                'fillOutlineColor': 'blue',
                'activeId': null
            },
            'verySafe': {
                'name': 'verySafeNeighborhoods',
                'type': 'fill',
                'fillColor': '#00ff00',
                'filter': [
                    'all',
                    ['<=', 'crimes_count', 10]
                ],
                'hoverId': null
            },
            'safe': {
                'name': 'safeNeighborhoods',
                'type': 'fill',
                'fillColor': '#9fff00',
                'filter': [
                    'all',
                    ['>', 'crimes_count', 10],
                    ['<=', 'crimes_count', 20]
                ],
                'hoverId': null
            },
            'medium': {
                'name': 'mediumNeighborhoods',
                'type': 'fill',
                'fillColor': '#ffd300',
                'filter': [
                    'all',
                    ['>', 'crimes_count', 20],
                    ['<=', 'crimes_count', 30]
                ],
                'hoverId': null
            },
            'dangerous': {
                'name': 'dangerousNeighborhoods',
                'type': 'fill',
                'fillColor': '#ff6900',
                'filter': [
                    'all',
                    ['>', 'crimes_count', 30],
                    ['<=', 'crimes_count', 40]
                ],
                'hoverId': null
            },
            'veryDangerous': {
                'name': 'veryDangerousNeighborhoods',
                'type': 'fill',
                'fillColor': '#ff0000',
                'filter': [
                    'all',
                    ['>', 'crimes_count', 40]
                ],
                'hoverId': null
            }
        };

        map.on('load', function() {
            $.get('/api/neighborhoods', function(data) {
                var featureCollection = JSON.parse(data);
                addNeighborhoodsLayer(featureCollection, 'verySafe');
                addNeighborhoodsLayer(featureCollection, 'safe');
                addNeighborhoodsLayer(featureCollection, 'medium');
                addNeighborhoodsLayer(featureCollection, 'dangerous');
                addNeighborhoodsLayer(featureCollection, 'veryDangerous');
                setVisibilityById('neighborhoodFilter', true);
            });
        });

        function addSchoolsLayer(featureCollection) {
            var source = map.getSource(schoolsData.name);
            if (source == null) {
                map.addLayer({
                    'id': schoolsData.name,
                    'type': schoolsData.type,
                    'source': {
                        'type': 'geojson',
                        'data': featureCollection,
                    },
                    'paint': {
                        'circle-radius': schoolsData.circleRadius,
                        'circle-color': schoolsData.circleColor,
                        'circle-stroke-color': schoolsData.circleStrokeColor,
                        'circle-stroke-width': schoolsData.circleStrokeWidth
                    }
                });

                map.on('click', schoolsData.name, function(e) {
                    setSpanText('schoolTitle', e.features[0].properties.school_title);
                    setSpanText('schoolStation', e.features[0].properties.station_title);
                    setSpanText('schoolDistance', e.features[0].properties.station_distance + 'm');

                    if (schoolsData.activeId) {
                        map.setFeatureState({source: schoolsData.name, id: schoolsData.activeId}, { active: false});
                        schoolsData.activeId = null;
                    }
                    schoolsData.activeId = e.features[0].id;
                    map.setFeatureState({source: schoolsData.name, id: schoolsData.activeId}, { active: true});

                    $.get('/api/station_for_school', {
                        'schoolId': e.features[0].id,
                        'neighborhoodId': neighborhoodsData.commons.activeId
                    }, function(data) {
                        if (data[0]) {
                            var stationId = data[0].id;
                        } else {
                            var stationId = null;
                            $('#schoolDistance').append(' (nie je v okrsku)');
                        }
                        if (stationsData.activeId) {
                            map.setFeatureState({source: stationsData.name, id: stationsData.activeId}, { active: false});
                            stationsData.activeId = null;
                        }
                        if (stationId) {
                            stationsData.activeId = stationId;
                            map.setFeatureState({source: stationsData.name, id: stationsData.activeId}, { active: true});
                        }

                        setVisibilityById('schoolInfo', true);

                        addRouteFromSchoolToStation();
                    });
                });
            } else {
                source.setData(featureCollection);
            }
        };

        function addStationsLayer(featureCollection) {
            var source = map.getSource(stationsData.name);
            if (source == null) {
                map.addLayer({
                    'id': stationsData.name,
                    'type': stationsData.type,
                    'source': {
                        'type': 'geojson',
                        'data': featureCollection
                    },
                    'paint': {
                        'circle-radius': stationsData.circleRadius,
                        'circle-color': stationsData.circleColor,
                        'circle-opacity': stationsData.circleOpacity,
                        'circle-stroke-color': stationsData.circleStrokeColor,
                        'circle-stroke-width': stationsData.circleStrokeWidth
                    }
                });

                map.on("mousemove", stationsData.name, function(e) {
                    if (e.features.length > 0) {
                        if (stationsData.hoverId) {
                            map.setFeatureState({source: stationsData.name, id: stationsData.hoverId}, { hover: false});
                            setSpanText('stationTitle', e.features[0].properties.name);
                            setSpanText('stationCrimes', e.features[0].properties.count);
                            setVisibilityById('stationInfo', true);
                        }
                        var oldHoveredId = stationsData.hoverId;
                        stationsData.hoverId = e.features[0].id;

                        if (stationsData.hoverId != oldHoveredId) {
                            $.get('/api/crimes_for_station', {
                                'stationId': stationsData.hoverId
                            }, function(data) {
                                var featureCollection = JSON.parse(data);

                                if (map.getLayer(crimesData.name) == null) {
                                    map.addLayer({
                                        'id': crimesData.name,
                                        'type': crimesData.type,
                                        'source': {
                                            'type': 'geojson',
                                            'data': featureCollection,
                                        },
                                        'paint': {
                                            'circle-radius': crimesData.circleRadius,
                                            'circle-color': crimesData.circleColor
                                        }
                                    });
                                } else {
                                    map.getSource(crimesData.name).setData(featureCollection);
                                }
                                if (stationsData.hoverId) {
                                    setLayerVisibility(crimesData.name, true);
                                }
                            });
                        }

                        map.setFeatureState({source: stationsData.name, id: stationsData.hoverId}, { hover: true});
                    }
                });

                map.on("mouseleave", stationsData.name, function() {
                    if (stationsData.hoverId) {
                        map.setFeatureState({source: stationsData.name, id: stationsData.hoverId}, { hover: false});
                    }
                    setVisibilityById('stationInfo', false);
                    stationsData.hoverId = null;

                    if (map.getLayer(crimesData.name) != null) {
                        setLayerVisibility(crimesData.name, false);
                    }
                });
            } else {
                source.setData(featureCollection);
            }
        };

        function addNeighborhoodsLayer(featureCollection, layerKey) {
            map.addLayer({
                'id': neighborhoodsData[layerKey].name,
                'type': neighborhoodsData[layerKey].type,
                'source': {
                    'type': 'geojson',
                    'data': featureCollection
                },
                'paint': {
                    'fill-color': neighborhoodsData[layerKey].fillColor,
                    'fill-opacity': ["case",
                        ["boolean", ["feature-state", "hover"], false],
                        neighborhoodsData.commons.fillOpacityHover,
                        neighborhoodsData.commons.fillOpacity
                    ],
                    'fill-outline-color': neighborhoodsData.commons.fillOutlineColor
                },
                'filter': neighborhoodsData[layerKey].filter
            });

            map.on('click', neighborhoodsData[layerKey].name, function(e) {
                if (neighborhoodsData.commons.activeId != e.features[0].id) {
                    fitBoundsToFeature(e.features[0]);
                    setVisibilityById('neighborhoodFilter', false);
                    setVisibilityById('resetButton', true);
                    neighborhoodsData.commons.activeId = e.features[0].id;
                    setLayerVisibility(crimesData.name, false);
                    setLayerVisibility(routeData.name, false);
                    setLayerVisibility(stationsData.name, false);
                    setLayerVisibility(schoolsData.name, false);
                    setSpanText('neighborhoodTitle', e.features[0].properties.title);
                    var crimesCount = e.features[0].properties.crimes_count;
                    setSpanText('neighborhoodCrimes', crimesCount);
                    if (crimesCount <= 10) {
                        setSpanText('neighborhoodSafe', 'Velmi bezpecne');
                    } else if (crimesCount <= 20) {
                        setSpanText('neighborhoodSafe', 'Bezpecne');
                    } else if (crimesCount <= 30) {
                        setSpanText('neighborhoodSafe', 'Stredne');
                    } else if (crimesCount <= 40) {
                        setSpanText('neighborhoodSafe', 'Nebezpecne');
                    } else {
                        setSpanText('neighborhoodSafe', 'Velmi nebezpecne');
                    }
                    setVisibilityById('schoolInfo', false);
                    setVisibilityById('stationInfo', false);

                    $.get('/api/items_in_neighborhood', {
                        'neighborhoodId': e.features[0].id
                    }, function(data) {
                        var schoolsFeatureCollection = JSON.parse(data.schools);
                        addSchoolsLayer(schoolsFeatureCollection);

                        var stationsFeatureCollection = JSON.parse(data.stations);
                        addStationsLayer(stationsFeatureCollection, false);

                        setLayerVisibility(schoolsData.name, true);
                        setLayerVisibility(stationsData.name, true);

                        setSpanText('neighborhoodSchools', schoolsFeatureCollection.features.length);
                        setSpanText('neighborhoodStations', stationsFeatureCollection.features.length);

                        setVisibilityById('neighborhoodInfo', true);
                    });
                }
            });

            map.on('mousemove', neighborhoodsData[layerKey].name, function(e) {
                if (e.features.length > 0) {
                    if (neighborhoodsData[layerKey].hoverId) {
                        map.setFeatureState({source: neighborhoodsData[layerKey].name, id: neighborhoodsData[layerKey].hoverId}, { hover: false});
                    }
                    neighborhoodsData[layerKey].hoverId = e.features[0].id;
                    map.setFeatureState({source: neighborhoodsData[layerKey].name, id: neighborhoodsData[layerKey].hoverId}, { hover: true});
                }
            });

            map.on('mouseleave', neighborhoodsData[layerKey].name, function() {
                if (neighborhoodsData[layerKey].hoverId) {
                    map.setFeatureState({source: neighborhoodsData[layerKey].name, id: neighborhoodsData[layerKey].hoverId}, { hover: false});
                }
                neighborhoodsData[layerKey].hoverId =  null;
            });
        };

        function updateLayer(el) {
            if (el.checked) {
                setLayerVisibility(neighborhoodsData[el.value].name, true);
            } else {
                setLayerVisibility(neighborhoodsData[el.value].name, false);
            }
        };

        function fitBoundsToFeature(feature) {
            var boundaryBox = turf.extent(feature.geometry);
            map.fitBounds(boundaryBox, {
                padding: 60
            });
        };

        function backToNeighborhoods() {
            map.flyTo({
                center: [-87.668385, 41.859365],
                zoom: 10
            })
            setLayerVisibility(crimesData.name, false);
            setLayerVisibility(schoolsData.name, false);
            setLayerVisibility(stationsData.name, false);
            setLayerVisibility(routeData.name, false);
            setVisibilityById('resetButton', false);
            setVisibilityById('neighborhoodFilter', true);
            setVisibilityById('neighborhoodInfo', false);
            setVisibilityById('schoolInfo', false);
            setVisibilityById('stationInfo', false);
        }

        function addRouteFromSchoolToStation() {
            if (schoolsData.activeId && stationsData.activeId) {
                var school = map.getSource(schoolsData.name)._data.features.filter(function (item) {
                    return item.id == schoolsData.activeId;
                });
                var start = school[0].geometry.coordinates;

                var station = map.getSource(stationsData.name)._data.features.filter(function (item) {
                    return item.id == stationsData.activeId;
                });
                var end = station[0].geometry.coordinates;

                var directionsRequest = 'https://api.mapbox.com/directions/v5/mapbox/walking/' + start[0] + ',' + start[1] + ';' + end[0] + ',' + end[1] + '?geometries=geojson&access_token=' + mapboxgl.accessToken;
                $.get(directionsRequest, function(data) {
                    var route = data.routes[0].geometry;
                    var source = map.getSource(routeData.name);
                    if (source == null) {
                        map.addLayer({
                            'id': routeData.name,
                            'type': routeData.type,
                            'source': {
                                'type': 'geojson',
                                'data': {
                                    'type': 'Feature',
                                    'geometry': route
                                }
                            },
                            'paint': {
                                'line-width': routeData.lineWidth
                            }
                        });
                    } else {
                        source.setData({
                            'type': 'Feature',
                            'geometry': route
                        });
                    }
                    setLayerVisibility(routeData.name, true);
                });
            }
        }

        function setLayerVisibility(name, value) {
            if (value == true) {
                if (map.getLayer(name) != null) {
                    map.setLayoutProperty(name, 'visibility', 'visible');
                }
            } else {
                if (map.getLayer(name) != null) {
                    map.setLayoutProperty(name, 'visibility', 'none');
                }
            }
        };

        function setSpanText(spanId, text) {
            $('#' + spanId).text(text);
        };

        function setVisibilityById(id, value) {
            if (value == true) {
                $('#' + id).show();
            } else {
                $('#' + id).hide();
            }
        };
    </script>
@endsection