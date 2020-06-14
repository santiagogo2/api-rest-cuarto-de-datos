<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\IncomeRecord;

class IncomeRecordController extends Controller
{
    //FUNCIÓN PARA ALMACENAR LOS DATOS EN LA TABLA
    public function store(Request $request){
    	// Obtener los datos json del request
    	$json = $request->input('json', null);
    	$params = json_decode($json);
    	$params_array = json_decode($json, true);

    	if (!empty($params_array)) {
    		$validate = \Validator::make($params_array, [
    			'user'			=> 'required',
    			'document_name'	=> 'nullable',
    		]);

    		if($validate->fails()){
    			$data = array(
    				'status' 	=> 'error',
    				'code'		=> 400,
    				'message'	=> 'La validación de los datos ha fallado',
    				'errors'	=> $validate->errors()
    			);    			
    		} else{
    			// Guardar el registro
    			$incomeRecord = new IncomeRecord();
    			$incomeRecord->user = $params->user;
    			$incomeRecord->document_name = $params->document_name;

    			$incomeRecord->save();

    			$data = array(
    				'status' 	=> 'success',
    				'code' 		=> 200,
    				'message'	=> 'El registro de ingreso de usuario se ha guardado correctamente para el usuario '.$incomeRecord->user.'.',
    				'income_record'	=> $incomeRecord
    			);
    		}
    	} else{    		
            $data = array(
                'status' => 'error',
                'code' => 411,
                'message' => 'Ha ingrasado los datos de manera incorrecta o incompletos'
            );
    	}
        return response()->json($data, $data['code']);
    }
}