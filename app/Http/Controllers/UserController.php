<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\User;

class UserController extends Controller
{
	public function __construct(){
		$this->middleware('api.superadmin')->except(['login']);
	}
	// =================================================================================
	// =====================Función para el login de la aplicación======================
	// =================================================================================
    public function login(Request $request){
    	$jwtAuth = new \JwtAuth();
    	// Recibir los datos por POST
    	$json = $request->input('json', null);
    	$params = json_decode($json);
    	$params_array = json_decode($json, true);

    	if(!empty($params_array)){
    		// Validar los datos recibidos
    		$validate = \Validator::make($params_array, [
    			'user_alias'	=> 'required',
    			'password'		=> 'required'
    		]);
    		if($validate->fails()){    			
                $data = array(
                    'status' 	=> 'error',
                    'code'		=> 400,
                    'message'	=> 'La validación de los datos ha fallado.',
                    'errors'	=> $validate->errors()
                );
    		} else{
    			// Cifrar la contraseña
    			$password_hashed = hash('SHA256', $params->password);

    			// Devolver el token o los datos
    			if(!empty($params->gettoken)){
                    $data = $jwtAuth->signup($params->user_alias, $password_hashed, true);
                    if($data){
                    	$data = array(
                    		'status'	=> 'success',
                    		'code'		=> 200,
                    		'identity'	=> $data 
                    	);
                    }
    			} else{
                    $data = $jwtAuth->signup($params->user_alias, $password_hashed);
                    if($data){
                    	$data = array(
                    		'status'	=> 'success',
                    		'code'		=> 200,
                    		'token'		=> $data 
                    	);
                    } else{
                    	$data = array(
                    		'status'	=> 'error',
                    		'code'		=> 401,
                    		'message'		=> '*Los datos que ha ingresado son incorrectos.' 
                    	);
                    }
                }
    		}
    	} else{    		
            $data = array(
                'status'	=> 'error',
                'code'		=> 411,
                'message'	=> 'Ha ingrasado los datos de manera incorrecta o incompletos'
            );
    	}
    	// Devolver respuesta
        return response()->json($data, $data['code']);
    }

	// =================================================================================
	// =====================Función para visualizar la información======================
	// =================================================================================
    public function index(Request $request){
		// Obtener los datos del usuario de la BD
		$users = User::all();
		if( is_object($users) && sizeof($users)!=0 ){
			$data = array(
				'status'	=> 'success',
				'code'		=> 200,
				'users'		=> $users
			);
		} else {
			$data = array(
				'status'	=> 'error',
				'code'		=> 400,
				'users'		=> 'No existen usuarios actualmente en la base de datos'
			);
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }

    public function show($id, Request $request){
		// Obtener el usuario que se busca por el id
		$user = User::find($id);

		if(is_object($user)){
			$data = array(
				'status' => 'success',
				'code' => 200,
				'user' => $user
			);
		} else{
			$data = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'El Usuario con id: '.$id.' no existe.'
			);
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }

	// =================================================================================
	// =====================Función para almacenar la información=======================
	// =================================================================================
    public function store(Request $request){
		// Recoger los datos por POST
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			// Validar los datos
			$validate = \Validator::make($params_array, [
				'user_alias'	=> 'required|unique:users',
				'user_type'     => 'required|numeric',
				'password'		=> 'required',
				'name'			=> 'required|regex:/^[\pL\s\-]+$/u',
				'role'			=> 'required'
			]);
			if($validate->fails()){
				$data = array(
					'status' 	=> 'error',
					'code'		=> 400,
					'message'	=> 'La validación de los datos ha fallado',
					'errors'	=> $validate->errors()
				);
			} else{
				// Cifrar la contraseña
				$password_hashed = hash('SHA256', $params->password);

				// Guardar el usuario nuevo
				$user = new User();
				$user->user_alias = $params->user_alias;
				$user->user_type = $params->user_type;
				$user->password = $password_hashed;
				$user->name = $params->name;
				$user->role = $params->role;

				$user->save();

				$data = array(
					'status' 	=> 'success',
					'code' 		=> 200,
					'message'	=> 'El usuario '.$user->user_alias.' se ha guardado correctamente.',
					'user'		=> $user
				);
			}
		} else{
			$data = array(
				'status' => 'error',
				'code' => 411,
				'message' => 'Ha ingrasado los datos de manera incorrecta o incompletos'
			);
		}
    	// Devolver respuesta
    	return response()->json($data, $data['code']);
    }

	// =================================================================================
	// =====================Función para actualizar la información======================
	// =================================================================================
    public function update($id, Request $request){
		// Recoger los datos por POST
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			// Validar los datos
			$validate = \Validator::make($params_array, [
				'user_alias' => 'required|unique:users,user_alias,'.$id,
				'user_type'     => 'required|numeric',
				'name' => 'required|regex:/^[\pL\s\-]+$/u',
				'role' => 'required'
			]);
			if($validate->fails()){
				$data = array(
					'status' => 'error',
					'code' => 400,
					'message' => 'La validación de los datos ha fallado.',
					'errors' => $validate->errors()
				);    
			} else{
				// Retirar el contenido que no se desea actualizar
				unset($params_array['id']);
				unset($params_array['password']);
				unset($params_array['created_at']);
				unset($params_array['updated_at']);

				// Actualizar el usuario en la BD
				$user = User::where('id', $id)->update($params_array);
				if($user != 0){
					$data = array(
						'status' => 'success',
						'code' => 200,
						'message' => 'El Usuario '.$params->user_alias.' se ha actualizado correctamente.',
						'changes' => $params_array
					);
				} else {
					$data = array(
						'status' => 'error',
						'code' => 404,
						'message' => 'No se ha podido actualizar el Usuario: '.$params->user_alias
					);  
				}
			}
		} else{    			
			$data = array(
				'status' => 'error',
				'code' => 411,
				'message' => 'Ha ingrasado los datos de manera incorrecta o incompletos'
			);
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }

    public function updatePassword($id, Request $request){
		// Recoger los datos por POST
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			// Validar los datos
			$validate = \Validator::make($params_array, [
				'password'	=> 'required'
			]);
			if($validate->fails()){
				$data = array(
					'status' => 'error',
					'code' => 400,
					'message' => 'La validación de los datos ha fallado.',
					'errors' => $validate->errors()
				);   
			} else {
				// Retirar el contenido que no se desea actualizar
				unset($params_array['id']);
				unset($params_array['user_alias']);
				unset($params_array['name']);
				unset($params_array['surname']);
				unset($params_array['role']);
				unset($params_array['created_at']);
				unset($params_array['updated_at']);

				// Cifrar la contraseña
				$params_array['password'] = hash('SHA256', $params_array['password']); 

				// Actualizar el usuario en la BD
				$user = User::where('id', $id)->update($params_array);
				if($user != 0){
					$data = array(
						'status' => 'success',
						'code' => 200,
						'message' => 'La contraseña del Usuario: '.$params->user_alias.' se ha actualizado correctamente.',
						'changes' => $params_array
					);
				} else {
					$data = array(
						'status' => 'error',
						'code' => 404,
						'message' => 'No se ha podido actualizar la contraseña del Usuario: '.$params->user_alias
					);  
				}
			}
		} else{    			
			$data = array(
				'status' => 'error',
				'code' => 411,
				'message' => 'Ha ingrasado los datos de manera incorrecta o incompletos'
			);
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }
	// =================================================================================
	// ======================Función para eliminar la información=======================
	// =================================================================================
    public function destroy($id, Request $request){
		$user = User::where('id', $id)->first();
		if(!empty($user)){
			$user->delete();

			$data = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'El Usuario '.$user->user_alias.' se ha eliminado correctamente',
				'destroy' => $user
			);
		} else {
			$data = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'No existe ningun Usuario con el id: '.$id
			); 
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }
}