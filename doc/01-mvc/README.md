# Framework prototype

## Run

	$ cd BEAR.Resource/
	$ chmod 777 -R script/cache
	$ cd doc/01-mvc/htdocs
	$ php index.php --url Hello
	$ php index.php --url HelloAop

## Function

 * Basic MVC like module.
 * Cached object graph.
 * Parameter provider in Page. (@Provide)
 * Aspect oriented programing. (@Aspect, @Log)
 * View observe controller. (Controller doesn't know view)
 * Framework / Application separated module.
