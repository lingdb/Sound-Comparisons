# flask-oauth project

Starter Code for admin section of flask-based website. This project also contains a test database, created from CSV files of lexical cognacy data for Indo-European.

## Initial things to set up the project environment.

This is a test environment flask site where the main app is a simple server front-end with a simple database back-end. You will need to set up the database before proceeding.

### Setting up the database:

1. To initialize and populate the test database, in folder `\db` run: **python db_setup.py**
2. The main app uses the db_access.py module to access the database.

## Setting up the OAuth environment working.

### Create a new Google project:

1. Go to: **https://console.developers.google.com/project/**.
2. On leftmost menu of the landing page, go to: **APIs & auth > Credentials**.
3. Follow the instructions to create a new project, record **Client ID** and **Client secret**.
4. When configuring the Web application, be sure to set the fields for "Authorized JavaScript origins" and "Authorized redirect URIs". [NB. the redirects URIs are important for determining what happens after users have logged in or logged out.] 
5. Download a JSON file of the **Client secret**; probably best to rename the file to something like `client_secret.json`.

### In **login.html**:

- paste the **Client ID ** in the `data-clientid` inside the *signInButton* div.

### Configuring *signInButton* in **login.html** template -- some explanation of parameters: 

- *data-scope*: Specifies google resources to access [e.g. `openid` requests user's name, profile picture and email address]
- *data-clientid*: The Client ID provided when registering web application for Google plus.
- *data-redirecturi*: By setting this to `postmessage`, we can enable the one time use code flow.
- *data-accesstype*: Setting this to `offline` enables requests to Google API server, evenif user not logged in.
- *data-cookiepolicy*: Determining scope of URIs that can access the cookie, e.g. `single_host_origin` for site with single domain and no host domains.
- *data-callback*: Specifies callback function, e.g. setting this to `signInCallback`, then if user grants access to profile, this callback method is called and it is given a one time use code plus access token.
- *data-approvalprompt*: Setting this to `force` means user must login each time they visit the login page [i.e. no check to see if they're already logged in]. This is useful when debugging, but may be annoying in production, so it might best to disable it for the latter. 

## Using the oauth app.

### Running your server:

* To run the flask server, at topmost level of project run: **python project.py**
* In your browser visit **http://localhost:5000** to view the project app. 
* You should be able to *view* database items without logging in, but only users who are logged should be able to *add*, *edit*, and *delete* database entries. 

