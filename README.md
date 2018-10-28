# General course assignment

Build a map-based application, which lets the user see geo-based data on a map and filter/search through it in a meaningfull way. Specify the details and build it in your language of choice. The application should have 3 components:

1. Custom-styled background map, ideally built with [mapbox](http://mapbox.com). Hard-core mode: you can also serve the map tiles yourself using [mapnik](http://mapnik.org/) or similar tool.
2. Local server with [PostGIS](http://postgis.net/) and an API layer that exposes data in a [geojson format](http://geojson.org/).
3. The user-facing application (web, android, ios, your choice..) which calls the API and lets the user see and navigate in the map and shows the geodata. You can (and should) use existing components, such as the Mapbox SDK, or [Leaflet](http://leafletjs.com/).

## Example projects

- Showing nearby landmarks as colored circles, each type of landmark has different circle color and the more interesting the landmark is, the bigger the circle. Landmarks are sorted in a sidebar by distance to the user. It is possible to filter only certain landmark types (e.g., castles).

- Showing bicykle roads on a map. The roads are color-coded based on the road difficulty. The user can see various lists which help her choose an appropriate road, e.g. roads that cross a river, roads that are nearby lakes, roads that pass through multiple countries, etc.

## Data sources

- [Open Street Maps](https://www.openstreetmap.org/)

## My project

Fill in (either in English, or in Slovak):

**Application description**: Aplikacia sa venuje analyze mapovych dat z oblasti Chicaga. Zamerana bude na data z oblasti bike sharingu v spojeni s datami o kriminalite. Aplikacia bude zobrazovat informacie o kvantite kriminalnych cinov v jednotlivych oblastiach prostrednictvom vhodnej vizualizacie (predbezne heatmapa). Okrem toho bude aplikacia zobrazovat jednotlive stanice pre bicykle spolu s informaciou o tom, ako je dana stanica bezpecna (v zavislosti od kriminalnych cinov v jej okoli), napriklad rozlisenim urovne bezpecnosti roznymi farbami. Komplexnejsie vyuzitie najde aplikacia pri odporucani bezpecnej trasy z bodu A do bodu B. Pouzivatel si zvoli startove a cielove miesto, zada maximalnu vzdialenost, ktoru je ochotny prejst peso (vzdialenost medzi startom a startovacou stanicou / cielom a cielovou stanicou) a riziko, ktore je ochotny podstupit. Aplikacia mu navrhne startovaciu a cielovu stanicu tak, aby boli splnene jeho poziadavky na dlzku presunu peso a takisto aj jeho pozidavky na bezpecnost jednotlivych stanic.

**Data source**: Chicago Divvy Bicycle Sharing Data (www.kaggle.com/yingwurenjian/chicago-divvy-bicycle-sharing-data), Crimes in Chicago (www.kaggle.com/currie32/crimes-in-chicago)

**Technologies used**: BE: PHP (Laravel/Lumen), FE: HTML+CSS+JS (jQuery/Angular 2+)
