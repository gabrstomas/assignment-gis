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

        var schoolsLayerDefaultValues = {
            'name': 'schools',
            'type': 'circle'
        }

        var fillColorStyles = {
            'interval': {
                'property': 'station_distance',
                'type': 'interval',
                'stops': [
                    [0, 'red'],
                    [300, 'blue']
                ]
            }
        }

        map.on('load', function() {
            $.get('/api/schools', function(data) {
                var featureCollection = JSON.parse(data);
                map.addLayer({
                    'id': schoolsLayerDefaultValues.name,
                    'type': schoolsLayerDefaultValues.type,
                    'source': {
                        'type': 'geojson',
                        'data': featureCollection,
                    },
                    'paint': {
                        'circle-radius': 5,
                        'circle-color': fillColorStyles.interval
                    }
                });
            });

            map.on('click', schoolsLayerDefaultValues.name, function (e) {
                new mapboxgl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML('<b>' + e.features[0].properties.school_title + '</b> ' + e.features[0].properties.station_distance)
                    .addTo(map);
            });
        });
    </script>
@endsection