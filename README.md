# Docker
Docker install: [get-docker](https://docs.docker.com/get-docker/)

## Installation
```
$ git clone https://github.com/mikhailibproject/yandex-taxi-price.git   
$ cd yandex.taxi.price
$ make start
or  
$ docker compose up 
```

## Set up parameters app/.env
Copy app/.env.example to app/.env

Configure the required parameters
> IMPORTANT! exactly "app/.env", not ".env"
```
# Yandex.Taxi price service URL
YANDEX_TAXI_URI_API="https://taxi-routeinfo.taxi.yandex.net/taxi_info"

# Client ID API Key. The ID and Key must be requested from the Yandex support service 
YANDEX_CLIENT_ID="xxxxxxxx"
YANDEX_API_KEY="Some-String"

# Yandex.Weather service URL
YANDEX_WEATHER_URI_API="https://api.weather.yandex.ru/v2/forecast"

# API Key fom Yandex developer account 
YANDEX_WEATHER_API_KEY="xxx-xxx-xxx"

# Coordinate for request Weather forecast
WEATHER_COORDINATE_LONGITUDE=37.380804
WEATHER_COORDINATE_LATITUDE=55.811216

# Defaulyt path to SQLite database
PATH_TO_SQLITE_DB="storage/db/route_price.sqlite"

# Google Spreadsheets 
GOOGLE_APP_CREDENTIALS="credentials.json"

# Google Sppreadsheet identificator 
# https://docs.google.com/spreadsheets/d/_____this is an identifier______/edit#gid=0
GOOGLE_SPREADSHEET_ID="_____this is an identifier______"

# Range for reading route info 
# First line must contain HEADER
# id	name	description	            from_lat	from_long	to_lat	    to_long	    request_count	error_message
# Subsequent lines are data
# 1	    Route1	Route from home to work	55.8111280	37.3806690	55.7535640	37.5982050	1	
GOOGLE_SPREADSHEET_ROUTE_RANGE="Routes!A:I"
# Last results data range
GOOGLE_SPREADSHEET_RESULT_RANGE="Current_price!A1"
```

Loading data into Google Spreadsheets is impossible without obtaining
'credentials.json' and placing it in the 'storage/google-docs' directory.
To get it, you need to enable the Google Sheets API and create an application.
More details here - [developers.google.com/sheets/api/quickstart/ ](https://developers.google.com/sheets/api/quickstart/php#step_3_set_up_the_sample)

Yandex Taxi API Key and ID you can get in the service [Yandex Taxi API](https://yandex.ru/dev/taxi/taxiapi/)

Yandex Weather API Key for testing purposes [Yandex Developer](https://developer.tech.yandex.ru/services/)


## Running from the console:

in container:

At the beginning it is necessary to create a database
> make init-sql

```
Get help
# make help 

Create or clear SQlite DB
# make init-sql 
Request data from Yandex
# make get-data
Save report data
# make report
```
## Example
```
# make get-data
php cli yandextaxi:get-data

Request price and weather data from Yandex.Taxi and Yandex.Weather
==================================================================

 Loading route data from Google Sheet...
 Requesting the current weather and taxi prices from Yandex services...
 Saving data...
 Done...

```
## Scheduled launch:
in container:
```
Add line to /etc/crontab
# Example of job definition:
# .---------------- minute (0 - 59)
# |  .------------- hour (0 - 23)
# |  |  .---------- day of month (1 - 31)
# |  |  |  .------- month (1 - 12) OR jan,feb,mar,apr ...
# |  |  |  |  .---- day of week (0 - 6) (Sunday=0 or 7) OR sun,mon,tue,wed,thu,fri,sat
# |  |  |  |  |
# *  *  *  *  * user-name command to be executed

*/5  *  *  *  *   root    cd /var/app && php /var/app/cli yandextaxi:get-data
```
## Composer Commands:
```
$ make composer c="update"
$ make composer c="dump-autoload"
```
## Thanks 
to [skodnik](https://github.com/skodnik) for the initial version [yandex.taxi-pub](https://github.com/skodnik/yandex.taxi-pub)