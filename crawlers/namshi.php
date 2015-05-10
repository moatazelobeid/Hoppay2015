<?php

require("libs/PHPCrawler.class.php");

// Extend the class and override the handleDocumentInfo()-method
class MyCrawler extends PHPCrawler 
{
	const IdMerchant = 2;   // DON'T FORGET THIS!
	
	function handleDocumentInfo($DocInfo) 
	{
	//	echo "Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")\n";
	//	echo "Referer-page: ".$DocInfo->referer_url."\n";
		if(strpos($DocInfo->source,"Add to Cart"))
		{
			$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","Hoopay2015");
			$dbh->query("SET search_path TO Products");

			$url = $DocInfo->url;
			$temp = file_get_contents($url);

			$p1 = strpos($temp,"<div class=\"product-view-title\">");
			$p1 = strpos($temp,"<h1>",$p1);
			$p2 = strpos($temp,"</h1>",$p1);
			$title = trim(substr($temp,$p1+4,$p2-$p1-4));

			$p1 = strpos($temp,"<p class=\"old-price\">");
			$p1 = strpos($temp,"<span class=\"sign\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$oldprice = trim(substr($temp,$p1+19,$p2-$p1-19));
			$p1 = strpos($temp,"<p class=\"old-price\">");
			$p1 = strpos($temp,"<span class=\"digits\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$oldprice = $oldprice." ".trim(substr($temp,$p1+21,$p2-$p1-21));

			$p1 = strpos($temp,"<p class=\"special-price\">");
			$p1 = strpos($temp,"<span class=\"sign\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$newprice = trim(substr($temp,$p1+19,$p2-$p1-19));
			$p1 = strpos($temp,"<p class=\"special-price\">");
			$p1 = strpos($temp,"<span class=\"digits\">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$newprice = $newprice." ".trim(substr($temp,$p1+21,$p2-$p1-21));

			$p1 = strpos($temp,"<h3>Details</h3>");
			$p1 = strpos($temp,"<div class=\"std\">",$p1);
			$p2 = strpos($temp,"</div>",$p1);
			$description = trim(substr($temp,$p1+17,$p2-$p1-17));

			$p1 = strpos($temp,"<meta property=\"og:image\" content=\"");
			$p2 = strpos($temp,"\"/>",$p1);
			$image = trim(substr($temp,$p1+35,$p2-$p1-35));

			$sth = $dbh->prepare("DELETE FROM Products WHERE URL ILIKE :URL");
			$sth->bindValue(":URL",$url);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			$sth = $dbh->prepare("INSERT INTO Products (Name,Description,OldPrice,Price,URL,Image) VALUES (:Name,:Description,:OldPrice,:Price,:URL,:Image)");
			$sth->bindValue(":Name",$title);
			$sth->bindValue(":Description",$description);
			$sth->bindValue(":OldPrice",$oldprice);
			$sth->bindValue(":Price",$newprice);
			$sth->bindValue(":URL",$url);
			$sth->bindValue(":Image",$image);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			echo $URL." added\n\n";
		}
		flush();
	}
}

$crawler = new MyCrawler();

$crawler->setURL("http://namshi.com/ae/");

$crawler->addReceiveContentType("#text/html#");
$crawler->setCrawlingDepthLimit(4);

$crawler->setWorkingDirectory("./tmp/"); 
$crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);

$crawler->goMultiProcessed(30,PHPCrawlerMultiProcessModes::MPMODE_CHILDS_EXECUTES_USERCODE);
$report = $crawler->getProcessReport();
