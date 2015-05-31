<?php
/*
This will be our main example. The idea is very simple. The PHPCrawler will handle
the crawling thing, and the PHP will do the scrapping. Once you get the idea with
regular and normal search, scrapping from HTML is very easy. Especially since mostly 
of the webshops uses Magento or other platforms.
*/

//---------------------------------------------------------------------------------------------

// first, let's create our crawler extended from the main class
require("libs/PHPCrawler.class.php");

// Extend the class and override the handleDocumentInfo() method, which does the scrapping.
class MyCrawler extends PHPCrawler 
{
	const IdMerchant = 1;   // DON'T FORGET THIS! Hard-code the webshop ID, since this is done for everyone.
	
	function handleDocumentInfo($DocInfo) 
	{
		// output some information on the URL
		echo "(markavip) Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")\n";
		echo "(markavip) Referer-page: ".$DocInfo->referer_url."\n";
		
		// check if it's valid
		if(strpos($DocInfo->source,"Add to Cart"))
		{
			// connect to the DB using PDO
			$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","Hoopay2015");
			$dbh->query("SET search_path TO Products");

			// get the URL and the HTML code
			$url = $DocInfo->url;
			$temp = file_get_contents($url);

			// dig the title of the product
			$p1 = strpos($temp,"<div class=\"product-view-title\">");
			$p1 = strpos($temp,"<h1>",$p1);
			$p2 = strpos($temp,"</h1>",$p1);
			$title = trim(substr($temp,$p1+4,$p2-$p1-4));

			// same logic for price and special price, if avaliable
			$p1 = strpos($temp,"<p class=\"old-price\">");
			$p1 = strpos($temp,"<span class=\"sign\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$oldprice = trim(substr($temp,$p1+19,$p2-$p1-19));
			$p1 = strpos($temp,"<p class=\"old-price\">");
			$p1 = strpos($temp,"<span class=\"digits\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$oldprice = $oldprice." ".trim(substr($temp,$p1+21,$p2-$p1-21));

			// new price (like promotions)
			$p1 = strpos($temp,"<p class=\"special-price\">");
			$p1 = strpos($temp,"<span class=\"sign\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$newprice = trim(substr($temp,$p1+19,$p2-$p1-19));
			$p1 = strpos($temp,"<p class=\"special-price\">");
			$p1 = strpos($temp,"<span class=\"digits\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$newprice = $newprice." ".trim(substr($temp,$p1+21,$p2-$p1-21));

			// get the descripiton (choose one and get all, including HTML)
			$p1 = strpos($temp,"<h3>Details</h3>");
			$p1 = strpos($temp,"<div class=\"std\">",$p1);
			$p2 = strpos($temp,"</div>",$p1);
			$description = trim(substr($temp,$p1+17,$p2-$p1-17));

			// get the image link of the product (later one could add the image to the database)
			$p1 = strpos($temp,"<meta property=\"og:image\" content=\"");
			$p2 = strpos($temp,"\"/>",$p1);
			$image = trim(substr($temp,$p1+35,$p2-$p1-35));

			// now, clear the product from the database if the URL exists
			$sth = $dbh->prepare("DELETE FROM Products WHERE URL ILIKE :URL");
			$sth->bindValue(":URL",$url);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			// prepare the statement and insert the product in the database
			$sth = $dbh->prepare("INSERT INTO Products (IdMerchant,Name,Description,OldPrice,Price,URL,Image,QueryDocument) VALUES (:IdMerchant,:Name::text,:Description::text,:OldPrice,:Price,:URL,:Image,to_tsvector(:Name::text) || to_tsvector(:Description::text))");
			$sth->bindValue(":IdMerchant",self::IdMerchant);
			$sth->bindValue(":Name",$title);
			$sth->bindValue(":Description",$description);
			$sth->bindValue(":OldPrice",$oldprice);
			$sth->bindValue(":Price",$newprice);
			$sth->bindValue(":URL",$url);
			$sth->bindValue(":Image",$image);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			// if we got here without dying, then we're good to go
			echo $url." added\n\n";
		}
		
		// just flush buffer so we can keep up the progress
		flush();
	}
}

//---------------------------------------------------------------------------------------------

// Now, to the implementation. First, create the object.
$crawler = new MyCrawler();

// Now, set the URL. Try to be specified about targeting, because sometimes the main page does some weird redirections.
$crawler->setURL("http://markavip.com/ae/");

// Let's filter only HTML and the deep to 4 (enough for our example).
$crawler->addReceiveContentType("#text/html#");
$crawler->setCrawlingDepthLimit(4);

// We need a temp dir. Could be /tmp, but I like to keep things together.
$crawler->setWorkingDirectory("./tmp/"); 
$crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);

// This is used to resume old crawlings. Since we're doing this in a loop, I don't recommend.
/*
$crawler->enableResumption(); 
if (!file_exists("./tmp/markavip_id.tmp")) 
{ 
  $crawler_ID = $crawler->getCrawlerId(); 
  file_put_contents("./tmp/markavip_id.tmp", $crawler_ID); 
} 
else 
{ 
  $crawler_ID = file_get_contents("./tmp/markavip_id.tmp"); 
  $crawler->resume($crawler_ID);
} 
*/

// now, loop until the end of the days.
while(true)
{
	// go to the crawling, 30 workers only, and don't abstract the child objects (so each child has it's own PDO objects).
	$crawler->goMultiProcessed(5,PHPCrawlerMultiProcessModes::MPMODE_CHILDS_EXECUTES_USERCODE);
	
	// just print out some fancy messages of status
	$report = $crawler->getProcessReport();
	
	// wait 15min before going again
	sleep(15 * 60);
}


