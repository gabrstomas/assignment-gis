@extends('layouts.layout')

@section('content')
    <div class="row" style="margin-bottom: 30px;">
        <div class='col-md-9' id ='map'>
        </div>
        <div class='col-md-3'>
            <div class="checkbox">
                <label><input checked type='checkbox' id='checkAll'>Zobrazit vsetky skoly</label>
            </div>
            <div class="form-group">
                <label for="usr">Maximalna vzdialenost skoly od polohy:</label>
                <input type="number" class="form-control" id="maxDistanceFromLocation" value=5000>
            </div>
            <div class="form-group">
                <label for="usr">Maximalna vzdialenost skoly od najblizsej stanice:</label>
                <input type="number" class="form-control" id="maxDistanceFromStation" value=500>
            </div>
            <div class="form-group">
                <label for="usr">Polomer okruhu okolo stanice pre posudenie jej bezpecnosti:</label>
                <input type="number" class="form-control" id="stationCircle" value=1000>
            </div>
            <div class="form-group">
                <label for="usr">Maximalny pocet kriminalnych cinov v okoli stanice:</label>
                <input type="number" class="form-control" id="stationCrimesLimit" value=20>
            </div>
            <button type="button" class="btn btn-primary" onclick="searchSchools()">Vyhladat skoly</button>
        </div>
    </div>
    <!--
    <div class="row">
        <h4>Prehlad vysledkov</h4>
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nazov</th>
                        <th>Vzdialenost od stanice</th>
                        <th>Nazov najblizsej stanice</th>
                        <th>Pocet kriminalnych cinov v okoli stanice</th>
                    </tr>
                </thead>
                <tbody id="schoolsTableBody">
                    <tr>
                        <td>John</td>
                        <td>Doe</td>
                        <td>john@example.com</td>
                        <td>Doe</td>
                        <td>john@example.com</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    -->
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

        var currentLocation = new mapboxgl.Marker({
            draggable: true
        })
            .setLngLat([-87.668385, 41.859365])
            .addTo(map);

        var schoolsData = {
            'all': {
                'name': 'allSchools',
                'type': 'circle',
                'circleColor': 'green',
                'circleRadius': 6,
                'circleOpacity': 1,
                'circleStrokeColor': 'white',
                'circleStrokeWidth': 2
            },
            'selected': {
                'source': {
                    'name': 'selectedSchools',
                    'type': 'geojson'
                },
                'layer': {
                    'name': 'selectedSchools',
                    'type': 'circle',
                    'circleColor': 'blue',
                    'circleRadius': 6,
                    'circleOpacity': 1,
                    'circleStrokeColor': 'black',
                    'circleStrokeWidth': 2
                }
            }
        };

        $(document).ready(function() {
            $('#checkAll').change(function() {
                var value = $(this).is(':checked');
                setLayerVisibility(schoolsData.all.name, value);
            });
        });

        map.on('load', function() {
            $.get('/api/all_schools', function(data) {
                var featureCollection = JSON.parse(data);
                addAllSchoolsLayer(featureCollection);
            });

            map.on('click', schoolsData.all.name, function (e) {
                new mapboxgl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML(e.features[0].properties.school_title)
                    .addTo(map);
            });
        });

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

        function searchSchools() {
            var maxDistanceFromLocation = $('#maxDistanceFromLocation').val();
            var maxDistanceFromStation = $('#maxDistanceFromStation').val();
            var stationCircle = $('#stationCircle').val();
            var stationCrimesLimit = $('#stationCrimesLimit').val();

            $.get('/api/search', {
                'maxDistanceFromLocation': maxDistanceFromLocation,
                'maxDistanceFromStation': maxDistanceFromStation,
                'stationCircle': stationCircle,
                'stationCrimesLimit': stationCrimesLimit,
                'currentLocation': [currentLocation.getLngLat().lat, currentLocation.getLngLat().lng],
            }, function(data) {
                var featureCollection = JSON.parse(data);
                refreshSchoolsSource(featureCollection);
                refreshSchoolsLayer();
            });
        };

        function addAllSchoolsLayer(featureCollection) {
            map.addLayer({
                'id': schoolsData.all.name,
                'type': schoolsData.all.type,
                'source': {
                    'type': 'geojson',
                    'data': featureCollection
                },
                'paint': {
                    'circle-radius': schoolsData.all.circleRadius,
                    'circle-color': schoolsData.all.circleColor,
                    'circle-stroke-color': schoolsData.all.circleStrokeColor,
                    'circle-stroke-width': schoolsData.all.circleStrokeWidth
                }
            });
        };

        function refreshSchoolsSource(featureCollection) {
            var source = map.getSource(schoolsData.selected.source.name)
            if (source == null) {
                map.addSource(
                    schoolsData.selected.source.name,
                    {
                        'type': schoolsData.selected.source.type,
                        'data': featureCollection
                    }
                );
            } else {
                source.setData(featureCollection);
            }

            setSchoolsTableData(featureCollection.features);
        };

        function setSchoolsTableData(schools) {
            var tableBody = $('#schoolsTableBody');
            tableBody.empty();

            schools.forEach(function(school) {
                
            });
        };

        function refreshSchoolsLayer() {
            var layer = map.getLayer(schoolsData.selected.layer.name);
            if (layer == null) {
                map.addLayer({
                    'id': schoolsData.selected.layer.name,
                    'type': schoolsData.selected.layer.type,
                    'source': schoolsData.selected.source.name,
                    'paint': {
                        'circle-radius': schoolsData.selected.layer.circleRadius,
                        'circle-color': schoolsData.selected.layer.circleColor,
                        'circle-stroke-color': schoolsData.selected.layer.circleStrokeColor,
                        'circle-stroke-width': schoolsData.selected.layer.circleStrokeWidth
                    }
                });
            }
        };
    </script>
@endsection