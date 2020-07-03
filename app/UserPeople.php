<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\Storage;

class UserPeople extends BaseModel
{
    protected $table = 'user_peoples';

    protected $fillable = [
        'name',
        'age',
        'gender',
        'photo',
        'is_removed',
        'user_id'
    ];

    public $fileSystem = 'public';
    public $photoPath  = 'user\people\\';

    public function validator(array $data, $isUpdate = false)
    {
        $userId = ['required', 'exists:' . User::getTableName() . ',id'];
        if ($isUpdate === true) {
            $userId = [];
        }

        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'age'        => ['required', 'integer'],
            'gender'     => ['required', 'in:m,f'],
            'photo'      => ['max:10240'],
            'is_removed' => ['integer', 'in:0,1'],
            'user_id'    => $userId
        ]);
    }

    public function validatePhoto($request)
    {
        return Validator::make($request->all(), [
            'photo' => 'mimes:jpeg,png,jpg',
        ], [
            'photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }

    public function getPhotoAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $photoPath = (str_ireplace("\\", "/", $this->photoPath));
        return Storage::disk($this->fileSystem)->url($photoPath . $value);
    }
}
