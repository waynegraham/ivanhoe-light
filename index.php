<?php
include_once('db.php');

function gravatar_url($email, $size=70)
{
  $hash = md5(strtolower(trim($email)));
  $url = "http://www.gravatar.com/avatar/$hash?s=$size";
  return $url;
}

$self = $_SERVER['PHP_SELF']; // this file
$ipaddress = ("$_SERVER[REMOTE_ADDR]"); // the user's IP

$connection = mysqli_connect(
  $host,
  $username,
  $password,
  $database) or die(
    '<p class="error">Could not connect to database server.</p>'
  );

if($_SERVER['REQUEST_METHOD'] == 'POST') {
  // check the parameters
  if(empty($_POST['name']) || empty($_POST['email']) || empty($_POST['move'])){
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

$sql = "SELECT * FROM moves ORDER BY `id` DESC LIMIT 10";
$moves = mysqli_query($connection, $sql);

$connection->close();

?>
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
  <header role="banner">
    <nav>
      <ul>
        <li><a href="http://yoursite.com">[your name]</a></li>
        <li><a href="http://scholarslab.org">Scholars' Lab</a></li>
      </ul>
    </nav>
  </header>

  <main role="main" id="main">
    <h1>Ivanhoe Light</h1>

    <div id="moves">
      <ul>
      <?php while($row = mysqli_fetch_array($moves)): ?>
        <li>
          <div class="meta">
            <img src="<?php echo gravatar_url($row['email']); ?>" alt="Gravatar"/>
            <p><?php echo $row['name']; ?></p>
          </div>
          <div class="move"><?php echo $row['move']; ?></div>
        </li>
      <?php endwhile; ?>
       </ul>
    </div>

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
  </main>

  <footer>
  &nbsp;  some footer stuff
</footer>
</body>
</html>
