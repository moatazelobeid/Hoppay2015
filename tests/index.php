<?php

/*

This is a test to use full search engine from PostgreSQL. I won't enter in the details
of the engine, but here are some good links:

http://www.postgresql.org/docs/9.4/static/textsearch.html
http://blog.lostpropertyhq.com/postgres-full-text-search-is-good-enough/

I could insert the search query in the database, but I prefer to leave in the code, since
it's better to the developer, and the dba doesn't need to worry about it.

Also, the query will limit results to 100, just for the sake.

Nothing else here is really relevant. :-)

*/

// check for required search
$search = (isset($_POST["Search"])) ? trim($_POST["Search"]) : "";

// header
echo '
<html>
<body>
<form method="post" action="">
<p>Search: <input type="text" size="100" maxlength="255" name="Search" value="'.$search.'"> <input type="submit" value="SEARCH">
</form>
';

// show results?
if(!empty($search))
{
	// connect to the PDO
	$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","Hoopay2015");
	$dbh->query("SET search_path TO Products");
	
	// prepare the statement with the search engine
	$sth = $dbh->prepare("
		SELECT Products.Name,Products.Description,Products.OldPrice,Products.Price,Products.Image,Products.URL
		FROM Products WHERE Products.QueryDocument @@ to_tsquery(:SearchCriteria)
		ORDER BY Products.Name LIMIT 100
	");
	$sth->bindValue(":SearchCriteria","%".$search."%");
	$sth->execute();
	
	// display results
	while($row = $sth->fetch(PDO::FETCH_ASSOC))
	{
		echo "
		<div style=\"width: 100%; border: 1px solid black;\">
		<p><b>Product:</b> ".$row["name"]."
		<p><b>Description:</b>".$row["description"]."
		<p><b>Old Price:</b> ".$row["oldprice"]."
		<p><b>Price:</b> ".$row["price"]."
		<p><b>Image:</b> <img src=\"".$row["image"]."\">
		<p><b>URL:</b> <a target=\"_blank\" href=\"".$row["url"]."\">".$row["url"]."</a>
		</div>
		<p>&nbsp;
		";
	}
	
}

// footer
echo '
</body>
</html>
';