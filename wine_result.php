<?php

require_once ("MiniTemplator.class.php");
require 'db.php';
  
$get = new MiniTemplator;
$get->readTemplateFromFile("wine_result_template.htm");

//Get the search result
$wines = displayWineList();

// Display the search results
if(!empty($wines)) {
  foreach($wines as $wine): 
    $get->setVariable("wineName",$wine['wine_name']);
    $get->setVariable("grapeVariety",$wine['variety']);
    $get->setVariable("year",$wine['year']);
    $get->setVariable("wineryName",$wine['winery_name']);
    $get->setVariable("regionName",$wine['region_name']);
    $get->setVariable("cost",$wine['cost']);
    $get->setVariable("stockOnHand",$wine['on_hand']);
    $get->setVariable("stockSold",$wine['TotalStockSold']);
    $get->setVariable("salesRevenue",$wine['TotalRevenue']);
    $get->addBlock("row");
  endforeach;
  
  $get->generateOutput();
}
 
function showerror() {
  die("Error " . mysql_errno() . " : " . mysql_error());
}
    
function displayWineList() {
  // Get the user data
  $wineName = $_GET['wineName'];
  $wineryName = $_GET['wineryName'];
  $regionName = $_GET['regionName'];
  $grapeVariety = $_GET['grapeVariety'];
  $yearTo = $_GET['yearTo'];
  $yearFrom = $_GET['yearFrom'];
  $minStock = $_GET['minStock'];
  $minOrder = $_GET['minOrder'];
  $costMin = $_GET['costMin'];
  $costMax = $_GET['costMax'];  
    
  //User input validations  
  if($yearFrom > $yearTo) {
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please start with a lower bound for the year"); 
      history.back();
    </script>
  <?php
  } else if(!is_numeric($minStock) && !empty($minStock)) {
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the stock"); 
      history.back();
    </script>
  <?php
  }else if(!is_numeric($minOrder) && !empty($minOrder)) {
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the order"); 
      history.back();
    </script>
  <?php
  } else if(!is_numeric($costMin) && !empty($costMin)) {
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the minimum cost"); 
      history.back();
    </script>
  <?php
  } else if(!is_numeric($costMax) && !empty($costMin)) {
  ?>
    <!--Pop up box if the user input is wrong  -->
    <script type="text/javascript">   
      alert("Please enter a number for the maximum cost"); 
      history.back();
    </script>
  <?php
  } else if ($costMin > $costMax) {
  ?>
    <!--Pop up box if the user input is wrong  -->
   <script type="text/javascript">   
      alert("Please start with a small number"); 
      history.back();
   </script>
  <?php
  }
  //No user input error
  else{
    // Connect to the server
    if (!($connection = @ mysql_connect(DB_HOST, DB_USER, DB_PW))) {
      showerror();
    }

    if (!mysql_select_db(DB_NAME, $connection)) {
      showerror();
    }

    // Start a query ...
    $query = "SELECT wine_name, variety, year, winery_name, region_name, cost, on_hand, SUM(items.qty) AS TotalStockSold, SUM(items.price) AS TotalRevenue 
    FROM winery, region, wine, items, inventory, grape_variety, wine_variety
    WHERE winery.region_id = region.region_id 
    AND wine.winery_id = winery.winery_id 
    AND wine_variety.wine_id = wine.wine_id 
    AND wine_variety.variety_id = grape_variety.variety_id 
    AND inventory.wine_id = wine.wine_id 
    AND items.wine_id = wine.wine_id";

    // if statements to check whether the user entered something in the field
    // Add filter to the search if something is inputted on the form.

    //1.Search by wine name
    if (isset($wineName) && $wineName != "" ) {
      $query .= " AND wine_name LIKE '%{$wineName}%'";
    }

    //2. Search by winery name
    if (isset($wineryName) && $wineryName != "") {
      $query .= " AND winery_name LIKE '%{$wineryName}%'";
    }

    //3. Search by region
    if (isset($regionName) && $regionName != "") {
      $query .= " AND region_name = '{$regionName}'";
    }

    //4. Search by grape variety
    if (isset($grapeVariety) && $grapeVariety != "") {
      $query .= " AND variety = '{$grapeVariety}'";
    }

    //5. Search by year
    if (isset($yearFrom) && isset($yearTo) && $yearFrom != "" && $yearTo != ""){
      $query .= " AND year BETWEEN '{$yearFrom}' AND '{$yearTo}'";
    }

    //6. Search by number of stock
    if (isset($minStock) && $minStock != "") {
      $query .= " AND inventory.on_hand >= '{$minStock}'";
    }

    //7. Search by cost range
    if (isset($costMin) && isset($costMax) && $costMin != "" && $costMax != "") {
      $query .= " AND cost >= '{$costMin}' AND cost <= '{$costMax}'";
    }

    //Search are done by grouping the wine_id and grape variety
    $query .= " GROUP BY wine.wine_id, variety ";
  
    //8. Search by minimum order
    if (isset($minOrder) && $minOrder != "") {
      $query .= " HAVING TotalStockSold  >= '{$minOrder}'";
    }

    //Search list are arrange by wine name
    $query .= " ORDER BY wine_name;";

    //Get the criteria query result
    $wineQuery = wineResult($query, $connection);
  }
  return $wineQuery;
}//end displayWineList function

//Get the criteria query output
function wineResult($query, $connection) {
    // Run the query on the server
  if (!($result = @ mysql_query ($query, $connection))) {
    showerror();
  }
  // Find out how many rows are available
  $rowsFound = @ mysql_num_rows($result);
  $winesArray = array();
  
  // If the query has results
  if ($rowsFound > 0) {
    // Displays how many rows were found/
    echo '<br><h1>Search Results</h1><br>';
    echo $rowsFound.' records found <br><br>';
    // Fetch each of the query rows
    while ($row = @ mysql_fetch_assoc($result)) {
      $winesArray[] = $row;
    } // end while loop body
  } // end if $rowsFound body
  else {
?>
  <!--Pop up box if the user input is wrong  -->
  <script type="text/javascript">   
    alert("Sorry, there are no records found."); 
    history.back();
  </script>
<?php
 }
  return $winesArray;
} // end wineResult function
?> 