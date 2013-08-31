<?php 

require_once ("MiniTemplator.class.php");

$get = new MiniTemplator;

require 'db.php';

  // Connect to the server
  if (!($connection = @ mysql_connect(DB_HOST, DB_USER, DB_PW))) {
    showerror();
  }

  if (!mysql_select_db(DB_NAME, $connection)) {
    showerror();
  }
  
   //Show Error Function
   function showerror() {
     die("Error " . mysql_errno() . " : " . mysql_error());
   }

  $get->readTemplateFromFile("wine_search_template.htm");

  //Form
  $get->setVariable("action","wine_result.php");
  $get->setVariable("method","GET");

  //Wine Name textbox
  $get->setVariable("inputLabel", "Wine Name: ");
  $get->setVariable("inputName","wineName");
  $get->setVariable("inputType","text");
  $get->addBlock("inputWineBlock");

  //Winery Name textbox
  $get->setVariable("inputLabel","Winery Name: ");
  $get->setVariable("inputName","wineryName");
  $get->setVariable("inputType","text");
  $get->addBlock("inputWineBlock");

  //Region combobox
  $regions = getDropList("region", "region_name");
  $get->setVariable("optionLabel","Region: ");
  $get->setVariable("selectName","regionName");
     foreach ($regions as $id => $name):
       $get->setVariable("optionId",$id);
       $get->setVariable("optionName", $name);
       $get->addBlock("optionRegionInside");
     endforeach; 
  $get->addBlock("optionRegionBlock");

  //Grape Variety combobox
  $grapeVariety = getDropList("grape_variety", "variety");
  $get->setVariable("optionLabel","Grape Variety: ");
  $get->setVariable("selectName","grapeVariety");
    foreach ($grapeVariety as $id => $name):
      $get->setVariable("optionId",$id); 
      $get->setVariable("optionName", $name);
      $get->addBlock("optionGrapeInside");
    endforeach; 
  $get->addBlock("optionGrapeBlock");

  //Minimum Year combobox
  $yearFrom = getDropList("wine", "year");
  $get->setVariable("optionLabel","Year: ");
  $get->setVariable("selectName","yearFrom");
    foreach ($yearFrom as $id => $name):
      $get->setVariable("optionId",$id);
      $get->setVariable("optionName", $name);
      $get->addBlock("optionYearToInside");
    endforeach;
  $get->addBlock("optionYearToBlock");

  //Maximum Year combobox
  $yearTo = getDropList("wine", "year");
  $get->setVariable("optionLabel","  to  ");
  $get->setVariable("selectName","yearTo");
    foreach ($yearTo as $id => $name):
      $get->setVariable("optionId",$id);
      $get->setVariable("optionName", $name);
      $get->addBlock("optionYearFromInside");
    endforeach;
  $get->addBlock("optionYearFromBlock");

  //Minimum Stock textbox
  $get->setVariable("inputLabel","Minimum Number of Wines in Stock: ");
  $get->setVariable("inputName","minStock");
  $get->setVariable("inputType","text");
  $get->addBlock("inputStockOrderBlock");

  //Minimum Wines Ordered textbox
  $get->setVariable("inputLabel","Minimum Number of Wines Order: ");
  $get->setVariable("inputName","minOrder");
  $get->setVariable("inputType","text");
  $get->addBlock("inputStockOrderBlock");

  //Minimum Cost textbox
  $get->setVariable("inputLabel","Cost Range: ");
  $get->setVariable("inputName","costMin");
  $get->setVariable("inputType","text");
  $get->addBlock("inputCostBlock");

  //Maximum Cost textbox
  $get->setVariable("inputLabel","  to  ");
  $get->setVariable("inputName","costMax");
  $get->setVariable("inputType","text");
  $get->addBlock("inputCostBlock");

  //Submit button
  $get->setVariable("submitName","submitBtn");
  $get->setVariable("submitType","submit");
  $get->setVariable("submitValue","search");

  //Display the output
  $get->generateOutput();

  //Function to populate the Combo box
  function getDropList($tableName, $valueName) {
    // Connect to the server
    if (!($connection = @ mysql_connect(DB_HOST, DB_USER, DB_PW))) {
     showerror();
    }

    if (!mysql_select_db(DB_NAME, $connection)) {
     showerror();
    }

    // Query to find distinct values of $attributeName in $tableName
    $distinctQuery = "SELECT DISTINCT {$valueName} FROM {$tableName}";

    // Run the distinctQuery on the databaseName
    if (!($resultId = @ mysql_query ($distinctQuery, $connection))) {
      showerror();
    }
	
    $dropList = array();

    // Retrieve each row from the query
    while ($row = @ mysql_fetch_array($resultId)) {
      // Get the value for the attribute to be displayed
      $dropList[$row[$valueName]] = $row[$valueName];
    }

    return $dropList;
  } // end of function
?>