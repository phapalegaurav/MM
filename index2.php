<?php 
/*
 * 
 * Here, I've kept all sampel code which are unrelated to MM.
 * These samples can be referred as and when needed.
 * 
// ********************************** TEST **********************************************

// handle GET request for /articles
$app->get('/articles', function () use ($app) {
	$articles = R::find('articles');
	$app->response()->header('Content-Type', 'application/json');
	echo json_encode(R::exportAll($articles));
});


// handle GET requests for /articles/:id
$app->get('/articles/:id', function ($id) use ($app) {    
  try {
    // query database for single article
    $article = R::findOne('articles', 'id=?', array($id));
    
    if ($article) {
      // if found, return JSON response
      $app->response()->header('Content-Type', 'application/json');
      echo json_encode(R::exportAll($article));
    } else {
      // else throw exception
      throw new ResourceNotFoundException();
    }
  } catch (ResourceNotFoundException $e) {
    // return 404 server error
    $app->response()->status(404);
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});


// handle POST requests to /articles
$app->post('/articles', function () use ($app) {    
  try {
    // get and decode JSON request body
    $request = $app->request();
    $body = $request->getBody();
    $input = json_decode($body); 
    
    error_log($body);
    error_log(print_r($input, true));

    // store article record
    $article = R::dispense('articles');
    $article->title = (string)$input->title;
    $article->url = (string)$input->url;
    $article->date = (string)$input->date;
    $id = R::store($article);    
    
    // return JSON-encoded response body
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(R::exportAll($article));
  } catch (Exception $e) {
    $app->response()->status(400);
    $app->response()->header('X-Status-Reason', $e->getMessage());
  }
});


$app->get('/create-student', function () use($app){
	$student = R::dispense('student');
	$student->id = 1;
	$student->name = 'Foo';
	$student->age = 20;
	$id = R::store($student);

	$app->response()->header('Content-Type', 'application/json');
	echo json_encode(R::load('student', $id));
});

function mw1() {
  echo "This is middleware1";
}

function mw2() {
  echo "This is middleware2";
}

$app->get('/books/:one/:two', 'mw1', 'mw2', function ($one, $two) {
    echo "<br>The first paramter is " . $one;
    echo "<br>The second parameter is " . $two;
});

$app->get('/archive(/:year(/:month(/:day)))', function ($year = 2010, $month = 12, $day = 05) {
    echo sprintf('%s-%s-%s', $year, $month, $day);
});

$app->get('/old', function () use ($app) {
    //$params = $app->request()->params('foo');
    //error_log(print_r($params, true));
    $app->redirect('/new', 301);
});

$app->get('/new' , function () use ($app) {
    $host_with_port = $app->request()->getHostWithPort();
    print_r("Host with port " . print_r($host_with_port, true));
    echo "Hello world";
    //$app->response()->setBody("Hello World"); // DOES NOT work
    print_r($app->response()->finalize());
    //$app->stop();
    echo "But not this";
});

*/
?>