<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Therapist extends BaseModel
{
    protected $fillable = [
        'name',
        'dob',
        'gender',
        'email',
        'tel_number',
        'hobbies',
        'short_description',
        'is_freelancer',
        'paid_percentage',
        'is_deleted',
        'shop_id',
        'password'
    ];

    const GENDER_MALE   = 'm';
    const GENDER_FEMALE = 'f';

    public static $bookingTypes = [
        self::GENDER_MALE   => 'Male',
        self::GENDER_FEMALE => 'Female'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:therapists,email,' . $id];
        } else {
            $emailValidator = ['unique:therapists'];
        }

        return Validator::make($data, [
            'name'              => ['required', 'string', 'max:255'],
            'dob'               => ['required', 'date'],
            'gender'            => ['required', 'in:m,f'],
            'email'             => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'tel_number'        => ['required'],
            'hobbies'           => ['string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'shop_id'           => [(!$isUpdate ? 'required' : ''), 'integer'],
            'is_freelancer'     => ['required', 'in:0,1'],
            'paid_percentage'   => ['required', 'integer'],
            'password'          => [(!$isUpdate ? 'required': ''), 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
        ], [
            'password.regex' => 'Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].'
        ]);
    }
}