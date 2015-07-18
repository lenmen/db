<h1>Db Connector </h1>
<i>Make you're sql queries easier to deal with. The class use PDO and is in the moment low on functions but 
in the future there will be more usefull functions within it. The class is at the moment incompleet.</i><br>

This class has a GNU GLP 3 licence

<h3>How to use</h3>

--- 

   include('class/db.php);

    // new object
    $con = ['dbname', 'username', 'password'];
    $object = new db($con, $options); // Con has an array with the db settings
    
    $object->from("table");

    $object->join('table2', 'table2.id = table.id', 'LEFT'); // Create a left join
    
    $params = array(":id" => 1);
    $object->where('WHERE id = :id', $params);

    $results = $object->get(); // get the results back as an array or as one object record

---