# php-shopify-app-skeleton
A bare-bones Shopify app written in plain PHP with no framework

<p>This is a very basic stripped down Shopify app that was designed to be as plug and play as possible. It does require a database setup with two tables in it and uses MySQL to work with the database. The table structures are as follows:</p>

<table>
  <th colspan="6">clients</th>
  <tr>
    <th>Column Name</th>
    <th>Type</th>
    <th>NULL</th>
    <th>Key</th>	
    <th>Default</th>
    <th>Extra</th>
  </tr>
  <tr>
    <td>client_id</td>
    <td>int(11)</td>
    <td>NO</td>
    <td>PRI</td>	
    <td>NULL</td>
    <td>auto_increment</td>
  </tr>
  <tr>
    <td>client_name</td>
    <td>varchar(255)</td>
    <td>NO</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
</table>

<table>
  <th colspan="6">client_stores</th>
  <tr>
    <th>Column Name</th>
    <th>Type</th>
    <th>NULL</th>
    <th>Key</th>	
    <th>Default</th>
    <th>Extra</th>
  </tr>
  <tr>
    <td>store_id</td>
    <td>int(11)</td>
    <td>NO</td>
    <td>PRI</td>	
    <td>NULL</td>
    <td>auto_increment</td>
  </tr>
  <tr>
    <td>client_id</td>
    <td>int(11)</td>
    <td>NO</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
  <tr>
    <td>store_name</td>
    <td>varchar(255)</td>
    <td>NO</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
  <tr>
    <td>token</td>
    <td>varchar(255)</td>
    <td>NO</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
  <tr>
    <td>hmac</td>
    <td>varchar(255)</td>
    <td>YES</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
  <tr>
    <td>nonce</td>
    <td>varchar(255)</td>
    <td>YES</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
  <tr>
    <td>url</td>
    <td>varchar(255)</td>
    <td>NO</td>
    <td></td>	
    <td>NULL</td>
    <td></td>
  </tr>
  <tr>
    <td>last_activity</td>
    <td>datetime</td>
    <td>NO</td>
    <td></td>	
    <td>CURRENT_TIMESTAMP</td>
    <td></td>
  </tr>
  <tr>
    <td>active</td>
    <td>tinyint(4)</td>
    <td>NO</td>
    <td></td>	
    <td>1</td>
    <td></td>
  </tr>
</table>

The main purpose of writing this was the lack of something quick, simple, and ready to go. Most tutorials are in Ruby using the `shopify_app` gem. I tried this and had nothing but headaches getting it to work. After having to download multiple pieces of software, plugins, frameworks, etc I had an app that didn't run but still somehow managed to take up around 96MB.

So I decided to go back to the basics. This app will handle the Oauth handshake sent from Shopify as well as the added security of handling the app page view itself. This was also something I found missing from the tutorials. They handle the handshake to verify the call is coming from Shopify and then redirects to the page/location of where your app is going to run. The issue here is that at this point a hacker can simply go directly to your app location and skip around the handshake. This is asking for trouble.

This app will also be setup to be scaled in a way the would allow you to make it an external app (not embedded) and be able to have one client handle/work on multiple stores so that it can more easily be used by an agency. It also includes a column in `client_stores` named `active` that can be used to deactivate a store on the back end.


## Setup

### Clone the repo and clean up the directory
```
git clone https://github.com/XenithTech/php-shopify-app-skeleton.git my_app
cd my_app
rm -rf .git
rm README.md
rm LICENSE
```

### Create the app in Shopify
1. In our partners account (go ahead and create one if you don't have it), under `Apps` click on `Create app` in the top right.
2. Choose public (this is always a better option, in my opinion, because it has greater security measures and if you decide to make the app for another store, you only need the one instance)
3. Name your app
4. Set the `App URL` to point to `oauth.php`. This will be where your app is hosted. (ie: `https://yourapplication.com/oauth.php`)
5. Set the `Allowed redirection URL(s)` to include `postoauth.php` and `index.php`. It should look something like this:
  ```
  https://yourapplication.com/postoauth.php
  https://yourapplication.com/index.php
  ```
6. Click `Create app` in the top right

### Add your app Key and Secret Key to `config.php`
The next screen after clicking `Create app` should display these keys for you. Inside `config.php` set `$k` to the API key, and then set `$s` to the API secret key

### Set your app permissions in `config.php`
Modify the `$permissions` array to contain all permissions your app will need

### Connect your database in `config.php`
Create a database containing two tables with the given structure above. Be sure to create/add a user to this database. The permissions this user needs to have are at minimum `SELECT`, `UPDATE` and `INSERT`
Inside `config.php` do the following:

1. Set `$sn` to your server name. If your database is on the same server this app is hosted it will likely need to be set to `localhost`. Other wise if it is hosted elsewhere it should be set to the IP address of the server hosting the database.
2. Set `$un` to the database's user account name
3. Set `$pw` to the user account's password
4. Set `$dn` to the name of the database

### Upload your app to your server
Of course, the final step here is to upload all of the files for the app to your server. Once that is done your app should be ready to be installed on a development store.

## Build out your app your way
`index.php` is the home of the actual app. If you are wanting to serve the functionality of your app through some means other than PHP (ie: Node.js, React, etc) You simply need to change the `$redirection_url` to the location of your app. Keep in mind that how ever you host it there is some added security in the `index.php` file that will need to be handled appropriately.
