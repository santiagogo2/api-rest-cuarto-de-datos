<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Folder;

class FolderController extends Controller
{
	//FUNCIONES PARA VISUALIZAR LAS CARPETAS
    public function index(Request $request){
    	// Obtener el token del header
    	$token = $request->header('Authorization');
    	$jwtAuth = new \JwtAuth();
    	$user = $jwtAuth->checkToken($token, true);

    	// Comprobar si el usuario tiene permisos para acceder a esta sección
    	if($user->role){
    		$folders = Folder::with('documents')->get();
	    	if(sizeof($folders) != 0){
	    		$data = array(
	    			'status'	=> 'success',
	    			'code'		=> 200,
	    			'folders'	=> $folders
	    		);
	    	} else{
	    		$data = array(
	    			'status'	=> 'success',
	    			'code'		=> 404,
	    			'message'	=> 'Aun no se han cargado carpetas a la base de datos.'
	    		);
	    	}
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'El Usuario '.$user->user_alias.' no tiene permiso para acceder a esta sección.'
            );    		
    	}
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);
    }

    public function show($id, Request $request){
    	// Obtener el token del header
    	$token = $request->header('Authorization');
    	$jwtAuth = new \JwtAuth();
    	$user = $jwtAuth->checkToken($token, true);

    	// Comprobar si el usuario tiene permisos para acceder a esta sección
    	if($user->role == 'ROLE_SUPER_ADMIN' || $user->role == 'ROLE_ADMIN'){
    		$folder = Folder::find($id);

    		if(is_object($folder)){
    			$data = array(
    				'status'	=> 'success',
    				'code'		=> 200,
    				'folder'	=> $folder
    			);
    		} else{
    			$data = array(
    				'status'	=> 'error',
    				'code'		=> 404,
    				'message'	=> 'La carpeta con el id '.$id.', no existe.'
    			);
    		}
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'El Usuario '.$user->user_alias.' no tiene permiso para acceder a esta sección.'
            );  
    	}
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);
    }

    //FUNCIÓN PARA CREAR NUEVAS CARPETAS
    public function store(Request $request){
    	// Obtener el token del header
    	$token = $request->header('Authorization');
    	$jwtAuth = new \JwtAuth();
    	$user = $jwtAuth->checkToken($token, true);

    	// Comprobar si el usuario tiene permisos para acceder a esta sección
    	if($user->role == 'ROLE_SUPER_ADMIN' || $user->role == 'ROLE_ADMIN'){
    		// Obtener los datos json
    		$json = $request->input('json', null);
    		$params = json_decode($json);
    		$params_array = json_decode($json, true);

    		if(!empty($params_array)){
    			// Validar los datos
    			$validate = \Validator::make($params_array, [
    				'name'	=> 'required|unique:folder'
    			]);
    			if($validate->fails()){
	    			$data = array(
	    				'status' 	=> 'error',
	    				'code'		=> 400,
	    				'message'	=> 'La validación de los datos ha fallado',
	    				'errors'	=> $validate->errors()
	    			);
    			} else{
    				$folder = new Folder();
    				$folder->name = $params->name;

    				$folder->save();

    				$data = array(
    					'status'	=> 'success',
    					'code'		=> 200,
    					'message'	=> 'La carpeta '.$folder->name.' se ha creado correctamente.',
    					'folder'	=> $folder
    				);
    			}
    		} else{
	            $data = array(
	                'status' => 'error',
	                'code' => 411,
	                'message' => 'Ha ingrasado los datos de manera incorrecta o incompletos'
	            );
    		}
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'El Usuario '.$user->user_alias.' no tiene permiso para acceder a esta sección.'
            );
    	}
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);	
    }

    //FUNCIÓN PARA ACTUALIZAR LOS DATOS DE LAS CARPETAS CREADAS
    public function update($id, Request $request){
    	// Obtener el token del header
    	$token = $request->header('Authorization');
    	$jwtAuth = new \JwtAuth();
    	$user = $jwtAuth->checkToken($token, true);

    	// Comprobar si el usuario tiene permisos para acceder a esta sección
    	if($user->role == 'ROLE_SUPER_ADMIN' || $user->role == 'ROLE_ADMIN'){
    		// Recoger los datos por POST
    		$json = $request->input('json', null);
    		$params = json_decode($json);
    		$params_array = json_decode($json, true);

    		if(!empty($params_array)){
    			// Validar los datos
    			$validate = \Validator::make($params_array, [
    				'name'	=> 'required|unique:folder,name,'.$id
    			]);
    			if($validate->fails()){
	    			$data = array(
	    				'status' 	=> 'error',
	    				'code'		=> 400,
	    				'message'	=> 'La validación de los datos ha fallado. El nombre de la carpeta ya existe.',
	    				'errors'	=> $validate->errors()
	    			);
	    		} else{
	    			// Retirar el contenido que no se desea actualizar
	    			unset($params_array['id']);
    				unset($params_array['created_at']);
    				unset($params_array['updated_at']);

    				// Actualizar el usuario en la BD
    				$folder = Folder::where('id', $id)->update($params_array);
    				if($folder != 0){
                        $data = array(
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'La carpeta '.$params->name.' se ha actualizado correctamente.',
                            'changes' => $params_array
                        );
                    } else {
                        $data = array(
                            'status' => 'error',
                            'code' => 404,
                            'message' => 'No se ha podido actualizar la Carpeta: '.$params->name
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
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'El Usuario '.$user->user_alias.' no tiene permiso para acceder a esta sección.'
            );
    	}
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);	
    }

    //FUNCIÓN PARA ELIMINAR UNA CARPETA DE LA BASE DE DATOS
    public function destroy($id, Request $request){
    	// Obtener el token del header
    	$token = $request->header('Authorization');
    	$jwtAuth = new \JwtAuth();
    	$user = $jwtAuth->checkToken($token, true);

    	// Comprobar si el usuario tiene permisos para acceder a esta sección
    	if($user->role == 'ROLE_SUPER_ADMIN' || $user->role == 'ROLE_ADMIN'){
    		$folder = Folder::where('id', $id)->first();
    		if(!empty($folder)){
    			$folder->delete();

    			$data = array(
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La carpeta '.$folder->name.' se ha eliminado correctamente',
                    'destroy' => $folder
                );
    		} else{
                $data = array(
                    'status' => 'error',
                    'code' => 404,
                    'message' => 'No existe ninguna Carpeta con el id: '.$id
                ); 
    		}
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 401,
                'message' => 'El Usuario '.$user->user_alias.' no tiene permiso para acceder a esta sección.'
            );
    	}
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);	
    }
}
