      <?php
        # Retrieve settings from Parameter Store
        error_log('Retrieving settings');
        require 'aws-autoloader.php';
      
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
        
        $url = "http://169.254.169.254/latest/meta-data/placement/availability-zone";
        
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "GET" );
        $result = curl_exec( $ch );
        $az = curl_exec( $ch );
        
        #echo "<p> RESULT :" . $result;

        $region = substr($az, 0, -1);
        
        $secrets_client = new Aws\SecretsManager\SecretsManagerClient([
          'version' => 'latest',
          'region'  => $region
        ]);
        

        try {
          $ep = $secrets_client->getSecretValue([
            'SecretId' => '/inventory-app/endpoint'
          ]);
          $ep = $ep["SecretString"];
          #$ep = json_decode($ep, true);
          #$ep = $ep["/inventory-app/endpoint"];
          $un = $secrets_client->getSecretValue([
            'SecretId' => '/inventory-app/username'
          ]);
          $un = $un["SecretString"];
          #$un = json_decode($un, true);
          #$un = $un["/inventory-app/username"];
          $pw = $secrets_client->getSecretValue([
            'SecretId' => '/inventory-app/password'
          ]);
          $pw = $pw["SecretString"];
          #$pw = json_decode($pw, true);
          #$pw = $pw["/inventory-app/password"];
          $db = $secrets_client->getSecretValue([
            'SecretId' => '/inventory-app/db'
          ]);
          $db = $db["SecretString"];
          #$db = json_decode($db, true);
          #$db = $db["/inventory-app/db"];
        }
        catch (Exception $e) {
          $ep = '';
          $db = '';
          $un = '';
          $pw = '';
        }
      error_log('Settings are: ' . $ep. " / " . $db . " / " . $un . " / " . $pw);
      #echo " Check your Database settings ";
      ?>
