<?php

require("libs/PHPCrawler.class.php");

class MyCrawler extends PHPCrawler 
{
	const IdMerchant = 2;   // DON'T FORGET THIS!
	
	function handleDocumentInfo($DocInfo) 
	{
		echo "(namshi) Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")\n";
		echo "(namshi) Referer-page: ".$DocInfo->referer_url."\n";
		if(strpos($DocInfo->source,"Add To Shopping Bag"))
		{
			$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","Hoopay2015");  // this can be exported as well
			$dbh->query("SET search_path TO Products");

			$url = $DocInfo->url;
			$temp = file_get_contents($url);

			$p1 = strpos($temp,"<div class=\"product_details\">");
			$p1 = strpos($temp,"<a",$p1);
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</a>",$p1);
			$title = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<div class=\"product_details\">");
			$p1 = strpos($temp,"<h2>",$p1);
			$p2 = strpos($temp,"</h2>",$p1);
			$title = $title." - ".trim(substr($temp,$p1+4,$p2-$p1-4));
			
			$p1 = strpos($temp,"<p class=\"price\">");
			$p1 = strpos($temp,"<span>",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$newprice = trim(substr($temp,$p1+6,$p2-$p1-6));

			$p1 = strpos($temp,"<div class=\"info_content");
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</div>",$p1);
			$description = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<li class=\"zoom_image");
			$p1 = strpos($temp,"<img src=\"",$p1);
			$p2 = strpos($temp,"\"",$p1+15);
			$image = trim(substr($temp,$p1+10,$p2-$p1-10));

			$sth = $dbh->prepare("DELETE FROM Products WHERE URL ILIKE :URL");
			$sth->bindValue(":URL",$url);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			$sth = $dbh->prepare("INSERT INTO Products (IdMerchant,Name,Description,Price,URL,Image,QueryDocument) VALUES (:IdMerchant,:Name::text,:Description::text,:Price,:URL,:Image,to_tsvector(:Name::text) || to_tsvector(:Description::text))");
			$sth->bindValue(":IdMerchant",self::IdMerchant);
			$sth->bindValue(":Name",$title);
			$sth->bindValue(":Description",$description);
			$sth->bindValue(":Price",$newprice);
			$sth->bindValue(":URL",$url);
			$sth->bindValue(":Image",$image);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

			echo $url." added\n\n";
		}
		flush();
	}
}

$crawler = new MyCrawler();

$crawler->setURL("https://en-sa.namshi.com/");

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

