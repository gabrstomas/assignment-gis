# General course assignment

Build a map-based application, which lets the user see geo-based data on a map and filter/search through it in a meaningfull way. Specify the details and build it in your language of choice. The application should have 3 components:

1. Custom-styled background map, ideally built with [mapbox](http://mapbox.com). Hard-core mode: you can also serve the map tiles yourself using [mapnik](http://mapnik.org/) or similar tool.
2. Local server with [PostGIS](http://postgis.net/) and an API layer that exposes data in a [geojson format](http://geojson.org/).
3. The user-facing application (web, android, ios, your choice..) which calls the API and lets the user see and navigate in the map and shows the geodata. You can (and should) use existing components, such as the Mapbox SDK, or [Leaflet](http://leafletjs.com/).

## Zdroje dát

- [Open Street Maps](https://www.openstreetmap.org/)
- [Chicago Divvy Bicycle Sharing Data](www.kaggle.com/yingwurenjian/chicago-divvy-bicycle-sharing-data)
- [Crimes in Chicago](www.kaggle.com/currie32/crimes-in-chicago)

## Safe Schools Chicago

**Opis aplikácie**: Aplikácia je zameraná na analýzu mapových dát z oblasti Chicaga. Určená je primárne pre rodičov, ktorí hľadajú vhodnú školu pre svoje deti, pričom ich kritériami pri výbere vhodnej školy je jej vzdialenosť od bydliska, dostupnosť prostredníctvom bike-sharingovej služby a bezpečnosť.

Používateľ má možnosť zobraziť na mape školy v oblasti Chicaga, ktoré sú farebne rozlíšené na základe toho, či sa v ich blízkosti nachádza stanica bike-sharingu. Používateľ má možnosť nastaviť preferovanú vzdialenosť, ktorá určuje, či je škola považovaná za dobre dostupnú (predvolená hodnota akceptovanej vzdialenosti od bike-sharingovej stanice je 300m). Okrem tohto majú používatelia možnosť využiť filter zobrazenia a zobraziť na mape len také školy, ktoré sú dobre dostupné.

Druhým prípadom použitia je možnosť zobrazenia bike-sharingových staníc, ktoré sú farebne rozlíšené na základe toho, koľko kriminálnych činov smerovaných na deti sa v roku 2016 udialo v ich blízkosti. Hranicu pre kriminálne činy je taktiež možné nastaviť manuálne (predvolená hodnota je 1000m).

Ďalším prípadom použitia je vyhľadanie vhodných škôl na základe zadaných kritérií. Používateľ v prvom kroku zvolí na mape miesto, ktoré reprezentuje jeho bydlisko. Následne určí, aká je maximálna vzdialenosť, ktorú by jeho deti mali prejsť medzi bike-sharingovou stanicou a školou. V ďalšom kroku nastaví používateľ akceptovateľnú mieru kriminality v oblasti bike-sharingových staníc, ktoré je ochotný akceptovať. Mieru kriminality nastaví prostredníctvom polomeru kruhu okolo bike-sharingovej stanice a maximálneho počtu kriminálnych činov pre tieto okruhy. Aplikácia následne zobrazí na mape také školy, ktoré vyhovujú zadaným podmienkam a teda:

1. V blízkosti školy sa nachádza aspoň jedna akceptovateľná stanica (manuálne nastavená maximálne vzdialenosť od školy).
2. Stanica je akceptovateľná vtedy, ak spĺňa kritéria bezpečnosti.
3. Kritériom bezpečnosti je maximálny počet kriminálnych činov v blízkosti stanice (manuálne nastavená hranica blízkosti, manuálne nastavený maximálny počet kriminálnych činov).

Okrem škôl sa na mape zobrazia aj všetky akceptovateľné stanice v blízkosti zobrazených škôl, pričom veľkosť ich zobrazenia je závislá od počtu kriminálnych činov v ich blízkosti.

(optional) Záverečným prípadom použitia je navrhnutie vhodných staníc pre presun z bodu A do bodu B. V prvom kroku používateľ zvolí na mape štartové a cieľové miesto. Následne zadá maximálnu vzdialenosť presunu medzi týmito miestami a stanicami. Aplikácia mu na základe zadaných kritérií navrhne najbezpečnejšiu kombináciu staníc, ktoré spĺňajú jeho podmienky. Kritériá bezpečnosti používateľ nastavuje rovnako, ako v predchádzajúcom prípade použitia.

**Použité technológie**: 

* BE: PHP (Laravel)
* FE: HTML+CSS+JS (jQuery)
