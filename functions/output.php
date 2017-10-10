<?php
require_once('./all_functions.php');

//function for connecting to database
function connect() {
  $db= new mysqli('localhost', 'movie_user', 'password', 'movie_explorer');

  if($db->connect_errno) {
    throw new Exception('Unable to connect to database');
  }
  return $db;
}
//try to register
if(isset($_POST['register_username']) && isset($_POST['register_password'])) {
  $error = null;
  $conn = connect();
  
    $username = $conn->real_escape_string($_POST['register_username']);
    $password = $conn->real_escape_string($_POST['register_password']);
  
    $result = $conn->query("select * from users
                            where username='".$username."'");
  
    if($result) {
      if($result->num_rows>0){
        $error = 'That username is already taken';
        exit($error);
      }
    }
    $r1= $conn->query("insert into users values (null, '".$username."', sha1('".$password."'))");
    if($r1) {
      $r2 = $conn->query("create table if not exists ".$username."(id INT AUTO_INCREMENT NOT NULL PRIMARY KEY, title VARCHAR(50) NOT NULL, posterpath VARCHAR(100) NOT NULL, url VARCHAR(150) NOT NULL)");
      if(!$r2) {
        $error = "unable to create users watchlist";
      }
    }

    exit($error);
}
//try to login
if(isset($_POST['login_username']) && isset($_POST['login_password'])) {
  $error = null;
  $conn = connect();
  
    $username = $conn->real_escape_string($_POST['login_username']);
    $password = $conn->real_escape_string($_POST['login_password']);
  
    $result = $conn->query("select * from users
                            where username='".$username."'
                            and password=sha1('".$password."')");
  
    if($result) {
      if($result->num_rows>0){
        $_SESSION['user'] = $username;
      }else{
        $error = 'invalid username and/or password';
      }
    }else{
      $error = 'invalid username and/or password';
    }
    exit($error);
}
// remove movie from watchlist
if(isset($_POST['id'])) {
  $response;
  $user = $_SESSION['user'];
  $conn = connect();

  if ($conn->connect_error) {
    die('Connect Error, '. $conn->connect_errno . ': ' . $conn->connect_error);
  }

  $id = $conn->real_escape_string($_POST['id']);

  $result = $conn->query("delete from ".$user." where id='".$id."'");

  $sql = $conn->query("select id, title, posterpath, url from ".$user);

  if($sql->num_rows > 0) {
    $response = '<div class="col s4 offset-s4">';
    $response .= '<ul class="collection">';
    while($data = mysqli_fetch_row($sql)) {
      $id = $data[0];
      $t = $data[1];
      $p = $data[2];
      $u = $data[3];
  
      $response .= '<li class="collection-item avatar">';
      $response .= '<img src="https://image.tmdb.org/t/p/w500/'.$p.'" alt="" class="circle">';
      $response .= '<a href="'.$u.'"><span class="title">'.$t.'</span></a>';
      $response .= '<a class="secondary-content"><i id="'.$id.'" class="tiny material-icons remove">clear</i></a>';
      $response .= '</li>';
    }
    $response .= '</ul>';
    $response .= '</div>';
  }else {
    $response = '';
  }
  
  // if($result) {
  //   $response = "successfully removed";
  // }else{
  //   $response = "removal failure";
  // }

  exit($response);
}
// add movie to watchlist
if(isset($_POST['title']) && isset($_POST['posterpath']) && isset($_POST['url']) && isset($_SESSION['user'])) {
  $response;
  $user = $_SESSION['user'];
  $conn = connect();

  if ($conn->connect_error) {
    die('Connect Error, '. $conn->connect_errno . ': ' . $conn->connect_error);
  }

  $title = $conn->real_escape_string($_POST['title']);
  $posterpath = $conn->real_escape_string($_POST['posterpath']);
  $url = $conn->real_escape_string($_POST['url']);

  //$query = "SELECT * FROM $user WHERE title=$title AND posterpath=$posterpath AND url=$url";
  $result = $conn->query("select * from ".$user." where title='".$title."' and posterpath='".$posterpath."' and url='".$url."'");

  if($result->num_rows == 0) {
    $r = $conn->query("insert into ".$user." values
    (null, '".$title."', '".$posterpath."', '".$url."')");

    if(!$r) {
      $response = "Failed to add <img class='circ' src='https://image.tmdb.org/t/p/w500/".$posterpath."'/> ".$title." to your watchlist";
    }else{
      $response = "Added: <img class='circ' src='https://image.tmdb.org/t/p/w500/".$posterpath."'/> ".$title." to watchlist";
    }
  }else{
    $response = "<img class='circ' src='https://image.tmdb.org/t/p/w500/".$posterpath."'/> ".$title." is already in your watchlist";
  }
  exit($response);
}
// check for search query
if(isset($_POST['search'])) {
  $mid;
  $response = "<ul><li>No data found</li></ul>";

  $conn = connect();

  if ($conn->connect_error) {
    die('Connect Error, '. $conn->connect_errno . ': ' . $conn->connect_error);
  }

  //assign typed characters to $q using real_escape_string to prevent sql injection
  $q = $conn->real_escape_string($_POST['q']);

  //search for movie titles containing substring $q
  $sql = $conn->query("SELECT m.movieId, m.title, l.movieId, l.tmdbId FROM movies AS m, links AS l WHERE m.title LIKE '%$q%' AND m.movieId=l.movieId LIMIT 20 ");
  
  // if there are rows with title containing substring
  if($sql->num_rows > 0) {
    $response = "<ul>";
    while($data = $sql->fetch_array()){
      $response .= "<li id='".$data['tmdbId']."' class='sblink'>".$data['title']."</li>";
    }
    $response .= "</ul>";
  }

  exit($response);
}

// functions for outputing onto a page
function create_page($title, $showlisting=false, $showsearch=false) {
?>
<!DOCTYPE html>
<html>
  <head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="node_modules\materialize-css\dist\css\materialize.css"  media="screen,projection"/>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php if($title){ echo "Logged in as: ".$title; }?></title>
  </head>

  <body>
    <!--Import jQuery before materialize.js-->
    <script
          src="https://code.jquery.com/jquery-3.2.1.min.js"
          integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
          crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="node_modules\materialize-css\dist\js\materialize.min.js"></script>
    <script type="text/javascript" src="client.js"></script>
  <?php
  //check for parameter in url
if(isset($_GET['movieId'])) { 

  $conn = connect();
  
    if ($conn->connect_error) {
      die('Connect Error, '. $conn->connect_errno . ': ' . $conn->connect_error);
    }
  
    //assign typed characters to $q using real_escape_string to prevent sql injection
    $mid = $conn->real_escape_string($_GET['movieId']);
  
    //search for movie titles containing substring $q
    $sql = $conn->query("SELECT tmdbId FROM links WHERE tmdbId=$mid");
    if($sql) {
      if($sql->num_rows > 0) {
          $apikey = '38699f1077982285c7ee74b81a6b627e';
          $url = "https://api.themoviedb.org/3/movie/";
          $url .= $mid;
          $url .= "?language=en-US&api_key=";
          $url .= $apikey;
    
        ?>
          <script type="text/javascript">
            var title;
            var settings = {
              "async": true,
              "crossDomain": true,
              "url": "<?php echo $url;?>",
              "method": "GET",
              "headers": {},
              "data": "{}"
            }
    
          $.ajax(settings).done(function (response) {
            console.log(response);
            $("#content").html(`
            <img src='https://image.tmdb.org/t/p/w500/` + response['backdrop_path'] + `' />
            <a id="addbtn" class="right btn-floating btn-large waves-effect waves-light red addbtn"><i class="material-icons">add</i></a>
            <input type="hidden" id="title" name="title" value="` + response['title'] + `">
            <input type="hidden" id="posterpath" name="posterpath" value="` + response['poster_path'] + `">
            <input type="hidden" id="url" name="url" value="` + window.location + `">
            <h2>` + response['title'] + `</h2>
            <h4>` + response['tagline'] + `</h4>
            <p>` + response['overview'] + `</p>`);
          });
          </script>
          <?php
          }

    }
}
  ?>
  <?php
    if($title) {
      create_nav($title, $showlisting, $showsearch);
    }
  }
  function create_footer() {
    ?>
    <!-- <footer class="page-footer teal lighten-1 fixed">
          <div class="container teal lighten-2">
            <div class="row">
              <div class="col l6 s12">
                <h5 class="blue-text text-darken-2">Footer Content</h5>
                <p class="grey-text text-lighten-4">You can use rows and columns here to organize your footer content.</p>
              </div>
              <div class="col l4 offset-l2 s12">
                <h5 class="white-text">Links</h5>
                <ul>
                  <li><a class="grey-text text-lighten-3" href="#!">Link 1</a></li>
                  <li><a class="grey-text text-lighten-3" href="#!">Link 2</a></li>
                  <li><a class="grey-text text-lighten-3" href="#!">Link 3</a></li>
                  <li><a class="grey-text text-lighten-3" href="#!">Link 4</a></li>
                </ul>
              </div>
            </div>
          </div>
          <div class="footer-copyright">
            <div class="container">
            © 2014 Copyright Text
            <a class="grey-text text-lighten-4 right" href="#!">More Links</a>
            </div>
          </div>
        </footer> -->
    </body>
    </html>
    <?php
  }

  function create_nav($heading, $showlisting=false, $loggedin=false) {
    if($loggedin){
    ?>
  <nav>
    <div class="nav-wrapper teal lighten-2">
      <a href="index.php" class="brand-logo black-text text-darken-2">Movie Explorer</a>
      <ul id="nav-mobile" class="right hide-on-med-and-down">
        <li><a href="watchlist.php"><?php echo $heading; ?></a></li>
        <li><a href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>
  <div class="row">
      <div class="col s4">
        <div class="row">
          <div class="input-field col s12">
            <i class="material-icons prefix">search</i>
            <input type="text" id="searchBox" class="searchbox">
            <label for="searchbox">Search Movies</label>
            <div id="response" class="sb"></div>
          </div>
        </div>
      </div>
    </div>
    <?php
    }else {
      ?>
      <nav>
        <div class="nav-wrapper teal lighten-2">
          <a href="login.php" class="brand-logo black-text text-darken-2">Movie Explorer</a>
        </div>
      </nav>
      <?php
    }
    ?>
    <div id="content"></div>
    <?php
    if($showlisting) {
    ?>
      <div class="carousel">
        <a class="carousel-item" href="/phpfinalproject/fp/movie.php?movieId=603"><img src="https://image.tmdb.org/t/p/w500/7u3pxc0K1wx32IleAkLv78MKgrw.jpg"></a>
        <a class="carousel-item" href="/phpfinalproject/fp/movie.php?movieId=747"><img src="https://image.tmdb.org/t/p/w500/50mMiR0R1QclaAODTutwXBrReLJ.jpg"></a>
        <a class="carousel-item" href="/phpfinalproject/fp/movie.php?movieId=11017"><img src="https://image.tmdb.org/t/p/w500/hure3RNjFDqiuuLF96K36t3Qfzf.jpg"></a>
        <a class="carousel-item" href="/phpfinalproject/fp/movie.php?movieId=9622"><img src="https://image.tmdb.org/t/p/w500/6T81a9Jz32xyoOzNnfGE2evrgRf.jpg"></a>
      </div>
    <?php
    
    }
  }

  // function for outputing onto a login page
function create_login($title) {
  
  ?>
  <!DOCTYPE html>
  <html>
    <head>
      <!--Import Google Icon Font-->
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <!--Import materialize.css-->
      <link type="text/css" rel="stylesheet" href="node_modules\materialize-css\dist\css\materialize.css"  media="screen,projection"/>
  
      <!--Let browser know website is optimized for mobile-->
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
  
    <body>
      <!--Import jQuery before materialize.js-->
      <script
            src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous">
      </script>
      <script type="text/javascript" src="node_modules\materialize-css\dist\js\materialize.min.js"></script>
      <script type="text/javascript" src="client.js"></script>
      <?php
      if($title) {
        create_nav($title);
      }
      ?>
      <div id='error'></div>
      <div class = "row grey lighten-4">
        <form class = "col s12 offset-s4">
            <h3>Login</h3>
            <div class = "row">
               <div class = "input-field col s4">
                  <i class = "material-icons prefix">account_circle</i>
                  <input placeholder="username" value="username" id="username"
                     name="username" type="text" class="active validate" required />
                  <label for="username">Username</label>
               </div>
            </div>
            <div class="row">
              <div class = "input-field col s4">
              <i class = "material-icons prefix">lock</i>      
                 <label for="password">Password</label>
                 <input name="password" id="password" type="password" placeholder="Password"
                    class="validate" required />          
              </div>
            </div>          
            <div class="row">
              <div class = "input-field col s4">
              <input type="button" id="login" value="Login" class="waves-effect waves-light btn"></input>
              Don't have an account? <a href="register.php">Register</a>        
              </div>
            </div>          
         </form>       
      </div>
      
    <?php
    }

     // function for outputing onto a register page
function create_register($title) {
  
  ?>
  <!DOCTYPE html>
  <html>
    <head>
      <!--Import Google Icon Font-->
      <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
      <!--Import materialize.css-->
      <link type="text/css" rel="stylesheet" href="node_modules\materialize-css\dist\css\materialize.css"  media="screen,projection"/>
  
      <!--Let browser know website is optimized for mobile-->
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    </head>
  
    <body>
      <!--Import jQuery before materialize.js-->
      <script
            src="https://code.jquery.com/jquery-3.2.1.min.js"
            integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
            crossorigin="anonymous">
      </script>
      <script type="text/javascript" src="node_modules\materialize-css\dist\js\materialize.min.js"></script>
      <script type="text/javascript" src="client.js"></script>
      <?php
      if($title) {
        create_nav($title);
      }
      ?>
      <div id='error'></div>
      <div class = "row grey lighten-4">
        <form class="col s12 offset-s4">
            <h3>Register</h3>
            <div class = "row">
               <div class = "input-field col s4">
                  <i class = "material-icons prefix">account_circle</i>
                  <input placeholder="username" value="username" id="username"
                     type="text" name="register_username" class="active validate" required />
                  <label for="name">Username</label>
               </div>
            </div>
            <div class="row">
              <div class = "input-field col s4">
              <i class = "material-icons prefix">lock</i>      
                 <label for="password">Password</label>
                 <input name="register_password" id="password" type="password" placeholder="Password"
                    class="validate" required />          
              </div>
            </div>          
            <div class="row">
              <div class = "input-field col s4">
              <input type="button" id="register" value="Register" class="waves-effect waves-light btn"></input>       
              </div>
            </div>          
         </form>       
      </div>
      
    <?php
    }

         // function for outputing onto a watchlist page
function create_watchlist($user, $showlisting=false, $showsearch=false) {
  
?>
<!DOCTYPE html>
<html>
  <head>
    <!--Import Google Icon Font-->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!--Import materialize.css-->
    <link type="text/css" rel="stylesheet" href="node_modules\materialize-css\dist\css\materialize.css"  media="screen,projection"/>

    <!--Let browser know website is optimized for mobile-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php if($user){ echo "Logged in as: ".$user; }?></title>
  </head>

  <body>
    <!--Import jQuery before materialize.js-->
    <script
          src="https://code.jquery.com/jquery-3.2.1.min.js"
          integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
          crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="node_modules\materialize-css\dist\js\materialize.min.js"></script>
    <script type="text/javascript" src="client.js"></script>
  <?php
  
  
  ?>
  <?php
    if($user) {
      create_nav($user, $showlisting, $showsearch);
    }
  // get watchlist and display it
  $conn = connect();

  if ($conn->connect_error) {
    die('Connect Error, '. $conn->connect_errno . ': ' . $conn->connect_error);
  }

  $sql = $conn->query("select id, title, posterpath, url from ".$user);

  if($sql->num_rows > 0) {
    echo '<div id="watchlist" class="row">';
    echo '<div class="col s4 offset-s4">';
    echo '<ul class="collection">';
    while($data = mysqli_fetch_row($sql)) {
      $id = $data[0];
      $t = $data[1];
      $p = $data[2];
      $u = $data[3];
  
      echo '<li class="collection-item avatar">';
      echo '<img src="https://image.tmdb.org/t/p/w500/'.$p.'" alt="" class="circle">';
      echo '<a href="'.$u.'"><span class="title">'.$t.'</span></a>';
      echo '<a class="secondary-content"><i id="'.$id.'" class="tiny material-icons remove">clear</i></a>';
      echo '</li>';
    }
    echo '</ul>';
    echo '</div>';
    echo '</div>';
  } 
}

function redirect($location){
  //see if someone is logged in
      $rd = "Location: ".$location;
      header($rd); 
}

  
?>