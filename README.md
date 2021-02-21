# Speech 2 Text API

The project contains an API endpoint that will consume Google's own Speech-to-Text API, and return in the response the speech
contained on an audio file transcribed into English language text.

## Getting Started
---

First thing to note, this API will not work out of the box and **you will require** a Google application credentials configuration file
for use with this API. Once you have your copy, setup is easy and detailed below. To obtain the credentials file use the link below:

* [Google Speech-to-Text](https://cloud.google.com/speech-to-text) - Their API documentation and getting started

Once you have your Google credentials configuration JSON file your ready to get going with this API.

---
## Built With & Prerequisites

* [PHP 7.4](https://www.php.net/downloads.php#v7.4.15) - The version of PHP used
* [Laravel 8.12](https://laravel.com/) - PHP framework of choice
* [MySQL 8.0.20](https://www.mysql.com/downloads/) - Version of MySQL
* [Apache 2.4.43](https://www.apache.org/) - The web server software used

Project was developed on a `Windows OS x86_64` environment.

This API currently only works with FLAC audio files and these will require you to base 64 encode a FLAC file before posting it to the API.

A couple of handy links should you require help creating your own file ready to use with this API:

* [Zamzar.com](https://www.zamzar.com/convert/m4a-to-flac/) - Convert M4A files to FLAC
* [Base64.Guru](https://base64.guru/converter/encode/file) - Convert file to Base64

Although I've listed creating your own encoded files in prerequisites there are a number of test files contained within the project that you can use to ensure the API is setup, and working prior to creating your own files. These files can be located in the directory `tests\files\audio\base64encodedflacfiles`. **NOTE** there are two files named `test_file*` inside of the `test\files\` dir removing these from this location will break the automated tests, which you can run and are referenced below. The other two files are yours to remove and upload as test files before making your own.

## Installing & Setup
---
A step by step guide to get you up and running with this API

1. Clone down this repo to your local machine taking note taking note of the Built With section above as to to the technologies and software used to create
this API on your local machine.

```
cd folder/to/clone-into/
git clone https://github.com/ryan-farrell/speech2text-api.git
```

2. Create a local database with the software you would do normally name the `speech2text-api` and setup your db user to have access to this new db. This is the db username you will use in point 3.

3. Copy and paste the `.env.example` file in the root folder of the directory and rename to just `.env`.

4. In the new `.env` file add your db username to line 14 and db password associated with this user to line 15 instead of the place holders in the file als exampled below.
If your DB_USER has no password then delete the place holder and leave blank.

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=speech2text-api
DB_USERNAME={your_db_username}
DB_PASSWORD={your_password}
```
5. Place a copy of your `google-credentials.json` (refer to *Getting Started* section above if you haven't got this file already) in the `\setup-files` directory found in the root dir of this project. Make sure to rename the file to `setup.json`. 

6. In the command line make sure your in the root directory of this api project. Run the command `npm run api-setup` which will go thru a series of steps to get the package updated with its dependencies, migrate two tables into the database, create the API documentation and boot up a local server at http://127.0.0.1:8000 (the default URL done by Laravel).

7. You should be able to connect to the API [click this link](http://127.0.0.1:8000/api/v1/audiofiles) to get a simple message confirming connection to the API. *NOTE* If you serving up your localhost from another **port other than 8000** (the Laravel default) you will need to amend the port in the example here and anywhere else it is shown, including the documentation. 

8. Lastly to work with this project you can now use the provided documentation which can be located in dir `\apidocs\index.html`. Open up the `index.html` file in your browser to view. The docs should walk you through how to use the possible endpoints and expected outcomes with examples for reference. In the docs I reference using the `curl` command to send HTTP requests to the API, but also included in the project is a postman collection you can import into postman should you prefer to use `Speech2Text API.postman_collection.json`.

## Running the tests

To run the tests in this project you have 2 options. In the command line use `npm run tests` to run the tests and see the output. Alternatively use `npm run coverage` to run the tests and produce the coverage report. Report will be located in `\tests\coverage\` open up `dashboard.html` in your browser to view the report.

There is currently 6 tests and 45 assertions passing with 2 additional tests marked as risky that still require work. I've concentrated on the methods and controllers I've introduced as part of this project.

## Limitations

Currently unknown side effects my occur if you try to upload a file which has not been base64 encoded and was not previously a FLAC file. Also if the hertz_rate of the original recording was not at 44,100 this may also fail the API. The encode file include referenced above will work to confirm testing. 

Next steps to improve the API:

- [ ] Add the test audio files 
- [ ] Complete the tests on the two marked risky currently.
- [ ] Validate incoming file for Base64 encoding
- [ ] Include a package to check the uploaded file and set some parameters based on the file uploaded  
- [ ] Once I can establish the length of the audio file include the additional Google logic to deal with larger audio files asynchronously.
   
## Authors

* **Ryan Farrell** - *Initial work*

## License
This project is licensed under the [MIT license](https://opensource.org/licenses/MIT).
