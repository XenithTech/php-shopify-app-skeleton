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

The main purpose of writing this was the lack of something quick this simple and ready to go. Most tutorials are in Ruby using the `shopify_app` gem. I tried this and had nothing but headaches getting it to work. After having to download multiple pieces of software, plugins, frameworks, etc I had an app that didn't run but still somehow managed to take up around 96MB.

So I decided to go back to the basics. This app will handle the Oauth handshake sent from Shopify as well as the added security of handling the app page view itself. This was also something I found missing from the tutorials. They handle the handshake to verify the call is coming from Shopify and then redirects to the page/location of where your app is going to run. The issue here is that at this point a hacker can simply go directly to your app location and skip around the handshake. This is asking for trouble.

This app will also be setup to be scaled in a way the would allow you to make it an external app (not embedded) and be able to have one client handle/work on multiple stores so that it can more easily be used by an agency. It also includes a column in `client_stores` named `active` that can be used to deactivate a store on the back end.
