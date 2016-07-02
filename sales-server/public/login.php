<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/login', function (Request $request, Response $response) {

	try{

		$user = (object)$request->getParsedBody();

		if (empty($user->email)){
			throw new \Exception("Email empty...");
		}
		if (empty($user->password)){
			throw new \Exception("Password empty...");
		}

		if ($user->create_account){
//Criar a conta do usuário
			if (empty($user->name)){
				throw new \Exception("Name empty...");
			}
//Verifica se o email já está cadastrado
			$sql = "SELECT id FROM users WHERE email=:email";
			$stmt = DB::prepare($sql);
			$stmt->bindParam("email",$user->email);
			$stmt->execute();

			if ($stmt->rowCount()==0){

				$isEmployee = $user->role=="Employee";
				$isCustomer = $user->role=="Customer";
				$password = md5($user->password);

				$sql = "INSERT INTO users (name,email,password,isEmployee,isCustomer) VALUES (:name,:email,:password,:isEmployee,:isCustomer)";
				$stmt = DB::prepare($sql);
				$stmt->bindParam(':name', $user->name);
				$stmt->bindParam(':email', $user->email);
				$stmt->bindParam(':password', $password);
				$stmt->bindParam(':isEmployee', $isEmployee,PDO::PARAM_INT);
				$stmt->bindParam(':isCustomer', $isCustomer,PDO::PARAM_INT);
				$stmt->execute();

				$user->id = DB::lastInsertId();
                $user->password = "";

				$response->withJson($user);

			}else{
				throw new \Exception("Email exists in database");
			}

		}
		else{
//Tenta logar
		}

	}
	catch(\Exception $e){
		return $response->withStatus(500)->write($e->getMessage());
	}

	return $response;
}
);