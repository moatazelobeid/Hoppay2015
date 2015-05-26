<?php

require("libs/PHPCrawler.class.php");

class MyCrawler extends PHPCrawler 
{
	const IdMerchant = 3;   // DON'T FORGET THIS!
	
	function handleDocumentInfo($DocInfo) 
	{
		echo "Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")\n";
		echo "Referer-page: ".$DocInfo->referer_url."\n";
		if(strpos($DocInfo->source,"Add to Cart"))
		{
			$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","Hoopay2015");
			$dbh->query("SET search_path TO Products");

			$url = $DocInfo->url;
			$temp = file_get_contents($url);

			$p1 = strpos($temp,"<h1 itemprop=\"name");
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</h1>",$p1);
			$title = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<h3 class=\"subhead was");
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</h",$p1);
			$oldprice = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<h3 class=\"price\">");
			$p1 = strpos($temp,"</small",$p1);
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"<small",$p1);
			$newprice = trim(substr($temp,$p1+1,$p2-$p1-1));
			$p1 = strpos($temp,"currency-text\"",$p1);
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</sm",$p1);
			$newprice = $newprice." ".trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<div id=\"desc-short\"");
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</div>",$p1);
			$description = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<meta itemprop=\"image\" content");
			$p1 = strpos($temp,"\"",$p1);
			$p2 = strpos($temp,"\"",$p1);
			$image = trim(substr($temp,$p1+1,$p2-$p1-1));

			$sth = $dbh->prepare("DELETE FROM Products WHERE URL ILIKE :URL");
			$sth->bindValue(":URL",$url);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			$sth = $dbh->prepare("INSERT INTO Products (IdMerchant,Name,Description,OldPrice,Price,URL,Image) VALUES (:IdMerchant,:Name,:Description,:OldPrice,:Price,:URL,:Image)");
			$sth->bindValue(":IdMerchant",self::IdMerchant);
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

$crawler->setURL("http://uae.souq.com/ae-en/");

$crawler->addReceiveContentType("#text/html#");
$crawler->setCrawlingDepthLimit(4);

$crawler->setWorkingDirectory("./tmp/"); 
$crawler->setUrlCacheType(PHPCrawlerUrlCacheTypes::URLCACHE_SQLITE);

while(true)
{
	$crawler->goMultiProcessed(5,PHPCrawlerMultiProcessModes::MPMODE_CHILDS_EXECUTES_USERCODE);
	$report = $crawler->getProcessReport();
	sleep(15 * 60);
}
