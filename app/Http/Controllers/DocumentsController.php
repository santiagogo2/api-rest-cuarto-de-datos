<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Documents;

class DocumentsController extends Controller
{
	public function __construct(){
		$this->middleware('api.admin')->only(['store', 'uploadFiles']);
		$this->middleware('api.superadmin')->only(['destroy']);
	}
	// =================================================================================
	// ============Funciones para visualizar la información de documentos===============
	// =================================================================================
	public function index(Request $request){
		// Buscar todos los documentos en la base de datos
		$documents = Documents::with('user')
                              ->orderBy('document_name', 'desc')
                              ->get();
		if(sizeof($documents) != 0){
			$data = array(
				'status'	=> 'success',
				'code'		=> 200,
				'documents'	=> $documents
			);
		} else{
			$data = array(
				'status'	=> 'error',
				'code'		=> 404,
				'message'	=> 'Aun no se han cargado documentos.'
			);
		}			
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
	}

	public function show($id, Request $request){
		// Buscar si existe el documento en la base de datos
		$document = Documents::with('user')->find($id);

		if(is_object($document)){
			$data = array(
                'status' => 'success',
                'code' => 200,
                'document' => $document
            );
		} else{
            $data = array(
                'status' => 'error',
                'code' => 404,
                'message' => 'El Documento con id '.$id.', no existe'
            );
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);

	}

	public function showByWord($word, Request $request){
		// Buscar los documentos
		$documents = Documents::with('user')
							  ->where('name', 'like', '%'.$word.'%')
                              ->orderBy('document_name', 'desc')
							  ->get();

		if(is_object($documents) && sizeof($documents) != 0){
			$data = array(
				'status'	=> 'success',
				'code'		=> 200,
				'documents'	=> $documents 
			);
		} else {
			$data = array(
				'status'	=> 'error',
				'code'		=> 404,
				'message'	=> 'No se ha encontrado ningún Documento que contenga en su nombre '.$word
			);
		}

		return response()->json($data, $data['code']);
	}

    public function showByFolder($folder, Request $request){
        //Buscar los documentos en la base de datos con la carpeta asociada
        $documents = Documents::with('user')
                              ->where('folder_id', $folder)
                              ->orderBy('document_name', 'desc')
                              ->get();
        if(is_object($documents) && sizeof($documents) != 0){
            $data = array(
                'status'    => 'success',
                'code'      => 200,
                'documents' => $documents
            );
        } else {
            $data = array(
                'status'    => 'error',
                'code'      => 404,
                'message'   => 'No se ha encontrado ningún Documento relacionado a la carpeta '.$folder
            );
        }
        return response()->json($data, $data['code']);
    }

	// =================================================================================
	// ===================Funciones para guardar nuevos documentos======================
	// =================================================================================
	public function store(Request $request){
		// Buscar el usuario del token
		$token = $request->header('Authorization');
		$jwtAuth = new \JwtAuth();
		$user = $jwtAuth->checkToken($token, true);
		// Obtener el usuario que se busca por el id
		$json = $request->input('json', null);
		$params = json_decode($json);
		$params_array = json_decode($json, true);

		if(!empty($params_array)){
			// Validar los datos
			$validate = \Validator::make($params_array, [
				'name'				=> 'required',
				'document_name'		=> 'required',
				'folder_id'         => 'required|numeric'
			]);
			if($validate->fails()){
				$data = array(
					'status' 	=> 'error',
					'code'		=> 400,
					'message'	=> 'La validación de los datos ha fallado',
					'errors'	=> $validate->errors()
				);
			} else{
				// Guardar el registro nuevo del documento
				$document = new Documents();
				$document->name = $params_array['name'];
				$document->document_name = $params_array['document_name'];
				$document->user_id = $user->sub;
				$document->folder_id = $params_array['folder_id'];

				$document->save();

				$data = array(
					'status' 	=> 'success',
					'code' 		=> 200,
					'message'	=> 'El documento '.$document->name.' se ha guardado correctamente.',
					'document'	=> $document
				);
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
	// ==============Funciones para eliminar los registros de documentos================
	// =================================================================================
    public function destroy($id, Request $request){
		$document = Documents::where('id', $id)->first();
		if(!empty($document)){
			$document->delete();

			$data = array(
				'status' => 'success',
				'code' => 200,
				'message' => 'El Documento '.$document->name.' se ha eliminado correctamente',
				'destroy' => $document
			);    			
		} else{
			$data = array(
				'status' => 'error',
				'code' => 404,
				'message' => 'No existe ningun Documento con el id: '.$id
			); 
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }

	// =================================================================================
	// =====================Interacciones con el disk de Laravel========================
	// =================================================================================
    public function getFile($filename, Request $request){
    	$isset = \Storage::disk('documents')->exists($filename);
    	if($isset){
			$path = \Storage::disk('documents')->path($filename);

			$arrayPath = explode('-', $filename, 2);
			$originalName = $arrayPath[1];
			
			// Se retorna una respuesta donde se descarga el archivo solicitado por la url
			return response()->download($path, $originalName);
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El archivo '.$filename.' no existe en el servidor.'
            );
            return response()->json($data, $data['code']);
    	}
    }

	// Guardar los archivos en el disk
    public function uploadFiles(Request $request){
		// Recoger los datos de la petición
		$file = $request->file('file0');

		// Validación del archivo
		$validate = \Validator::make($request->all(), [
			'file0' => 'required'
		]);
		if($validate->fails()){
			$data = array(
				'status' => 'error',
				'code' => 400,
				'message' => 'La validación de los datos ha fallado. No ha subido los archivos correctamente',
				'errors' => $validate->errors()
			);            
		} else {
			// Guardar la imágen
			if($file){
				$file_name = time().'-'.$file->getClientOriginalName();
				\Storage::disk('documents')->put($file_name, \File::get($file));

				$data = array(
					'status' => 'success',
					'code' => 200,
					'message' => 'El archivo '.$file->getClientOriginalName().' se ha subido correctamente al servidor.',
					'file' => $file_name
				);
			} else{
				$data = array(
					'status' => 'error',
					'code' => 411,
					'message' => 'No se ha podido subir el archivo '.$file->getClientOriginalName().' al servidor.'
				);
			}
		}
    	// Devolver la respuesta
    	return response()->json($data, $data['code']);
    }

    // Eliminar un archivo almacenado
    public function deleteFile($filename, Request $request){
    	$isset = \Storage::disk('documents')->exists($filename);
    	if($isset){
    		$file = \Storage::disk('documents')->delete($filename);
            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'El archivo '.$filename.' se ha eliminado correctamente.'
            );
    	} else{
            $data = array(
                'status' => 'error',
                'code' => 400,
                'message' => 'El archivo '.$filename.' no existe en el servidor.'
            );  		
    	}
    	// Devolver la respuesta
        return response()->json($data, $data['code']);  
    }
}