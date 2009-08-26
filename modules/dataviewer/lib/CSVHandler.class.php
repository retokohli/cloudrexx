<?php
class CSVHandler {
    var $Separator;
    var $DataFile;
    var $DataKey;
    var $HeaderData;
    var $ItemsList;
    var $Items_Count;

    //Constructor
    function CSVHandler($Filename, $Separator, $KeyFieldName) {
        $this->DataFile=$Filename;
        $this->DataKey=$KeyFieldName;
        $this->Separator=$Separator;
        $this->color="#FFFFFF";
    }


    //read data into this->ItemsList and return it in an array
    function ReadCSV() {
        $this->Items_Count=0;
        $this->ItemsList=array();
        $Item=array();
        $fp = fopen ($this->DataFile,"r");
        $this->HeaderData = fgetcsv ($fp, 3000, ";");

        while ($DataLine = fgetcsv ($fp, 3000, $this->Separator)) {
            for($i=0;$i<count($this->HeaderData);$i++){
                $Item[$this->HeaderData[$i]]=$DataLine[$i];
            }
            array_push($this->ItemsList,$Item);
            $this->Items_Count++;
        }
        fclose($fp);

        return ($this->ItemsList);
    }


    //returns an array with all available columns in csv file
    function ListAllHeader() {
        $Data=$this->ReadCSV();
        reset ($this->HeaderData);

        while(list($HKey,$HVal)=each($this->HeaderData)) {            //Create Headers Line
            if ($HVal !== "") {
                $HHeaders[] = $HVal;
            }
        }

        return $HHeaders;
    }
}
?>
