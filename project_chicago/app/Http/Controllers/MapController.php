<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapController extends Controller
{
    // Helpers
    public function asFeatureCollection($items)
    {
        $featureCollection = new \stdClass();
        $featureCollection->type = 'FeatureCollection';
        $featureCollection->features = [];

        foreach ($items as $item) {
            $feature = $this->asFeature($item);
            if ($feature) {
                array_push($featureCollection->features, $feature);
            }
        }

        return json_encode($featureCollection);
    }

    public function asFeature($item)
    {
        if ($item->id != null) {
            $feature = new \stdClass();
            $feature->type = 'Feature';
            $feature->properties = new \stdClass();

            foreach($item as $attribute => $value) {
                switch ($attribute) {
                    case 'id':
                        $feature->id = $value;
                        break;
                    case 'geom':
                        $geometry = json_decode($value);
                        $feature->geometry = json_decode($value);
                        break;
                    case 'station_distance':
                        $feature->properties->station_distance = floatval($value);
                        break;
                    default:
                        $feature->properties->$attribute = $value;
                        break;
                }
            }
            return $feature;
        } else {
            return null;
        }
    }

    // Search
    public function allSchools()
    {
        $searchQuery = '
            WITH
                schools AS (SELECT osm_id, way, name FROM planet_osm_point WHERE amenity = ?)
            SELECT
                schools.osm_id AS id,
                ST_AsGeoJSON(ST_Transform(schools.way, 4326))::json AS geom,
                schools.name AS school_title
            FROM schools
        ';

        $schools = DB::select($searchQuery, ['school']);

        $schools = $this->asFeatureCollection($schools);

        return $schools;
    }

    public function search(Request $request)
    {
        $maxDistanceFromLocation = $request->input('maxDistanceFromLocation');
        $maxDistanceFromStation = $request->input('maxDistanceFromStation');
        $stationCircle = $request->input('stationCircle');
        $stationCrimesLimit = $request->input('stationCrimesLimit');
        $currentLocation = $request->input('currentLocation');

        $searchQuery = '
            WITH
                schools AS (SELECT osm_id, way, name FROM planet_osm_point WHERE amenity = ? AND ST_DWithin(ST_Transform(way, 4326)::geography, ST_MakePoint(?, ?, 4326)::geography, ?)),
                stations AS (SELECT stations.id, stations.name, stations.geo, count(crimes.id) AS crimes_count FROM stations LEFT JOIN crimes ON ST_DWithin(stations.geo::geography, crimes.geo::geography, ?) GROUP BY stations.id HAVING count(crimes.id) <= ?)
            SELECT 
                schools.osm_id AS id,
                ST_AsGeoJSON(ST_Transform(schools.way, 4326))::json AS geom,
                schools.name AS school_title,
                station.dist AS station_distance,
                station.name AS station_title,
                station.crimes_count AS station_crimes_count
            FROM schools
            CROSS JOIN LATERAL
                (SELECT
                    stations.name,
                    ST_Distance(ST_Transform(schools.way, 4326)::geography,ST_Transform(stations.geo, 4326)::geography) as dist,
                    stations.crimes_count
                FROM stations
                ORDER BY dist
                LIMIT 1
                ) AS station
            WHERE station.dist <= ?
        ';

        $schools = DB::select($searchQuery, [
            'school',
            $currentLocation[1],
            $currentLocation[0],
            $maxDistanceFromLocation,
            $stationCircle,
            $stationCrimesLimit,
            $maxDistanceFromStation
        ]);

        $schools = $this->asFeatureCollection($schools);

        return $schools;
    }

    // Explore
    public function neighborhoods(Request $request)
    {
        if ($request->has('under11')) {
            $under11 = $request->input('under11');   
        } else {
            $under11 = true;
        }
        if ($request->has('under21')) {
            $under21 = $request->input('under21');
        } else {
            $under21 = true;
        }
        if ($request->has('under31')) {
            $under31 = $request->input('under31'); 
        } else {
            $under31 = true;
        }
        if ($request->has('under41')) {
            $under41 = $request->input('under41');
        } else {
            $under41 = true;
        }
        if ($request->has('others')) {
            $others = $request->input('others');  
        } else {
            $others = true;
        }

        $having = null;
        $havingStatements = [];
        if ($under11) {
            array_push($havingStatements, '(COUNT(crimes.id) BETWEEN 0 AND 10)');
        }
        if ($under21) {
            array_push($havingStatements, '(COUNT(crimes.id) BETWEEN 11 AND 20)');
        }
        if ($under31) {
            array_push($havingStatements, '(COUNT(crimes.id) BETWEEN 21 AND 30)');
        }
        if ($under41) {
            array_push($havingStatements, '(COUNT(crimes.id) BETWEEN 31 AND 40)');
        }
        if ($others) {
            array_push($havingStatements, '(COUNT(crimes.id) >= 41)');
        }

        if (count($havingStatements) > 0) {
            $having = implode(' OR ', $havingStatements);
        }

        $neighborhoodsQuery = '
            SELECT
                neighborhoods.gid AS id,
                ST_AsGeoJSON(ST_Transform(neighborhoods.geom, 4326))::json AS geom,
                neighborhoods.pri_neigh AS title,
                COUNT(crimes.id) AS crimes_count
            FROM neighborhoods LEFT JOIN crimes 
            ON st_contains(neighborhoods.geom,crimes.geo) 
            GROUP BY neighborhoods.gid
        ';
        if ($having) {
            $neighborhoodsQuery .= 'HAVING ' . $having;
        }
        $neighborhoods = DB::select($neighborhoodsQuery);

        $neighborhoods = $this->asFeatureCollection($neighborhoods);

        return $neighborhoods;
    }

    public function itemsInNeighborhood(Request $request)
    {
        $neighborhoodId = $request->input('neighborhoodId');

        $schoolsQuery = '
        WITH
            neighborhood AS (SELECT geom FROM neighborhoods WHERE gid = ?),
            all_schools AS (SELECT osm_id, name, way FROM planet_osm_point WHERE amenity = ?),
            schools AS (SELECT osm_id, name, way FROM all_schools JOIN neighborhood ON ST_Contains(ST_Transform(neighborhood.geom,4326), ST_Transform(all_schools.way, 4326)))
        SELECT
            schools.osm_id AS id,
            ST_AsGeoJSON(ST_Transform(schools.way, 4326))::json AS geom,
            schools.name AS school_title,
            station.dist AS station_distance,
            station.name AS station_title
        FROM schools
        CROSS JOIN LATERAL
            (SELECT
                stations.name,
                ST_Distance(ST_Transform(schools.way, 4326)::geography,ST_Transform(stations.geo, 4326)::geography) as dist
             FROM stations
             ORDER BY dist
             LIMIT 1
            ) AS station
        ';
        $schools = DB::select($schoolsQuery, [$neighborhoodId, 'school']);
        $schools = $this->asFeatureCollection($schools);

        $stationsQuery = '
            WITH
                neighborhood AS (SELECT geom FROM neighborhoods WHERE gid = ?)
            SELECT
                stations.id,
                stations.name,
                ST_AsGeoJSON(ST_Transform(stations.geo, 4326))::json AS geom,
                count(crimes.id) AS count
            FROM neighborhood
            LEFT JOIN stations
            ON ST_Contains(ST_Transform(neighborhood.geom,4326), ST_Transform(stations.geo, 4326))
            LEFT JOIN crimes
            ON ST_DWithin(stations.geo::geography, crimes.geo::geography, 1000)
            GROUP BY stations.id  
        ';
        $stations = DB::select($stationsQuery, [$neighborhoodId]);
        $stations = $this->asFeatureCollection($stations);
        
        $items['schools'] = $schools;
        $items['stations'] = $stations;

        return $items;
    }

    public function closestStationForSchool(Request $request)
    {
        $schoolId = $request->input('schoolId');
        $neighborhoodId = $request->input('neighborhoodId');

        $stationsQuery = '
            WITH
                school AS (SELECT way FROM planet_osm_point WHERE osm_id = ?),
                neighborhood AS (SELECT geom FROM neighborhoods WHERE gid = ?),
                stations_in_neighborhood AS (SELECT * FROM stations JOIN neighborhood ON ST_Contains(ST_Transform(neighborhood.geom,4326), ST_Transform(stations.geo, 4326)))
            SELECT
                station.id AS id
            FROM school
            CROSS JOIN LATERAL
                (SELECT
                    stations_in_neighborhood.*
                FROM stations_in_neighborhood
                ORDER BY ST_Distance(ST_Transform(school.way, 4326)::geography,ST_Transform(stations_in_neighborhood.geo, 4326)::geography)
                LIMIT 1
            ) AS station
        ';

        $stations = DB::select($stationsQuery, [$schoolId, $neighborhoodId]);

        return $stations;
    }

    // Others
    public function schools()
    {
        $schoolsQuery = '
            SELECT
                schools.osm_id AS id,
                ST_AsGeoJSON(ST_Transform(schools.way, 4326))::json AS geom,
                schools.name AS school_title,
                station.dist AS station_distance,
                station.name AS station_name
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
            AND ST_Y(ST_Transform(way, 4326)) < 41.9774
        ';

        $schools = DB::select($schoolsQuery, ['school']);

        $schools = $this->asFeatureCollection($schools);

        return $schools;
    }

    public function stations()
    {
        $stationsQuery = '
            SELECT
                stations.id AS id,
                ST_AsGeoJSON(ST_Transform(stations.geo, 4326))::json AS geom,
                stations.name AS title,
                count(c.id) AS count
            FROM stations
            LEFT JOIN crimes c
            ON ST_DWithin(stations.geo::geography, c.geo::geography, 1000)
            GROUP BY stations.id
        ';

        $stations = DB::select($stationsQuery);

        $stations = $this->asFeatureCollection($stations);

        return $stations;
    }

    public function stationsForSchool(Request $request)
    {
        $schoolId = $request->input('schoolId');

        $stationsQuery = '
            WITH
                school AS (SELECT * FROM planet_osm_point WHERE osm_id = ?)
            SELECT
                stations.id AS id,
                stations.name AS title,
                ST_AsGeoJSON(ST_Transform(stations.geo, 4326))::json AS geom,
                count(crimes.id) AS count
            FROM school
            LEFT JOIN stations
            ON ST_DWithin(stations.geo::geography, ST_Transform(school.way, 4326)::geography, 1000)
            LEFT JOIN crimes
            ON ST_DWithin(stations.geo::geography, crimes.geo::geography, 1000)
            GROUP BY stations.id
        ';

        $stations = DB::select($stationsQuery, [$schoolId]);

        $stations = $this->asFeatureCollection($stations);

        return $stations;
    }

    public function crimesForStation(Request $request)
    {
        $stationId = $request->input('stationId');

        $crimesQuery = '
            SELECT crimes.id AS id, ST_AsGeoJSON(ST_Transform(crimes.geo, 4326))::json AS geom, crimes.type AS crime_type, crimes.description AS crime_description
            FROM crimes
            JOIN stations
            ON ST_DWithin(stations.geo::geography, crimes.geo::geography, 1000)
            WHERE stations.id = ?
        ';

        $crimes = DB::select($crimesQuery,[$stationId]);

        $crimes = $this->asFeatureCollection($crimes);

        return $crimes;
    }
}
