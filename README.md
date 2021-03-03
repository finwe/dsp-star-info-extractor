# Star systems info extractor for Dyson Sphere Program

This tool will automagically convert a table with information about a celestial body to a plain text table
in a Google Document.

![][1]

## Prerequisites

* A server capable of serving PHP pages, PHP 8, composer
* AWS account and credentials for use of AWS Textract
* Google Developer app with Google Sheets API enabled and OAuth configured
    * Redirect URL of the app should point to the `GET /access` endpoint of the configured server
* Dedicated Google Spreadsheet for writing

## Setup

* Install composer dependencies
* Create `log`, `temp/cache` directories
* Set up the server to point to the `public` directory of the app
* Copy `config/config.local.neon.template` to `config/config.local.neon` and fill with information
    * `sheet_client_config` is a path to Google app config json. 
        * Example value can be `%root_dir%/config/credentials.json`
    * Other options should be self-explanatory
    * `%root_dir%` can be used to point to the root directory of the app
* Set up Google Docs access token by visiting the `GET /access` endpoint on the configured server
* Send your screenshots to the `POST /document` endpoint of the configured server
    * The endpoint expects a single POST field of name `dataurl` with Base64-encoded data-url of the image

## Screenshots

* It is necessary to provide just a cropped screenshot of the details table, otherwise the result is uncertain
* Very useful tool for this can be [CloudShot][3] along with my [Generic API Upload Plugin][4] which send exactly the data the app expects

## Results

* Each solar system will have a separate sheet in the document
* Each star and planet of the system will take two columns with two empty columns on the right for notes
   * The order of bodies on the sheet is Star/Giant/Black hole and then orbiting bodies I-V 
* On subsequent screenshots of the same object table, the data of the object will be overwritten

[Example Google Spreadsheet][2] from my current playthrough.

## Disclaimer

* The app is configured in development mode and not ready for production as it may leak source code 
  and credentials. Only local use is encouraged. Use lvh.me domain which points to 127.0.0.1
  for oauth redirect url and/or the whole app.

* Docker image of the app is possible in the future depending on interest

[1]: https://github.com/finwe/dsp-star-info-extractor/raw/master/preview.png
[2]: https://docs.google.com/spreadsheets/d/1M8BVZWMAZSM5ajXi2zpb__fGQTRNrOm1-gMqPYhnc8U/edit?usp=sharing
[3]: https://cloudshot.com
[4]: https://github.com/finwe/cloudshot-http-api-upload-plugin
