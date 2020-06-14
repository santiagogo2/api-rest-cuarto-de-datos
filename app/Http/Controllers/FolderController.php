<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Folder;

class FolderController extends Controller
{
	public function __construct(){
		$this->middleware('api.admin')->except(['index', 'getFolderByFolderId']);
	}
	// =================================================================================
	// ===========Funciones para visualizar la información de las carpetas==============
	// =================================================================================
    public function index(Request $request){
		$folders = Folder::orderBy('name', 'ASC')
							->with('documents')
							->get();
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
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);
    }

    public function show($id, Request $request){
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
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);
    }

    public function getFolderByFolderId($folder_id=null, Request $request){
		if($folder_id && $folder_id == 'null') $folder_id = null;
		$folders = Folder::with('documents')
						 ->where('folder_id', '=', $folder_id)
						 ->get();
		if(sizeof($folders) != 0){
			$data = array(
				'status'    => 'success',
				'code'      => 200,
				'folders'   => $folders
			);
		} else{
			$data = array(
				'status'    => 'success',
				'code'      => 404,
				'message'   => 'Aun no se han cargado carpetas a la base de datos.'
			);
		}
        // Dovolver respuesta
        return response()->json($data, $data['code']);
    }

   // =================================================================================
	// ===================Funciones para guardar nuevas carpetas=======================
	// ================================================================================
    public function store(Request $request){
		// Obtener los datos json
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			// Validar los datos
			$validate = \Validator::make($params_array, [
				'name'	     => 'required|unique:folder',
				'folder_id'  => 'nullable' 
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
				$folder->folder_id = $params->folder_id;

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
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);	
    }

    // =================================================================================
	// ==============Funciones para actualizar los registros de carpetas================
	// =================================================================================
    public function update($id, Request $request){
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
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);	
    }

    // =================================================================================
	// ===============Funciones para eliminar los registros de carpetas=================
	// =================================================================================
    public function destroy($id, Request $request){
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
    	// Dovolver respuesta
    	return response()->json($data, $data['code']);	
    }
}