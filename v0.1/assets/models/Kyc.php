<?php

class Kyc  extends AbstractClasses
{

    private   $conn;
    private $renikyc;

    public function __construct(Database $database, ReniKYC $reniPayment)
    {

        $this->conn = $database->connect();
        $this->renikyc = $reniPayment;
    }



    public function registerUserKyc(array $data)
    {

        #checkVeridicationMeans.::This checks if user has already done KYC.
        if ($this->hasVerifiedKyc($data['usertoken'])) {
          $this->outputData(false, 'Account has already been verified', null);
            exit;
        }

        $reniPayLoad = [
           'usertoken' => $data['renitoken'],
           'bvn' => $data['bvn'],
        ];


        $sendReniKYC = $this->renikyc->updateUserBVN($reniPayLoad);
        
       

        if(!$sendReniKYC){
           return  $this->outputData(false,"Unable to process request, Please contact support", null);
        }
      

        #  Prepare the fields and values for the insert query
        $fields = [
            'renitoken' => $data['renitoken'],
            'usertoken' => $data['usertoken'],
            'fullname' => $data['fname'],
            'occupation' => $data['occupation'],
            'status' => 1,
            'time' => time()

        ];

        # Build the SQL query
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));
        $columns = implode(', ', array_keys($fields));
        $sql = "INSERT INTO tblkyc ($columns) VALUES ($placeholders)";

        #  Execute the query and handle any errors
        try {
            $stmt =  $this->conn->prepare($sql);
            $i = 1;
            foreach ($fields as $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($i,   $value, $type);
                $i++;
            }
            $stmt->execute();

            http_response_code(201);
            $output = $this->outputData(true, 'KYC verification successful', null);
            exit;
        } catch (PDOException $e) {

            $output  = $this->respondWithInternalError('Error: ' . $e->getMessage());
            exit;
        } finally {
            $stmt = null;
            $this->conn = null;
        }

        return $output;
    }


    public function hasVerifiedKyc(string $usertoken)
    {
        try {
            $sql = 'SELECT COUNT(*) FROM tblkyc WHERE usertoken = :usertoken';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':usertoken', $usertoken, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $count = $stmt->fetchColumn();
                
                if ($count > 0) {
                    return true; // KYC is verified
                } else {
                    return false; // KYC is not verified
                }
            } else {
                $this->respondWithInternalError('Failed to execute query');
                return false; // Return false on query execution failure
            }
        } catch (Exception $e) {
            $this->respondWithInternalError('Error: ' . $e->getMessage());
            return false; // Return false on exception
        } finally {
            $stmt = null;
        }
    }
    

}
