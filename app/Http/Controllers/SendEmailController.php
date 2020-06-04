<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class SendEmailController extends Controller
{
    function send(Request $request){
    	$json = $request->input('json', null);
    	$params = json_decode($json);
    	$params_array = json_decode($json, true);

    	if(!empty($params_array)){
    		$validate = \Validator::make($params_array, [
    			'name'		=> 'required',
    			'surname'	=> 'required',
    			'email'		=> 'required|email',
    			'cedula'	=> 'required|numeric',
                'company'   => 'required',
    		]);

    		if($validate->fails()){
    			$data = array(
    				'status' 	=> 'error',
    				'code'		=> 400,
    				'message'	=> 'La validación de los datos ha fallado',
    				'errors'	=> $validate->errors()
    			);
    		} else{
    			$dataEmail = array(
    				'name'		=> $params->name,
    				'surname'	=> $params->surname,
    				'email'		=> $params->email,
    				'id'		=> $params->cedula,
                    'company'   => $params->company
    			);
    			Mail::to('cuartodedatos@subredsur.gov.co')->send(new SendMail($dataEmail));

    			$data = array(
    				'status' 	=> 'success',
    				'code'		=> 200,
    				'message'	=> 'Email enviado correctamente. Su solicitud está en proceso.'
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
