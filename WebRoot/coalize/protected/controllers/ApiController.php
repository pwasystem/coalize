<?php
class ApiController extends Controller
{
    // Members
    /**
     * Key which has to be in HTTP USERNAME and PASSWORD headers 
     */
    Const APPLICATION_ID = 'ASCCPE';

    /**
     * Default response format
     * either 'json' or 'xml'
     */
    private $format = 'json';
    /**
     * @return array action filters
     */
    public function filters()
    {
            return array();
    }

    // Actions
	
	//List rest
    public function actionList()
	{
		//Solicita autorização
		$this->_checkAuth();
		
		// Get the respective model instance
		switch($_GET['model'])
		{
			//lista clientes
			case 'clientes':
				if (isset($_GET['offset'])) {
					$models = Cliente::model()->findAll(
						array(
							'limit' => $_GET['limit'],
							'offset' => $_GET['offset']-1,
						)
					);
				//Retorna lista completa
				} else {
					$models = Cliente::model()->findAll();
				}
				break;
				
			//lista produtos
			case 'produtos':
				if (isset($_GET['cliente'])){
					$models = Produto::model()->findAll(
						'cliente_id=:cliente_id',
						array(':cliente_id' => $_GET['cliente']));
				
				//Retorna dados com paginação
				} elseif (isset($_GET['offset'])) {
					$models = Produto::model()->findAll(
						array(
							'limit' => $_GET['limit'],
							'offset' => $_GET['offset']-1,
						)
					);
				//Retorna lista completa
				} else {
					$models = Produto::model()->findAll();					
				}
				break;
			default:
				// Model not implemented error
				$this->_sendResponse(501, sprintf(
					'Error: Mode <b>list</b> is not implemented for model <b>%s</b>',
					$_GET['model']) );
				Yii::app()->end();
		}
		// Did we get some results?
		if(empty($models)) {
			// No
			$this->_sendResponse(200, 
					sprintf('No items where found for model <b>%s</b>', $_GET['model']) );
		} else {
			// Prepare response
			$rows = array();
			foreach($models as $model)
				$rows[] = $model->attributes;
			// Send the response
			$this->_sendResponse(200, CJSON::encode($rows));
		}
	}
	
	public function actionView()
	{
		//Solicita autorização
		$this->_checkAuth();
		
		// Check if id was submitted via GET
		if(!isset($_GET['id']))
			$this->_sendResponse(500, 'Error: Parameter <b>id</b> is missing' );

		switch($_GET['model'])
		{
			// Find respective model    
			case 'cliente':
				$model = Cliente::model()->findByPk($_GET['id']);
				break;
			case 'produto':
				$model = Produto::model()->findByPk($_GET['id']);
				break;
			default:
				$this->_sendResponse(501, sprintf(
					'Mode <b>view</b> is not implemented for model <b>%s</b>',
					$_GET['model']) );
				Yii::app()->end();
		}
		// Did we find the requested model? If not, raise an error
		if(is_null($model))
			$this->_sendResponse(404, 'No Item found with id '.$_GET['id']);
		else
			$this->_sendResponse(200, CJSON::encode($model));
	}

    public function actionCreate()
	{
		//Solicita autorização
		$this->_checkAuth();
		
		switch($_GET['model'])
		{
			// Get an instance of the respective model
			case 'cliente':
				$model = new Cliente;                    
				break;
			case 'produto':
				$model = new Produto;                    
				break;
			default:
				$this->_sendResponse(501, 
					sprintf('Mode <b>create</b> is not implemented for model <b>%s</b>',
					$_GET['model']) );
					Yii::app()->end();
		}
		// Try to assign POST values to attributes
		foreach($_POST as $var=>$value) {
			// Does the model have this attribute? If not raise an error
			if($model->hasAttribute($var))
				$model->$var = $value;
			else
				$this->_sendResponse(500, 
					sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>', $var,
					$_GET['model']) );
		}
		// Try to save the model
		if($model->save())
			$this->_sendResponse(200, CJSON::encode($model));
		else {
			// Errors occurred
			$msg = "<h1>Error</h1>";
			$msg .= sprintf("Couldn't create model <b>%s</b>", $_GET['model']);
			$msg .= "<ul>";
			foreach($model->errors as $attribute=>$attr_errors) {
				$msg .= "<li>Attribute: $attribute</li>";
				$msg .= "<ul>";
				foreach($attr_errors as $attr_error)
					$msg .= "<li>$attr_error</li>";
				$msg .= "</ul>";
			}
			$msg .= "</ul>";
			$this->_sendResponse(500, $msg );
		}
	}
    
    public function actionUpdate()
	{
		//Solicita autorização
		$this->_checkAuth();
		
		// Parse the PUT parameters. This didn't work: parse_str(file_get_contents('php://input'), $put_vars);
		$json = file_get_contents('php://input'); //$GLOBALS['HTTP_RAW_POST_DATA'] is not preferred: http://www.php.net/manual/en/ini.core.php#ini.always-populate-raw-post-data
		$put_vars = CJSON::decode($json,true);	//true means use associative array

		switch($_GET['model'])
		{
			// Find respective model
			case 'produto':
				$model = Produto::model()->findByPk($_GET['id']);                    
				break;
			case 'cliente':
				$model = Cliente::model()->findByPk($_GET['id']);                    
				break;
			default:
				$this->_sendResponse(501, 
					sprintf( 'Error: Mode <b>update</b> is not implemented for model <b>%s</b>',
					$_GET['model']) );
				Yii::app()->end();
		}
		// Did we find the requested model? If not, raise an error
		if($model === null)
			$this->_sendResponse(400, 
					sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.",
					$_GET['model'], $_GET['id']) );
			
		// Try to assign PUT parameters to attributes
		foreach($put_vars as $var=>$value) {
			// Does model have this attribute? If not, raise an error
			if($model->hasAttribute($var))
				$model->$var = $value;
			else {
				$this->_sendResponse(500, 
					sprintf('Parameter <b>%s</b> is not allowed for model <b>%s</b>',
					$var, $_GET['model']) );
			}
		}
		// Try to save the model
		if($model->save())
			$this->_sendResponse(200, CJSON::encode($model));
		else
			// prepare the error $msg
			// see actionCreate
			// ...
			$this->_sendResponse(500, $msg );
	}

	protected function beforeSave()
	{
		//...
		// author_id may have been posted via API POST
		if(is_null($this->author_id) or $this->author_id=='')
			$this->author_id=Yii::app()->user->id;
		//...
	}

	public function actionDelete()
	{
		//Solicita autorização
		$this->_checkAuth();
		
		switch($_GET['model'])
		{
			// Load the respective model
			case 'produto':
				$model = Produto::model()->findByPk($_GET['id']);                    
				break;
			case 'cliente':
				$model = Cliente::model()->findByPk($_GET['id']);                    
				break;
			default:
				$this->_sendResponse(501, 
					sprintf('Error: Mode <b>delete</b> is not implemented for model <b>%s</b>',
					$_GET['model']) );
				Yii::app()->end();
		}
		// Was a model found? If not, raise an error
		if($model === null)
			$this->_sendResponse(400, 
					sprintf("Error: Didn't find any model <b>%s</b> with ID <b>%s</b>.",
					$_GET['model'], $_GET['id']) );

		// Delete the model
		$num = $model->delete();
		if($num>0)
			$this->_sendResponse(200, $num);	//this is the only way to work with backbone
		else
			$this->_sendResponse(500, 
					sprintf("Error: Couldn't delete model <b>%s</b> with ID <b>%s</b>.",
					$_GET['model'], $_GET['id']) );
	}
	
	
	private function _sendResponse($status = 200, $body = '', $content_type = 'text/html')
	{
		// set the status
		$status_header = 'HTTP/1.1 ' . $status . ' ' . $this->_getStatusCodeMessage($status);
		header($status_header);
		// and the content type
		header('Content-type: ' . $content_type);

		// pages with body are easy
		if($body != '')
		{
			// send the body
			echo $body;
		}
		// we need to create the body if none is passed
		else
		{
			// create some body messages
			$message = '';

			// this is purely optional, but makes the pages a little nicer to read
			// for your users.  Since you won't likely send a lot of different status codes,
			// this also shouldn't be too ponderous to maintain
			switch($status)
			{
				case 401:
					$message = 'You must be authorized to view this page.';
					break;
				case 404:
					$message = 'The requested URL ' . $_SERVER['REQUEST_URI'] . ' was not found.';
					break;
				case 500:
					$message = 'The server encountered an error processing your request.';
					break;
				case 501:
					$message = 'The requested method is not implemented.';
					break;
			}

			// servers don't always have a signature turned on 
			// (this is an apache directive "ServerSignature On")
			$signature = ($_SERVER['SERVER_SIGNATURE'] == '') ? $_SERVER['SERVER_SOFTWARE'] . ' Server at ' . $_SERVER['SERVER_NAME'] . ' Port ' . $_SERVER['SERVER_PORT'] : $_SERVER['SERVER_SIGNATURE'];

			// this should be templated in a real-world solution
			$body = '
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
	<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<title>' . $status . ' ' . $this->_getStatusCodeMessage($status) . '</title>
	</head>
	<body>
		<h1>' . $this->_getStatusCodeMessage($status) . '</h1>
		<p>' . $message . '</p>
		<hr />
		<address>' . $signature . '</address>
	</body>
	</html>';

			echo $body;
		}
		Yii::app()->end();
	}
	
	private function _getStatusCodeMessage($status)
	{
		// these could be stored in a .ini file and loaded
		// via parse_ini_file()... however, this will suffice
		// for an example
		$codes = Array(
			200 => 'OK',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'Payment Required',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
		);
		return (isset($codes[$status])) ? $codes[$status] : '';
	}

	private function _checkAuth()
	{
		if(!$_SERVER['HTTP_AUTHORIZATION']){			
			// Check if we have the USERNAME and PASSWORD HTTP headers set?
			if(!(isset($_SERVER['HTTP_X_USERNAME']) and isset($_SERVER['HTTP_X_PASSWORD']))) {
				// Error: Unauthorized
				$this->_sendResponse(401);
			}
			$username = $_SERVER['HTTP_X_USERNAME'];
			$password = base64_decode($_SERVER['HTTP_X_PASSWORD']);
			
			// Find the user
			$user=User::model()->find('LOWER(username)=?',array(strtolower($username)));

			if($user===null) {
				// Error: Unauthorized
				$this->_sendResponse(401, 'Error: User Name is invalid');
			} else if($user->password!=$password) {
				// Error: Unauthorized
				$this->_sendResponse(401, 'Error: User Password is invalid');
			} else {
				//cria token
				//echo $user->hashPassword($password);
				$token = $user->hashPassword($password);
				Yii::app()->user->setState('token', $token);
				header("Authorization: $token");			
			}
		} else {
			$authorization = str_replace('Bearer ','',$_SERVER['HTTP_AUTHORIZATION']);
			$token = Yii::app()->user->token;			
			if($token!=$authorization)
				// Error: Unauthorized
				$this->_sendResponse(401, "Error: Invalid Authorization Bearer");
		}
	}
//$this->_checkAuth();


}
?>