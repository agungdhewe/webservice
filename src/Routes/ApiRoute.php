<?php declare(strict_types=1);
namespace AgungDhewe\Webservice\Routes;

use AgungDhewe\PhpLogger\Log;
use AgungDhewe\Webservice\IRouteHandler;
use AgungDhewe\Webservice\Service;
use AgungDhewe\Webservice\ServiceRoute;
use AgungDhewe\Webservice\WebApi;

class ApiRoute extends ServiceRoute implements IRouteHandler {
	function __construct(string $urlreq) {
		parent::__construct($urlreq); // contruct dulu parentnya
	}

	public function route(?array $param = []) : void {
		Log::info("Route Page $this->urlreq");


		ob_start();

		try {

			$urlreq = $this->urlreq;
			$classPath = null;
			$className = null;
			$functionName = null;

			$pattern = '/(?:api\/)((?:[^\/]+\/)+)([^\/]+)\/([^\/]+)$/';
			if (preg_match($pattern, $this->urlreq, $matches)) {
				$classPath = $matches[1] . $matches[2];
				$className = $matches[2];
				$functionName = $matches[3];
			}  else {
				throw new \Exception("Invalid endpoint $urlreq", 400);
			}

			// $urlreq = stripslashes($this->urlreq);
			
			$classPath = str_replace('/', '\\', $classPath);

			// cek apakah class ada
			if (!class_exists($classPath)) {
				Log::error("Class $classPath not found");
				throw new \Exception("Invalid endpoint $urlreq", 404);
			}

			// cek apakah class inherit dari WebApi
			if (!is_subclass_of($classPath, WebApi::class)) {
				Log::error("Class $classPath not inherit from WebApi");
				throw new \Exception("Invalid endpoint $urlreq", 500);
			}

			// cek apakah method ada
			if (!method_exists($classPath, $functionName)) {
				Log::error("Method $functionName not found in class $classPath");
				throw new \Exception("Invalid endpoint $urlreq", 404);
			}



			// data yang dikirim melalui POST
			$jsonData = file_get_contents('php://input');
			$receiveParameters = json_decode($jsonData, true);
			if (json_last_error() !== JSON_ERROR_NONE) {
				$errmsg = Log::error("Invalid request data: " . json_last_error_msg());
				throw new \Exception($errmsg, 400);
			}

			// cek apakah argument yang dikirim sesuai
			$executeParameters = [];
			$methodInfo = self::GetMethodInfo($classPath, $functionName);

			if (!$methodInfo['isApiMethod']) {
				Log::error("Method $functionName is not API method in class $classPath");
				throw new \Exception("Invalid endpoint $urlreq", 405);
			}

			$methodParameters = $methodInfo['parameters'];
			foreach ($methodParameters as $mp) {
				$name = $mp['name'];
				$type = $mp['type'];

				if (!array_key_exists($name, $receiveParameters)) {
					Log::error("Missing parameter '$name'");
					throw new \Exception("Missing one or more parameter(s) in request", 400);
				}


				$recvValue = $receiveParameters[$name];
				// $recvType = gettype($recvValue);
				if (!self::IsTypeMatch($type, $recvValue)) {
					Log::error("Invalid type for parameter '$name'. Expected '$type'");
					throw new \Exception("Invalid one or more parameter(s) in rerquest", 400);
				}

				$executeParameters[$name] = $recvValue; 
			}

			// eksekusi method
			$webApi = new $classPath();
			$webApi->webApiVerify($functionName);
			$ret = $webApi->$functionName(...array_values($executeParameters));


			$output = ob_get_contents();
			ob_end_clean();

			$result = [
				'code' => 0,
				'errormessage'=>'',
				'response'=>$ret,
				'output'=>$output
			];

			$ressponse = json_encode($result);

			header("HTTP/1.1 200 OK");
			header('Content-Type: application/json');
			echo $ressponse;


			
		} catch (\Exception $ex) {
			$output = ob_get_contents();
			ob_end_clean();

			$errCode = $ex->getCode();
			if ($errCode==0) {
				$errCode = 500;
				$httpErrorName = 'Internal Error';
			} else if (in_array($errCode, [400, 401, 403, 404, 405, 500])) {
				$httpErrorName = Service::HTTP_ERROR_LIST[$errCode][1];
			}

			$err = [
				'code' => $errCode,
				'errormessage' => $ex->getMessage(),
				'output'=>$output
			];

			$ressponse = json_encode($err);

			header("HTTP/1.1 $errCode $httpErrorName");
			header('Content-Type: application/json');
			echo $ressponse;
		}
	}


	public static function GetMethodInfo($className, $methodName) {
		try {
			

			$reflectionMethod = new \ReflectionMethod($className, $methodName);


			$parameters = $reflectionMethod->getParameters();
			$params = [];
			
			foreach ($parameters as $param) {
				$paramDetails = [
					'name' => $param->getName(),
					'type' => $param->getType() ? $param->getType()->getName() : null,
					'isOptional' => $param->isOptional(),
					'defaultValue' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null
				];
				$params[] = $paramDetails;
			}
			
			$isapi = false;
			$docComment = $reflectionMethod->getDocComment();
			if (!empty($docComment)) {
				if (strpos($docComment, '@ApiMethod') !== false) {
					$isapi = true;
				}
			}

			$method = [
				'name' => $methodName,
				'classname' => $className,
				'parameters' => $params,
				'isApiMethod' => $isapi
			];

			return $method;
		} catch (\ReflectionException $ex) {
			throw $ex;
		}
	}


	public static function IsTypeMatch($type, $data) {
		switch (strtolower($type)) {
			case 'string':
				return is_string($data);
			case 'integer':
			case 'int':
				return is_int($data);
			case 'float':
			case 'double':
				return is_float($data);
			case 'boolean':
			case 'bool':
				return is_bool($data);
			case 'array':
				return is_array($data);
			case 'object':
				return is_object($data);
			case 'null':
				return is_null($data);
			case 'resource':
				return is_resource($data);
			default:
				return false; // Tipe tidak dikenali
		}
	}
	
}

