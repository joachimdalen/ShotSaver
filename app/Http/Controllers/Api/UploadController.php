<?php

namespace App\Http\Controllers\Api;

use App\Helpers\FileValidation;
use App\Models\FileUploads;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class UploadController extends Controller
{
	/**
	 * Upload a file
	 *
	 * @param Request $request
	 *
	 * @return \Illuminate\Contracts\Routing\UrlGenerator|string
	 */
	public function upload(Request $request)
	{
		$user = Auth::guard('api')->user();

		$file     = $request->file('d');
		$randStr  = str_random();
		$fileName = $randStr . '.' . $file->getClientOriginalExtension();

		$fileType = app(FileValidation::class)->fileType($file->getClientMimeType());
		if ($fileType == null) {
			return "This file type is not allowed.";
		}

		if ($file->move(public_path() . '/uploads', $fileName)) {
			$upload = FileUploads::create([
				'user_id'       => $user->id,
				'type'          => $file->getClientOriginalExtension(),
				'name'          => $randStr,
				'file'          => $fileName,
				'mime_type'     => $file->getClientMimeType(),
				'link'          => url('/uploads/' . $fileName),
				'size_in_bytes' => filesize(public_path() . '/uploads/' . $fileName),
			]);

			return route('file', $upload->name);
		} else {
			return "Failed to upload file.";
		}
	}

	/**
	 * Get the users uploads
	 *
	 * @param Request $request
	 *
	 * @return mixed
	 */
	public function myUploads(Request $request)
	{
		$user = Auth::guard('api')->user();

		return $user->uploads()->orderBy('id', 'DESC')->paginate(20);
	}
}
