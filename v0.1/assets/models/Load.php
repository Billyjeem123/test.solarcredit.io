<?php
use PhpOffice\PhpSpreadsheet\IOFactory;

class Load extends AbstractClasses
{

    private   $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->connect();
    }

    public function saveLoadItems()
{
    $inputFileName = $_FILES['applianace']['tmp_name'];
    $fileExtension = pathinfo($_FILES['applianace']['name'], PATHINFO_EXTENSION);

    if ($fileExtension !== 'xlsx') {
        $this->outputData(false, 'Only Excel files (XLSX) is allowed.', null);
        return;
    }

    if (!file_exists($inputFileName) || !is_readable($inputFileName)) {
        $this->outputData(false, 'Uploaded file is missing or not readable.', null);
        return;
    }
    $spreadsheet = IOFactory::load($inputFileName);
    $worksheet = $spreadsheet->getActiveSheet();

    $headers = [];
    $appliancesIndex = null;
    $wattsIndex = null;

    foreach ($worksheet->getRowIterator() as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false);

        $rowValues = [];
        foreach ($cellIterator as $cell) {
            $rowValues[] = $cell->getValue();
        }

        if (empty($headers)) {
            $headers = $rowValues;
            $appliancesIndex = array_search('Appliances', $headers);
            $wattsIndex = array_search('Watts', $headers);

            #  Check if required headers are present
            if ($appliancesIndex === false || $wattsIndex === false) {
                $this->outputData(false, 'Required headers appliances or watts not found in the file.', null);
                return;
            }

            #  continue;
        }

        if ($appliancesIndex !== false && $wattsIndex !== false) {
            $appliance = $rowValues[$appliancesIndex];
            $watts = intval($rowValues[$wattsIndex]);

            $updateOrInsertAppliance = $this->updateOrInsertAppliance($appliance, $watts);
            if (!$updateOrInsertAppliance) {
                $this->outputData(false, 'Unable to complete request, please try again', null);
            }
        }
    }
    
    $this->outputData(true, 'Record saved', null);
}


    public function updateAppliance($oldAppliances, $newAppliances)
    {
        try {
            $updateSql = 'UPDATE tblloadltems SET appliances = :newAppliances WHERE appliances = :oldAppliances';
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindParam(':oldAppliances', $oldAppliances, PDO::PARAM_STR);
            $updateStmt->bindParam(':newAppliances', $newAppliances, PDO::PARAM_STR);
            $updateStmt->execute();
    
            return true; #  Successful update
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
            return false; #  Error occurred
        } finally {
            $updateStmt = null;
        }
    }
    
    public function insertAppliance($appliances, $watts)
    {
        try {
            $insertSql = 'INSERT INTO tblloadltems (appliances, watts) VALUES (:appliances, :watts)'; #  Insert with appropriate columns and values
            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->bindParam(':appliances', $appliances, PDO::PARAM_STR);
            $insertStmt->bindParam(':watts', $watts, PDO::PARAM_STR);
            $insertStmt->execute();

            return true; #  Successful insert
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
            return false; #  Error occurred
        } finally {
            $insertStmt = null;
        }
    }

    public function updateOrInsertAppliance(string $appliances, int $watts)
    {
        try {
            $sql = 'SELECT appliances FROM tblloadltems WHERE appliances = :appliances';
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':appliances', $appliances, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $this->updateAppliance($appliances, $appliances);
            } else {
                return $this->insertAppliance($appliances, $watts);
            }
        } catch (PDOException $e) {
            $_SESSION['err'] = $e->getMessage();
            $this->respondWithInternalError($_SESSION['err']);
            return false; #  Error occurred
        } finally {
            $stmt = null;
        }
    }


    #getRecommendedInverter::This method fetches all Recommended loand product

    public function getRecommendedInverter($productsize)
    {

        $sql = "SELECT *
    FROM tblproducts WHERE size >= $productsize AND pcat_id IN (
      SELECT id
      FROM tblcategory
      WHERE catname = 'Inverter'
    )
    ORDER BY tblproducts.id DESC;
    
    ";
        try {
            $stmt = $this->conn->query($sql);
            $stmt->execute();
            $count = $stmt->rowCount();
            if ($count === 0) {
                $_SESSION['err'] = 'No record found';
                return [];
            }
            $dataArray = array();
            $recommendedInveter = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($recommendedInveter as $allInveter) {
                $array = [
                    'productname' => ($allInveter['pname']),
                    'productimage' => ($allInveter['imageUrl']),
                    'productprice' => $this->formatCurrency($allInveter['price']),
                    'productQuantity' => intval($allInveter['pquantity']),
                    'prductDesc' => ($allInveter['pdesc']),
                    'prductSize' => ($allInveter['size'])
                ];

                array_push($dataArray, $array);
            }
        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error while recommending inverter:' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            #  $this->conn = null;
        }
        return $dataArray;
    }

    #getRecommendedInverter::This method fetches all Recommended getRecommendedChargeController product

    public function getRecommendedChargeController($chargeController)
    {

        $sql = "SELECT *
    FROM tblproducts WHERE size >= $chargeController AND unit = 'AMPS' AND pcat_id IN (
      SELECT id
      FROM tblcategory
      WHERE catname = 'Chargecontroller'
    )
    ORDER BY tblproducts.id DESC;
    
    ";
        try {
            $stmt = $this->conn->query($sql);
            $stmt->execute();
            $count = $stmt->rowCount();
            if ($count === 0) {
                $_SESSION['err'] = 'No record found';
                return [];
            }
            $dataArray = array();
            $recommendedInveter = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($recommendedInveter as $allInveter) {
                $array = [
                    'productname' => ($allInveter['pname']),
                    'productimage' => ($allInveter['imageUrl']),
                    'productprice' => $this->formatCurrency($allInveter['price']),
                    'productQuantity' => intval($allInveter['pquantity']),
                    'prductDesc' => ($allInveter['pdesc']),
                    'prductSize' => ($allInveter['size'])
                ];
                array_push($dataArray, $array);
            }
        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error while recommending chargecontroller:' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            #  $this->checkSize = null;
        }
        return $dataArray;
    }

    #getRecommendedBatteryPower::This method fetches all Recommended getRecommendedBatteryPower product

    public function getRecommendedBatteryPower($batterypower)
    {

        $sql = "SELECT *
    FROM tblproducts WHERE size >= $batterypower AND unit = 'AMPS' AND pcat_id IN (
      SELECT id
      FROM tblcategory
      WHERE catname = 'Battery'
    )
    ORDER BY tblproducts.id DESC;
    
    ";
        try {
            $stmt = $this->conn->query($sql);
            $stmt->execute();
            $count = $stmt->rowCount();
            if ($count === 0) {
                $_SESSION['err'] = 'No record found';
                return [];
            }
            $dataArray = array();
            $recommendedInveter = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($recommendedInveter as $allInveter) {
                $array = [
                    'productname' => ($allInveter['pname']),
                    'productimage' => ($allInveter['imageUrl']),
                    'productprice' => $this->formatCurrency($allInveter['price']),
                    'productQuantity' => intval($allInveter['pquantity']),
                    'prductDesc' => ($allInveter['pdesc']),
                    'prductSize' => ($allInveter['size'])
                ];

                array_push($dataArray, $array);
            }
        } catch (PDOException $e) {
            $_SESSION['err'] = 'Error while recommending chargecontroller:' . $e->getMessage();
            return false;
        } finally {
            $stmt = null;
            #  $this->checkSize = null;
        }
        return $dataArray;
    }
}
