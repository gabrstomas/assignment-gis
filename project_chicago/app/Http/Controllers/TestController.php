<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function test() {
        return view('test');
    }

    public function schools()
    {
        $schools = DB::select('SELECT osm_id, amenity, ST_AsGeoJSON(ST_Transform(way, 4326)), name FROM planet_osm_point WHERE amenity = ? LIMIT 10', ['school']);
        return $schools;
    }

    public function schoolsAsFeatures()
    {
        $schools = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', osm_id,
            'geometry', ST_AsGeoJSON(ST_Transform(way, 4326))::json,
            'properties', json_build_object(
                'name', name
            )
        )
        FROM planet_osm_point
        WHERE amenity = ?
        AND ST_X(ST_Transform(way, 4326)) < -87.5658
        AND ST_X(ST_Transform(way, 4326)) > -87.6823
        AND ST_Y(ST_Transform(way, 4326)) > 41.7778
        AND ST_Y(ST_Transform(way, 4326)) < 41.9774",
        ['school']);
        return $schools;
    }

    public function schoolsWithDistances()
    {
        $schools = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', schools.osm_id,
            'geometry', ST_AsGeoJSON(ST_Transform(way, 4326))::json,
            'properties', json_build_object(
                'name', schools.name,
                'distance', station.dist,
                'closest_station', station.name
            )
        )
        FROM planet_osm_point AS schools
        CROSS JOIN LATERAL
        (SELECT
            stations.name,
            ST_Distance(
                ST_Transform(schools.way, 4326)::geography,
                ST_Transform(stations.geo, 4326)::geography
            ) as dist
            FROM stations
            ORDER BY dist
            LIMIT 1
        ) AS station
        WHERE amenity = ?
        AND ST_X(ST_Transform(way, 4326)) < -87.5658
        AND ST_X(ST_Transform(way, 4326)) > -87.7823
        AND ST_Y(ST_Transform(way, 4326)) > 41.7778
        AND ST_Y(ST_Transform(way, 4326)) < 41.9774",
        ['school']);

        return $schools;
    }

    public function stationsAsFeatures()
    {
        $stations = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', id,
            'geometry', ST_AsGeoJSON(ST_Transform(geo, 4326))::json,
            'properties', json_build_object(
                'name', name
            )
            ) FROM stations");
            
        return $stations;
    }

    public function stationsWithCrimes()
    {
        $stations = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', s.id,
            'geometry', ST_AsGeoJSON(ST_Transform(s.geo, 4326))::json,
            'properties', json_build_object(
                'name', s.name,
                'crimes', count(c.id)
            )
            ) FROM stations s
            LEFT JOIN crimes c
            ON ST_DWithin(s.geo::geography, c.geo::geography, 1000)
            GROUP BY s.id");

        return $stations;
    }

    public function crimesAsFeatures()
    {
        $crimes = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', id,
            'geometry', ST_AsGeoJSON(ST_Transform(geo, 4326))::json,
            'properties', json_build_object(
                'type', type,
                'description', description
            )
        ) FROM crimes");
        return $crimes;
    }

    public function neighborhoods()
    {
        $neighborhoods = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', gid,
            'geometry', ST_AsGeoJSON(ST_Transform(geom, 4326))::json,
            'properties', json_build_object(
                'description', pri_neigh
            )
        ) FROM neighborhoods");
        return $neighborhoods;
    }

    public function neighborhoodsWithCrimes()
    {
        $neighborhoods = DB::select("SELECT json_build_object(
            'type', 'Feature',
            'id', neighborhoods.gid,
            'geometry', ST_AsGeoJSON(ST_Transform(geom, 4326))::json,
            'properties', json_build_object(
                 'name', neighborhoods.pri_neigh,
                'count', count(crimes.geo)
             )
        )
        FROM neighborhoods LEFT JOIN crimes 
        ON st_contains(neighborhoods.geom,crimes.geo) 
        GROUP BY neighborhoods.gid;");
        return $neighborhoods;
    }
}
