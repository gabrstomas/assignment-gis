@extends('layouts.layout')

@section('content')
    <div class='col-md-9' id ='map'>
    </div>
    <div class='col-md-3'>
        <div class='checkbox'>
            <label><input type='checkbox' id='checkGradient'>Fill color as gradient</label>
            <label><input checked type='checkbox' id='under11'>Bezpecne</label>
            <label><input checked type='checkbox' id='under21'>Relativne bezpecne</label>
            <label><input checked type='checkbox' id='under31'>Mierne nebezpecne</label>
            <label><input checked type='checkbox' id='under41'>Nebezpecne</label>
            <label><input checked type='checkbox' id='others'>Extremne nebezpecne</label>
            <button type="button" class="btn btn-primary" id="refreshButton">Aktualizovat</button>
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
        })

        var neighborhoodsLayerDefaultValues = {
            'name': 'neighborhoods',
            'type': 'fill'
        }

        var fillColorStyles = {
            'interval': {
                'property': 'crimes_count',
                'type': 'interval',
                'stops': [
                    [0, '#00ff00'],
                    [11, '#9fff00'],
                    [21, '#ffd300'],
                    [31, '#ff6900'],
                    [41, '#ff0000']
                ]
            },
            'gradient': {
                'property': 'crimes_count',
                'stops': [
                    [10, '#00ff00'],
                    [20, '#9fff00'],
                    [30, '#ffd300'],
                    [40, '#ff6900'],
                    [100, '#ff0000']
                ]
            }
        }

        map.on('load', function() {
            $.get('/api/neighborhoods', {
                    'under11': $('#under11').is(':checked') ? 1 : 0,
                    'under21': $('#under21').is(':checked') ? 1 : 0,
                    'under31': $('#under31').is(':checked') ? 1 : 0,
                    'under41': $('#under41').is(':checked') ? 1 : 0,
                    'others': $('#others').is(':checked') ? 1 : 0
                }, function(data) {
                    var featureCollection = JSON.parse(data);
                    map.addLayer({
                        'id': neighborhoodsLayerDefaultValues.name,
                        'type': neighborhoodsLayerDefaultValues.type,
                        'source': {
                            'type': 'geojson',
                            'data': featureCollection,
                        },
                        'layout': {},
                        'paint': {
                            'fill-color': fillColorStyles.interval,
                            'fill-opacity': 0.4,
                            'fill-outline-color': 'blue'
                        }
                    });
            });

            map.on('click', 'neighborhoods', function (e) {
                new mapboxgl.Popup()
                    .setLngLat(e.lngLat)
                    .setHTML('<b>' + e.features[0].properties.title + '</b> ' + e.features[0].properties.crimes_count)
                    .addTo(map);
            });
        })

        $(document).ready(function() {
            $('#checkGradient').change(function() {
                var value = $(this).is(':checked');
                if (value) {
                    map.setPaintProperty(
                        neighborhoodsLayerDefaultValues.name,
                        'fill-color',
                        fillColorStyles.gradient
                    );
                } else {
                    map.setPaintProperty(
                        neighborhoodsLayerDefaultValues.name,
                        'fill-color',
                        fillColorStyles.interval
                    );
                }
            });

            $('#refreshButton').click(function() {
                $.get('/api/neighborhoods', {
                    'under11': $('#under11').is(':checked') ? 1 : 0,
                    'under21': $('#under21').is(':checked') ? 1 : 0,
                    'under31': $('#under31').is(':checked') ? 1 : 0,
                    'under41': $('#under41').is(':checked') ? 1 : 0,
                    'others': $('#others').is(':checked') ? 1 : 0
                }, function(data) {
                    var featureCollection = JSON.parse(data);
                    var source = map.getSource(neighborhoodsLayerDefaultValues.name);
                    source.setData(featureCollection);
                });
            });
        });
    </script>
@endsection