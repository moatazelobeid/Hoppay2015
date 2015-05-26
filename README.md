# Hoopay Crawler
This is the implementation of a modified PHPCrawl to crawl the merchants websites. The scrapping is manually done, but since PHP can handle regular and normal expressions, I think it suits the problem.

## Installation and Execution
The main implementation is actually platform independent. But I've tested this only in Linux. To run in Windows, you need to change the *goMultiProcessed()* to just *go*. It won't have child workers, but you can debug in Windows. I've not tested it, but I think it can work.
The database script is inside the *support* dir. Don't call it directly, since there's the database creation as well. The full text search engine is inside the testing script, since I prefer to leave that in the development environment. 
Each merchant has it's script. It's actually an extension of the crawler main class, in which there's the scrapping mechanism. So you just need to call the script to run for that merchant. Remember that, although you can try to call from the webserver, it take several hours to run. So the best choice is to run from command line. Also, the scripts run infinitely to keep updating things all the time.
Logging and errors are written in standard output. Use shell redirections if you want to dump it somewhere else.

## Testing
The testing script is a very simple implementation of full search engine of PostgreSQL. The layout is horrible, and it doesn't separate anything. But there's the product link as well. Also, only the first 100 results are displayed, for testing and sanity questions.
