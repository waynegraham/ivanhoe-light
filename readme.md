# Introduction
This tutorial will guide you through the process of creating a basic
Ivanhoe game with PHP, using a MySQL database to store the moves, and
make it look nice with some CSS. 

## Step 1: The Setup
Before starting, you should already have a database server set up
(providwith MAMP/WAMP), and the following details:

* Hostname (*localhost* unless you've done something crazy)
* Database name (you can create a new database on your MAMP/WAMP http
  page in `phpMyAdmin`)
* Username for database
* Password for database

### Create a Database

Click on the `phpMyAdmin` link on  your MAMP/WAMP installation. You
should see a field to create a new database, appropriately named
**Create new database**. The username, by default, will be `root` with a
blank password (unless you've changed that).

### Create a Table

We need a table to store information about the moves. In your newly
created database, create a new table named `moves` by clicking on the
**SQL** tab and pasting in the following:

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

After you click on the **Go**, you should see a message along these
lines:

  Your SQL query has been executed successfully

## Application

We need a few files for this application. We'll have a main `index.php`
file, a `style.css` file, and a file to store our database connection
information (`db.php`). 

```shell
$ mkdir ~/projects/ivanhoe-light
$ cd ~/projects/ivanhoe-light
$ touch {index.php, style.css, db.php}
$ git init
$ git add .
$ git commit -am "Initial files for Ivanhoe-light game"
```

### Database Connection

We need to tell our application how to connect to our database. Edit the
`db.php` file with your credentials to connect to your database:

```php
<?php
  $host = 'localhost';
  $username = 'root';
  $password = '';
  $database = 'ivanhoe'; // the database you created in phpMyAdmin
```

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
