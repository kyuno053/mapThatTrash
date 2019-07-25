# README

## Web app
 * backend: php
 * frontend: html/css/javascript(with [Leaflet library](https://leafletjs.com/))

## How it works

need two things :
 * pictures with gps metadata
 * a json file which contain all the detected trash per pictures called processed.json

## file tree

	|-additionnal
	|	|-export	// path to exported data from the web app
	|	|-image_to_process	// path to put all your sub folders for the pictures
	|	|-import // path to put the processed.json file
	|
	|-assets
	|	|-css
	|	|-image
	|
	|-index.php
	|-README.txt

## JSON Structures

### processed.json:
```JSON
	[
		{
			"path": "20190716_132412.jpg",
			"NB_trash": "1"
		},
		{
			"path": "20190716_133255.jpg",
			"NB_trash": "1"
		}
	]
```
	
### data.json:
```JSON
	{
		"additional\/image_to_process\/neighborhood6\/IMG_20190717_140421.jpg": {
		"neighborhood": "neighborhood6",
		"latDMS": {
			"Degrees_D": 52,
			"Minutes_M": 21,
			"Seconds_S": 52.1099
		},
		"latDD": 52.364474972222226,
		"longDMS": {
			"Degrees_D": 4,
			"Minutes_M": 54,
			"Seconds_S": 54.9359
		},
		"longDD": 4.9152599722222226,
		"nbTrash": 2
		}
	}
  ```
