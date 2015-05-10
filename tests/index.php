<html>
<body>
<form method="post" action="">
<p>Search: <input type="text" size="100" maxlength="255" name="Search"> <input type="submit" value="SEARCH">
</form>

<?php
$search = (isset($_POST["Search"])) ? trim($_POST["Search"]) : "";

if(!empty($search))
{
	$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","darkstar");
	$dbh->query("SET search_path TO Products");
	
	$sth = $dbh->prepare("SELECT * FROM Products WHERE Name ILIKE '%:Name%'");
	$sth->bindValue(":Name",$search);
	$sth->execute();
	while($row = $sth->fetch(PDO::FETCH_ASSOC))
	{
		?>
		<div style="width: 100%; border: 1px solid black;">
		<p><b>Product:</b> <?php echo stripslashes(htmlentities($row["name"])); ?>
		<p><b>Description:</b> <?php echo $row["description"]; ?>
		<p><b>Old Price:</b> <?php echo stripslashes(htmlentities($row["oldprice"])); ?>
		<p><b>Price:</b> <?php echo stripslashes(htmlentities($row["price"])); ?>
		<p><b>Image:</b> <img src="<?php echo $row["image"]; ?>">
		<p><b>URL:</b> <a target="_blank" href="<?php echo $row["url"]; ?>">
		</div>
		<?php
	}
	
}
?>
</body>
</html>