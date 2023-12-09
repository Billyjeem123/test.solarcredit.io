<?php



class Auth extends AbstractClasses
{


  private   $conn;


  public function __construct(Database $database)
  {
    $this->conn = $database->connect();
  }


  #authenticate Api Key

  public   function authenticateAPIKey($api_key): bool
  {


    if (empty($api_key)  ||  $api_key == "") {
      $_SESSION['err'] = "missing API key";
      return false;
    }


    if ($api_key != $_ENV['APP_TOKEN']) {

      $_SESSION['err'] = "Unauthorized access";
      return false;
    }


    return true;
  }
  
  
}
