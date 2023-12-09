<?php

ini_set("allow_url_fopen", true);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);

date_default_timezone_set("Africa/Lagos");

class Database{
    private  $pdo  = null;

    /**
     * connect database
     *
     * @return PDO
     */
    public function connect(): PDO
    {
        if($this->pdo === null){

        $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8";
        
        $this->pdo =  new PDO($dsn, $_ENV['DB_USER'],$_ENV['DB_PWORD'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
    }
    return $this->pdo;
    }
    
    public function see(){
        
        echo "j";
    }

}





?>
