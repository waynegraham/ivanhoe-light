# Introduction
This tutorial will guide you through the process of creating a very basic
version of the Ivanhoe game using PHP, a MySQL database to store the moves, 
as well as some basic styling to make it look nice with some CSS. 

## Step 1: The Setup

Before starting, you should already have a database server set up
(provided with [MAMP][mamp]/[WAMP][wamp]), and the following details:

* Hostname (*localhost* unless you've done something crazy)
* Database name (you can create a new database on your MAMP/WAMP http
  page in `phpMyAdmin`)
* Username for database
* Password for database

### Create a Database

If you are in MAMP, when you start the services up, you will see a link
for **phpMyAdmin** on the top of the web browser page.

![MAMP header](images/header.png)

If you are on WAMP, you will see a link to **phpMyAdmin** on the main
page.

Click on the `phpMyAdmin` link on  your MAMP/WAMP installation. You
should see a field to create a new database, appropriately named
**Create new database**. Create a new database; I'm using **ivanhoe**,
but it can be whatever you want.

![phpMyAdmin new database](images/phpmyadmin-newdb.png)

### Create a Table

Now that there is a database to store our data, we need to create a 
table to store information about the moves. In your newly
created database, create a new table named `moves` by clicking on the
**SQL** tab:

![phpMyAdmin sql tab](images/phpmyadmin-sql.png)

Now you can paste the following and click the **Go** button.


```sql
CREATE TABLE `moves` (
  `id` INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `email` VARCHAR(60) NOT NULL,
  `move` TEXT NOT NULL,
  `ipaddress` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`)
);
```

This is a SQL statement that creates a new table (*moves*) in the
database (*ivanhoe*). It states that the table will have the following:

* an numeric `id` field that will automatically increment when a new record is added
* a required field named `name` that is a string, that can be no longer than 45 characters
* a required field named `email` that is a string that can be up to 60
  characters long
* a required field named `move` that contains long text
* a required field named `ipaddress` that is a string 
* a "primary key" that you can use in other relational tables to refer
  to this move based on the the `id` field

If everything went correctly, you should see a message in phpMyAdmin
saying everything executed successfully.

![phpMyAdmin Success](images/phpmyadmin-sql-success.png)

For the purposes of this tutorial, you can use the default **username** 
of `root` with a blank password (unless you've changed that). However,
**DO NOT** do this in a production environment!


## Application

Ok, so we're done with phpMyAdmin. Now we need to actually create an
application to interact with the database. Create a new directory in
your MAMP/WAMP `htdocs` directory named `ivanhoe`.

* MAMP: `cd /Applications/MAMP/htdocs && mkdir -p ivanhoe`
* WAMP: `cd C:/WAMP/htdocs && mkdir -p ivanhoe`

For this application, we need to set up a few initial files and
initialize a git repository. We'll have a main `index.php`
file, a `style.css` file, and a file to store our database connection
information (`db.php`). Assuming you are already in your MAMP/WAMP
`ivanhoe` directory, do the following:

```shell
$ touch {index.php, style.css, db.php}
$ git init
$ git add .
$ git commit -am "Initial files for Ivanhoe-light game"
```

### Database Connection

Right now if you look at your application in your browser
(http://localhost:8888/ivanhoe or http://localhost/ivanhoe), you will
only see a blank page. Your application doesn't know anything about the
database, or what to do with it, so let's fix that. The first thing
we'll do is create some PHP variables to store information about how to
connect to the MySQL database you just created.

 Edit your`db.php` file with the credentials to connect to your database:

```php
<?php
  $host = 'localhost';
  $username = 'root';
  $password = '';
  $database = 'ivanhoe'; // or the database you created in phpMyAdmin
```

If you refresh the web page now, you'll still now see anything. All
we've done here is create a file that we can store the information about
connecting to the database that we can use in the application.

# Step 2: Some Code

The `index.php` file will contain all of the code for the application,
but we need an initial HTML template to display. We'll use this as the
basis of our application for now:

```html
<!DOCTYPE html>
<html class="no-js">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Ivanhoe Light</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="styles.css"> 
</head>
<body>

  <nav>
    <ul>
      <li><a href="http://yoursite.com">[your name]</a></li>
      <li><a href="http://scholarslab.org">Scholars' Lab</a></li>
    </ul>
  </nav>

  <main role="main">
    <h1>Ivanhoe Light</h1>
  </main>

  <footer>
  
  </footer>
</body>
</html>
```

## Working With the Database

Before you can do anything with the database, you have to be able to
connect to it with PHP. We'll need to tell our script about this at the
top of the `index.php` file:

```php
<?php
include_once('db.php');

$self = $_SERVER['PHP_SELF']; // this file
$ipaddress = ("$_SERVER[REMOTE_ADDR]"); // the user's IP

$connection = mysqli_connect(
  $host,
  $username,
  $password,
  $database) or die(
    '<p class="error">Could not connect to database server.</p>'
  );
?>
```

The first line incluedes the database variables we set in `db.php`. The
next lines get the name of the file (`index.php`) and retrieves the
user's IP address, which we will use later on. While we could just as
easily set the variables for connecting to the database in `index.php`,
it is a best practice to keep the connection credentials separate from
the actual application code.

The `$connection` stores the actual connection to the MySQL server. If
it cannot make the connection, it will "hang up" and display an error
message.

## A Simple Form

We want to create a form that allows us to submit new moves. We can do
this by mixing HTML and our PHP variables. In the `main` HTML element,
we can create a form like this:

```php
<form action="<?php echo $self?>" method="post">
  <div class="field">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" placeholder="Your name..." required="true" />
  </div>

  <div class="field">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" placeholder="Your email address..." required="true" />
  </div>


  <div class="field">
    <label for="move">Move:</label>
    <textarea name="move" rows="10" cols="40" placeholder="Your move..." required="true"></textarea>
  </div>

  <input type="submit" value="Make Move" />
</form>

```

We now have a form that uses HTML 5 hints to ensure users fill out the
form. However, if you fill out the forma and click the **Make Move** button,
nothing happens. Let's fix that. Under the `$connection` variable, we
need to check if the page has information sent to it via an HTTP POST. 

```php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // check the parameters
  if(empty($_POST['name'] || empty($_POST['email']) || empty($_POST['move']))){
    echo '<p class="error">You did not fill out the form.</p>';
  } else {
    if($stmt = $connection->prepare("INSERT INTO moves(name, email, move, ipaddress) VALUES (?, ?, ?, ?)")) {
      $stmt->bind_param('ssss', $name, $email, $move, $ipaddress);

      // set the variables
      $name = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['name']));
      $email = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['email']));
      $move = htmlspecialchars(mysqli_real_escape_string($connection, $_POST['move']));

      $stmt->execute(); //execute the insert
      $stmt->close(); // close the connection for writing

      echo '<p class="success">Your move was recorded</p>';
    }
  }
}
```

The first line checks to see if the request is a POST (from a form),
then checks to ensure that the `name`, `email`, and `move` were passed.
If everything checks out, we create a prepared SQL statement to insert
the data (to avoid hackers deleting all the data). The statement is
built up using the variable passed in the form, but scrubbing the
strings of HTML code, and properly escaping any special characters
(including statements to delete data). If you allow users to add
JavaScript (or other languages), you open yourself up to many types of
<a href="http://en.wikipedia.org/wiki/Cross-site_scripting">XSS attacks</a>.

## Displaying Moves
We can now submit data and save it, but we need to do something to
actually view all the data that's there. We now need to write some more
code to retrieve all the moves in the database and display them. Under
the form processing section, add this:

```php
$sql = "SELECT * FROM moves ORDER BY `id` DESC LIMIT 10";
$moves = mysqli_query($connection, $sql);
```

Now to display them, before the `form` element, add this:

```php
<div id="moves">
  <ul>
  <?php while($row = mysqli_fetch_array($moves)): ?>
    <li>
      <div class="meta"><p><?php echo $row['name']; ?></p></div>
      <div class="move"><?php echo $row['move']; ?></div>
    </li>
  <?php endwhile; ?>
   </ul>
</div>
```

Sweet, now we've got the data set, but let's tie the post to a user's
gravatar image. For this we'll write a new function (at the top of the
file) to generate the gravatar image URL based on the move's email
address:

```php
function gravatar_url($email, $size=70)
{
  $hash = md5(strtolower(trim($email)));
  $url = "http://www.gravatar.com/avatar/$hash?s=$size";
  return $url;
}
```

Now to add this to the page, we'll integrate this in to the `moves` we
just displayed on the page. In the `meta` class we just added, add an
image with a URL set to the `gravatar_url` method:

```php
 <img src="<?php echo gravatar_url($row['email']); ?>" alt="Gravatar"/>
```

Now if you refresh the page, you should see gravatar images next to each
move.

## Style
Now we have access to data to do all kinds of different things in
JavaScript and in the text, however, it right now it doesn't look very
good. Let's fix that with a bit of styling in CSS.

The first thing we want to do is style some of the major HTML blocks
we've defined in the code, moving the content in to the center of the
page, giving it a background color, and a better font.

```css
html {
  background: #fffddd;
}

body {
  font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
  margin: 0 auto;
  width: 900px;
  color: #444;
}

a:link { color: #700; }
a:visited { color: #b00; }
a:hover { color: #b00; text-decoration: none; }
a:active { color: #500; }

nav {
  display: block;
  border: 1px solid #ccc;
  border-width: 1px 0;
}

nav a {
  text-decoration: none;
  color: #777;
}

nav ul {
  list-style: none;
  padding-left: 0;
}

nav li {
  display: inline;
}

nav li + li:before {
  content: " · ";
  color: #ccc;
}

```

It's starting to look like something! Now a few styles for the
notification messages we created. This will create red/green boxes with
rounded edges to display when a move has been made:

```css
p.error {
  color: #a94442;
  background-color: #f2dede;
  border: 1px solid #ebccd1;
  padding: 15px;
  margin-bottom: 15px;
  border-radius: 4px;
  box-sizing: border-box;
}

p.success {
  background-color: #dff0d8;
  color: #3c763d;
  border: 1px solid #d6e9c6;
  padding: 15px;
  margin-bottom: 20px;
  border-radius: 4px;
  box-sizing: border-box;
}
```

Awesome, now let's work on styling the moves. Basically what we want to
do here is put the image on the left, with the content of the move on
the right, styling the list items to not display the circle:

```css
#moves ul {
  margin-left: 0;
  margin-bottom: 15px;
}

#moves li {
  list-style: none;
  clear: both;
  padding-top: 30px;
}

#moves li:first-child {
  padding-top: 0;
}

.meta {
  width: 85px;
  min-height: 110px;
  font-weight: bold;
  float: left;<D-r>
}

.meta img {
  padding: 5px;
  background-color: #313d60;
}

.meta p {
  padding-top: 5px;
  float: left;
}

.move {
  width: 700px;
  margin-left: 110px;
}
```

This styles the moves list to remove the circules, then styles the
`meta` image and name, then sets the `move` content to `700px` to fill
out the page.

But that form is still a little gross looking. Let's fix that by making
the text input larger, and adding some visual queues for which field the
user is in. We'll use some browser-specfici extensions, as well as
advanced selectors. 

```css
form {
  clear: both;
}

.field {
  margin-bottom: 15px;
}

label {
  margin-bottom: 5px;
  font-weight: bold;
}

input:not([type='submit']) {
  display: block;
  width: 100%;
  height: 34px;
  padding: 6px 12px;
  margin: 6px 0 0 0;
  font-size: 14px;
  line-height: 1.428571429;
  color: #555;
  vertical-align: middle;
  border: 1px solid #ccc;
  border-radius: 4px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
  box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
  -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
  transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
}

input[type='submit'] {
  padding: 6px 12px;
  margin-bottom: 0;
  font-size: 14px;
  font-weight: normal;
  line-height: 1.428571429;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  border-radius: 4px;
  -webkit-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  -o-user-select: none;
  color: #333;
  background-color: #fff;
  border-color: #adadad;
  text-decoration: none;
}

textarea {
  display: block;
  width: 100%;
  height: auto;
  padding: 6px 12px;
  font-size: 14px;
  line-height: 1.428571429;
  color: #555;
  vertical-align: middle;
  background-color: #fff;
  border: 1px solid #ccc;
  border-radius: 4px;
  -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
  box-shadow: inset 0 1px 1px rgba(0,0,0,0.075);
  -webkit-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
  transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
}
```

Looking good, but let's give the footer some love:

```css
footer {
  padding: 30px 0;
  display: block;
}
```

Hey, it looks like something now!

## Summary

So this very brief tutorial worked through developing a simple PHP
application that stores information in a database, and styling the
results.

# Going Further

* Make this design better with your own CSS
* Add a WSYIWYG editor to the **Moves** field
* Change the application to allow moves on a move
  * What needs to change in the design of the page?
  * What needs to change in the database?
  * How do you tell your application which form you're talking about?
* What are other features you could add?
* How could you use the same data to produce a different visualization
  of the data? 



[mamp]: http://www.mamp.info/en/index.html
[wamp]: http://www.wampserver.com/en/
