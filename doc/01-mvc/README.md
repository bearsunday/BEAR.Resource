# Framework prototype

## Run
	$ git clone git://github.com/koriym/BEAR.Resource.git
	$ cd BEAR.Resource/
	$ git submodule update --init
	$ chmod -R 777 doc/01-mvc/script/cache/
	$ cd doc/01-mvc/htdocs
	$ php index.php --url Hello
	$ php index.php --url HelloAop

## Result
    Content-Type: text/html; charset=UTF-8
    <html>
    <body>Hello World</body>
    </html>
    
    [Log] target = helloworld\ResourceObject\Greeting\Aop, input = Array, result = Hello World
    Content-Type: text/html; charset=UTF-8
    <html>
    <body>Hello World</body>
    </html>
    
## Function

 * Basic MVC like module.
 * Entire object graph is cached.
 * Parameter provider in Page. (@Provide)
 * Aspect oriented programing. (@Aspect, @Log)
 * View observe controller.
 * Framework / Application separated module.
