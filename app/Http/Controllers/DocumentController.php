<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Traits\backendTraits;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class DocumentController extends Controller
{
    use HelpersTrait;
    use backendTraits;
    public function submitDocuments(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'front_side_image' => 'required|image',
            'back_side_image' => 'required|image',
            'left_side_image' => 'required|image',
            'right_side_image' => 'required|image',
            'plate_number' => 'required|string',
            'license_image' => 'required|image',
            'car_type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $front_image = $this->upploadImage($request->front_side_image, 'documents');
        $back_image = $this->upploadImage($request->back_side_image, 'documents');
        $lside_image = $this->upploadImage($request->left_side_image, 'documents');
        $rside_image = $this->upploadImage($request->right_side_image, 'documents');
        $license_image = $this->upploadImage($request->license_image, 'documents');

        $document = Document::create([
            'user_id' => auth()->user()->id,
            'front_side_image' => $front_image,
            'back_side_image' => $back_image,
            'left_side_image' => $lside_image,
            'right_side_image' => $rside_image,
            'plate_number' => $request->plate_number,
            'license_image' => $license_image,
            'car_type' => $request->car_type,
        ]);

        return $this->returnData('document', $document, 'Documents submitted successfully');
    }


    public function approveDocument($id)
    {
        $document = Document::find($id);

        if (!$document) {
            return $this->returnError('E003', 'Document not found');
        }

        $document->is_verified = true;
        $document->verification_status = 'approved';
        $document->save();

        return $this->returnSuccessMessage('Document approved successfully');
    }
}