<?php

class NewsLetter extends AbstractClasses {



    public function createList($data)
    {
        $url = $_ENV['ReniMail']."/createList";
    
        $payload = [
            "name" => $data['name'],
            "details" => $data['details'],
        ];
    
        try {
            $connectToReniMail = $this->connectToReniMail($payload, $url);

             $encodeMailResponse = json_decode($connectToReniMail,true);
            if($encodeMailResponse['success'] == true) {


                 $dataArray = [
                  "name" => $encodeMailResponse['data']['name'],
                  "details" => $encodeMailResponse['data']['details'],
                  "listToken" => $encodeMailResponse['data']['listToken'],
                  "listid" => $encodeMailResponse['data']['id']
                 ];

                if(!$this->saveList($dataArray)){
                    $this->outputData(false, 'Error saving list', null);
                }

                $this->outputData(true, "List Added",  null);

            } else {
                $this->outputData(false, $encodeMailResponse['message'] ?? "Unable to process request, Please contact support", null);
            }

        } catch (Exception $e) {
            $this->outputData(false, $e->getMessage(), null);
        }
    }


     public function getAllLists() {

        $url = $_ENV['ReniMail']."/getLists";

        $connectToReniMail = $this->connectToReniMail(array(), $url);
        
        

             $encodeMailResponse = json_decode($connectToReniMail,true);
             
            //  var_dump($encodeMailResponse);
             

             if($encodeMailResponse !== null){
             
            if($encodeMailResponse['success'] == true) {

                $this->outputData(true, $encodeMailResponse['message'], $encodeMailResponse['data']);

            } else {
                $this->outputData(false, $encodeMailResponse['message'], null);
            }
             }else{
                 $this->outputData(false, "Unable to process request, Please contact  support", null);
             }
     }


     public  function addMailToNewsLetter($data) {


         $getNewsletterInfo = $this->getNewsletterInfo();
        $payload = [
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'mail' => $data['mail'],
            'listToken' => $getNewsletterInfo['listToken']
        ];
    
        $url = $_ENV['ReniMail'] . "/addContact";
    
        try {
            $connectToReniMail = $this->connectToReniMail($payload, $url);
             $encodeMailResponse = json_decode($connectToReniMail, true);
            
            if($encodeMailResponse !== null){
            return $encodeMailResponse;
            }
            
                return  0;
             
               
        } catch (Exception $e) {
            // Handle exceptions here
            $this->outputData(false, "An error occurred while adding userto newsletter mail: " . $e->getMessage(), null);
        }
    }



    public  function addContactToList($data) {

       $payload = [
           'firstname' => $data['fname'],
           'lastname' => $data['lname'],
           'mail' => $data['mail'],
           'listToken' => $data['listoken']
       ];
   
       $url = $_ENV['ReniMail'] . "/addContact";
   
       try {
           $connectToReniMail = $this->connectToReniMail($payload, $url);
           $decodedResponse = json_decode($connectToReniMail, true);
           
           if($decodedResponse !== null){
   
           if ($decodedResponse['success'] == true) {
               $this->outputData(true, $decodedResponse['message'], $decodedResponse['data']);
           } else {
               $this->outputData(false, $decodedResponse['message'], null);
           }
           
           }else{
               
                $this->outputData(false, "Unable to process request, Please contact support", null);
            
           }
       } catch (Exception $e) {
           // Handle exceptions here
           $this->outputData(false, "An error occurred while adding mail to promotions: " . $e->getMessage(), null);
       }
   }
    
    

    public function saveList(array $dataArray)
    {
        $db = new Database();
        try {
            $sql = 'INSERT INTO tbllist (listoken, listname, details,listid) VALUES (:listoken, :listname, :details, :listid)';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':listoken', $dataArray['listToken']);
            $stmt->bindParam(':listname', $dataArray['name']);
            $stmt->bindParam(':details', $dataArray['details']);
            $stmt->bindParam(':listid', $dataArray['listid']);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            $this->respondWithInternalError('Error: ' . $e->getMessage());
            return false;
        } finally {
            $stmt = null;
            $db = null;
        }
    }


    public function getNewsletterInfo($listname="News-letter"){

        try {
            $db = new Database();
            $sql = 'SELECT listoken, listname FROM tbllist WHERE listname = :listname';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':listname', $listname);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                $_SESSION['err']  = 'list is not valid';
                return false;
            }

            $planArray = $stmt->fetch(PDO::FETCH_ASSOC);

            $array = [
                'listname' => $planArray['listname'],
                'listToken' => $planArray['listoken']
            ];
        } catch (PDOException $e) {
            $_SESSION['err']  = 'Error: ' . $e->getMessage();
            return false;
        } finally {
            $stmt  = null;
            unset($db);
        }
        return $array;
    }


    public function getPromotionInfo($listname="Promotion"){

        try {
            $db = new Database();
            $sql = 'SELECT listoken, listname FROM tbllist WHERE listname = :listname';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':listname', $listname);
            $stmt->execute();

            if ($stmt->rowCount() === 0) {
                http_response_code(404);
                $_SESSION['err']  = 'list is not valid';
                return false;
            }

            $planArray = $stmt->fetch(PDO::FETCH_ASSOC);

            $array = [
                'listname' => $planArray['listname'],
                'listToken' => $planArray['listoken']
            ];
        } catch (PDOException $e) {
            $_SESSION['err']  = 'Error: ' . $e->getMessage();
            return false;
        } finally {
            $stmt  = null;
            unset($db);
        }
        return $array;
    }
    
    public function sendMailToContact($data) {
        $url = $_ENV['ReniMail']."/sendMailsToList";
    
        try {
           
    
            $payload = [
               'data' => $data['listoken'],
               'subject' => $data['subject'],
               'body' => $data['message']
            ];
    
            $connectToReniMail = $this->connectToReniMail($payload, $url);
            $decodedResponse = json_decode($connectToReniMail, true);
        
            
             if($decodedResponse !== null){
    
            if ($decodedResponse['success'] == true) {
                $this->outputData(true, $decodedResponse['message'], $decodedResponse['data']);
            } else {
                $this->outputData(false, $decodedResponse['message'], null);
            }
            
             }else{
            
             return   $this->outputData(false, "Unable to process reqest, Please contact support", null);
                 
             }
        } catch (Exception $e) {
            $this->outputData(false, "An error occurred: " . $e->getMessage(), null);
        }
    }
    


    public function getListIdFromList($NewsLetter = "Newsletter") {
        try {
            $db = new Database();
            $sql = 'SELECT listoken FROM tbllist WHERE listname = :Newsletter';
            $stmt = $db->connect()->prepare($sql);
            $stmt->bindParam(':Newsletter', $NewsLetter);
            $stmt->execute();
    
            $rowCount = $stmt->rowCount();
            if ($rowCount === 0) {
                http_response_code(404);
                $this->outputData(false, 'No record found', null);
                exit;
            }
            $dataRecords = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$dataRecords) {
                $this->outputData(false, 'No record found', null);
                exit;
            }
                $array = [
                    'listoken' => $dataRecords['listoken']
                ];
    
            
    
        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error: ' . $e->getMessage();
            return false;
        }finally{
            $db = null;
        }
        return $array;
    }
 
    

    public function getAllList() {
        try {
            $db = new Database();
            $sql = "SELECT listoken, listname, details, listid FROM tbllist";
            $stmt = $db->connect()->query($sql);
            $stmt->execute();
    
            $dataRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$dataRecords) {
                $this->outputData(false, 'No record found', null);
                exit;
            }
             
             $dataArray = [];
            foreach ($dataRecords as $records){
                $array = [
                    'token' => $records['listoken'],
                    'name' => $records['listname'],
                    // 'listid' => $records['listid'],
                    'details' => $records['details'],
                    
                ];
                array_push($dataArray , $array);
            }

    
            
    
        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error: ' . $e->getMessage();
            return false;
        }finally{
            $db = null;
        }
        return $this->outputData(true, "fetched lists", $dataArray);
    }
 
 
         public function linkUserToReniTrust($data)
    {
        $payload = [
            'mail' => $data['mail'],
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'username' => $this->addRandomNumberToFirstname($data['firstname']),
            'gender' => 'null',
            'phone' => $data['phone'],
        ];
    
        $url = $_ENV['RENI_SANDBOX'] . '/createUserProfile';
    
        try {
            $connectToReniMail = $this->connectToReniTrust($payload, $url);
            $decodedResponse = json_decode($connectToReniMail, true);
        
          
            
            if ($decodedResponse !== null) {
                
                if (isset($decodedResponse['success']) && $decodedResponse['success'] === true) {
                $token = ($decodedResponse['data']['token']) ?? 0;
                return $token;
            } else {
                return ($decodedResponse['data']);
            
            }
                
            }else{
             return 0;
                
            }
            
        } catch (Exception $e) {
            // Handle exceptions here
            $this->outputData(false, 'An error occurred while onboarding user: ' . $e->getMessage(), null);
        }
    }

    public function addRandomNumberToFirstname( $firstname ) {
        $randomNumber = rand( 100, 999 );
        // Generate a random number between 100 and 999
        $newUsername = $firstname  . $randomNumber;

        $firstname = $newUsername;
        return $firstname;
    }
    
    #ist
    //  public function connectToReniTrust(array $payload, string $url)
    // {
    //     $ch = curl_init();
    //     curl_setopt($ch, CURLOPT_URL, $url);
    //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    //     curl_setopt($ch, CURLOPT_POST, 1);
    //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
    //     // Set the Authorization header
    //     $headers = array(
    //        'Authorization: Bearer ' . $_ENV['Solar_Access_Bearer']
    //     );
    //     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
    //     $response = curl_exec($ch);
    
    //     curl_close($ch);
    
    //     return $response;
    // }
    
       
    public function connectToReniMail(array $payload, string $url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    
        // Set the Authorization header 
        $headers = array(
            'Authorization: Bearer ' . $_ENV['Solar_Access_Bearer']
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    
        $response = curl_exec($ch);
        if ($response === false || $response === null) {
            $error = curl_error($ch);
            $this->outputData(false, 'Unable to process request, plese try again later',null);
            exit;
        }
    
        curl_close($ch);
    
        return $response;
    }
         


}