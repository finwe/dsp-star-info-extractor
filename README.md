# Star systems info extractor for Dyson Sphere Program

This tool will automagically convert a table with information about a celestial body to a plain text table
in a Google Document.

![][1]

## Common prerequisites


* AWS account and credentials for use of AWS Textract
* Google Developer app with Google Sheets API enabled and OAuth configured
    * Redirect URL of the app should point to the `GET /access` endpoint of the configured server
* Dedicated Google Spreadsheet for writing

## Docker prerequisites

* Docker (optionally with docker-compose) installed and working

## Docker setup

* Download the application code and build the container with `docker build -t dsp-textract .`
* Create a persistent local directory to be mounted to the container. 
    * For docker-compose, this directory must be named `config.local/`, this name is recommended
* Copy `config/config.local.neon.template` from the source code to `config.local.neon` in the local config directory
* Point `sheet_client_config` and `sheet_token_config` parameters in `config.local.neon` to files in `config.local/` directory
* Set up remaining parameters in `config.local.neon` file
* Start the container
    * Either with `docker-compose`
         * The application will be exposed on port `8080`
    * Or with `docker run` where you point your local config directory to `config.local` in the container.
         * The server in the container runs on port `8080`, point wherever you like

## Manual prerequisites

* A server capable of serving PHP pages, PHP 8, composer

## Manual setup

* Install composer dependencies
* Create `log`, `temp/cache` directories
* Set up the server to point to the `public` directory of the app
* Copy `config/config.local.neon.template` to `config/config.local.neon` and fill with information
    * `sheet_client_config` is a path to Google app config json. 
        * Example value can be `%root_dir%/config/credentials.json`
    * Other options should be self-explanatory
    * `%root_dir%` can be used to point to the root directory of the app
  
## Using the app

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

## Debugging

* The app is configured for production mode
* To switch the app to a development mode, create a `.dev` file in the local config directory
* **Warning**: development mode may leak source code
  and credentials. Only local use is encouraged. Use lvh.me domain which points to 127.0.0.1
  for oauth redirect url and/or the whole app.

[1]: https://github.com/finwe/dsp-star-info-extractor/raw/master/preview.png
[2]: https://docs.google.com/spreadsheets/d/1M8BVZWMAZSM5ajXi2zpb__fGQTRNrOm1-gMqPYhnc8U/edit?usp=sharing
[3]: https://cloudshot.com
[4]: https://github.com/finwe/cloudshot-http-api-upload-plugin
