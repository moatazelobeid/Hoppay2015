<?php

require("libs/PHPCrawler.class.php");

class MyCrawler extends PHPCrawler 
{
	const IdMerchant = 4;   // DON'T FORGET THIS!
	
	function handleDocumentInfo($DocInfo) 
	{
		echo "(wysada) Page requested: ".$DocInfo->url." (".$DocInfo->http_status_code.")\n";
		echo "(wysada) Referer-page: ".$DocInfo->referer_url."\n";
		if(strpos($DocInfo->source,"Add to Cart"))
		{
			$dbh = new PDO("pgsql:host=localhost;dbname=Hoopay;port=5432;","postgres","Hoopay2015");
			$dbh->query("SET search_path TO Products");

			$url = $DocInfo->url;
			$temp = file_get_contents($url);

			$p1 = strpos($temp,"<h1 class=\"prod-info--name");
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</h1>",$p1);
			$title = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<p class=\"old-price\">");
			$p1 = strpos($temp,"<span class=\"price\" ",$p1);
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$oldprice = trim(substr($temp,$p1+1,$p2-$p1-1));
			
			$p1 = strpos($temp,"<p class=\"special-price\">");
			$p1 = strpos($temp,"<span class=\"price\" ",$p1);
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</span>",$p1);
			$newprice = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"<div class=\"prod-desc--copy");
			$p1 = strpos($temp,">",$p1);
			$p2 = strpos($temp,"</div>",$p1);
			$description = trim(substr($temp,$p1+1,$p2-$p1-1));

			$p1 = strpos($temp,"rel=\"image_gallery\"");
			$p1 = strpos($temp,"<img ",$p1);
			$p1 = strpos($temp,"src=\"",$p1);
			$p2 = strpos($temp,"\"",$p1+15);
			$image = trim(substr($temp,$p1+5,$p2-$p1-5));

			$sth = $dbh->prepare("DELETE FROM Products WHERE URL ILIKE :URL");
			$sth->bindValue(":URL",$url);
			$sth->execute();
			if($sth->errorCode() != 0) die("! erro linha: ".__LINE__."\n".$sth->errorInfo()[2]);

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

			echo $URL." added\n\n";
		}
		flush();
	}
}

$crawler = new MyCrawler();

$crawler->setURL("http://wysada.com/en/");

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
