<?php
	namespace App\services;
	use Firebase\JWT\Key;
	use Firebase\JWT\JWT;

	class Token{
		public static function decodificar($token){
			if (str_starts_with($token, "Bearer")) {
                    $token = substr($token, 7); 
                }
                $decodificado = JWT::decode($token,new Key('abc123','HS256'));
                $id = $decodificado->data->id;
            return $id;
		}
		public static function codificar(int $id,int $exp){
			$playload = ['data'=>[
            'id'=> $id,
            ],
            'exp'=>$exp
           ];
           $key = 'abc123';
           $token = JWT::encode($playload,$key,'HS256');
           return $token;
		} 

	}
?>