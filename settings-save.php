<!DOCTYPE html>
<html>
<head>
  <title>Inventory System</title>
  <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
</head>

  <body>
    <div class="container">

      <div class="row">
      <div class="col-md-12">
      <?php include('menu.php'); ?>

      <div class="jumbotron">

      <?php
      
        # Extract fields from input form
        $ep = $_POST['endpoint'];
      	$ep = str_replace(":3306", "", $ep);
      	$db = $_POST['database'];
        $un = $_POST['username'];
        $pw = $_POST['password'];

        # Store settings in Parameter Store
        error_log('Saving settings');
        require 'aws-autoloader.php';
        use Aws\Exception\AwsException;
        #
  
        #$az = file_get_contents('http://169.254.169.254/latest/meta-data/placement/availability-zone');

        $ch = curl_init();

// get a valid TOKEN
$headers = array (
        'X-aws-ec2-metadata-token-ttl-seconds: 21600' );
$url = "http://169.254.169.254/latest/api/token";
#echo "URL ==> " .  $url;
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
curl_setopt( $ch, CURLOPT_URL, $url );
$token = curl_exec( $ch );

#echo "<p> TOKEN :" . $token;
// then get metadata of the current instance 
$headers = array (
        'X-aws-ec2-metadata-token: '.$token );
#$url = "http://169.254.169.254/latest/dynamic/instance-identity/document";

$url = "http://169.254.169.254/latest/meta-data/placement/availability-zone";

curl_setopt( $ch, CURLOPT_URL, $url );
curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
$result = curl_exec( $ch );
$az = curl_exec( $ch );
$region = substr($az, 0, -1);

#echo "<p> RESULT :" . $result;


        $secrets_client = new Aws\SecretsManager\SecretsManagerClient([
          'version' => 'latest',
          'region'  => $region
        ]);
        

        # Save settings in Parameter Store
        try{
        $result = $secrets_client->createSecret([
          'Name' => '/inventory-app/endpoint',
          'SecretString' => $ep
        ]);
      } catch (AwsException $e) {
        $error = $e->getAwsErrorCode();
        if ($error == 'ResourceExistsException') {
          $result = $secrets_client->updateSecret([
            'SecretId' => '/inventory-app/endpoint',
            'SecretString' => $ep
          ]);
        }
      }

      try{
        $result = $secrets_client->createSecret([
          'Name' => '/inventory-app/username',
          'SecretString' => $un
        ]);
      } catch (AwsException $e) {
        $error = $e->getAwsErrorCode();
        if ($error == 'ResourceExistsException') {
          $result = $secrets_client->updateSecret([
            'SecretId' => '/inventory-app/username',
            'SecretString' => $un
          ]);
        }
      }
        try{
        $result = $secrets_client->createSecret([
          'Name' => '/inventory-app/password',
          'SecretString' => $pw
        ]);
      } catch (AwsException $e) {
        $error = $e->getAwsErrorCode();
        if ($error == 'ResourceExistsException') {
          $result = $secrets_client->updateSecret([
            'SecretId' => '/inventory-app/password',
            'SecretString' => $pw
          ]);
        }
      }

      try{
        $result = $secrets_client->createSecret([
          'Name' => '/inventory-app/db',
          'SecretString' => $db
        ]);
      } catch (AwsException $e) {
        $error = $e->getAwsErrorCode();
        if ($error == 'ResourceExistsException') {
          $result = $secrets_client->updateSecret([
            'SecretId' => '/inventory-app/db',
            'SecretString' => $db
          ]);
        }
      }
        # Try to connect to database
      try{
        $connect = mysqli_connect($ep, $un, $pw);
        if(!$connect) {
          # Failed to connect
          echo "<br /><p>Unable to Establish Database Connection<i>" . mysqli_error($connect) .  "</i></p>";

        } else {

          $dbconnect = mysqli_select_db($connect, $db);
          if(!$dbconnect) {
            # Failed to find database
            echo "<br /><p>Connected to Database but DB not found<i>" . mysqli_error($connect) .  "</i></p>";

          } else {
            # Load initial data
            echo "<br /><p>Loading initial data...</p>";
            echo " up ". $ep . " " . $un . " " . $pw . " " . $db;
            error_log('Settings are:... ' . $ep. " / " . $db . " / " . $un . " / " . $pw);
            exec("mysql -u $un -p$pw -h $ep $db < sql/inventory.sql");
          }
          
          # Send them back to home page
          echo "<script>window.location.href ='/';</script>";
        }
      } catch (Exception $e){
            echo "Check your Database connection details and try again..";
      }
        # Close database connection
        mysqli_close($connect);

      ?>

    </div>
    </div>
  </div>
  </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/scripts.js"></script>
  </body>
</html>
