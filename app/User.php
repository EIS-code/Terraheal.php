<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'dob',
        'country',
        'email',
        'tel_number',
        'nif',
        'address',
        'avatar',
        'avatar_original',
        'device_token',
        'device_type',
        'app_version',
        'photo',
        'oauth_uid',
        'oauth_provider',
        'country_id',
        'shop_id',
        'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    public $profilePhotoPath = 'user\profile\\';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /*protected $casts = [
        'email_verified_at' => 'datetime',
    ];*/

    const OAUTH_PROVIDER_GOOGLE   = 1;
    const OAUTH_PROVIDER_FACEBOOK = 2;
    const OAUTH_PROVIDER_TWITTER  = 3;
    const OAUTH_PROVIDER_LINKEDIN = 4;
    public static $oauthProviders = [
        self::OAUTH_PROVIDER_GOOGLE   => 'Google',
        self::OAUTH_PROVIDER_FACEBOOK => 'Facebook',
        self::OAUTH_PROVIDER_TWITTER  => 'Twitter',
        self::OAUTH_PROVIDER_LINKEDIN => 'LinkedIn'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:users,email,' . $id];
        } else {
            $emailValidator = ['unique:users'];
        }

        return Validator::make($data, [
            'name'                 => ['string', 'max:255'],
            'dob'                  => ['date'],
            'gender'               => ['in:m,f'],
            'email'                => array_merge(['string', 'email', 'max:255'], $emailValidator),
            'tel_number'           => ['string', 'max:50'],
            'password'             => ['min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'shop_id'              => ['integer'],
            // 'shop_id'           => ['required', 'integer'],
            'avatar'               => ['max:255'],
            'avatar_original'      => ['max:255'],
            'device_token'         => ['max:255'],
            'device_type'          => ['max:255'],
            'app_version'          => ['max:255'],
            'oauth_uid'            => ['max:255'],
            'oauth_provider'       => [(!empty($data['oauth_uid']) ? 'required' : ''), (!empty($data['oauth_uid']) ? 'in:1,2,3,4' : '')],
            'is_email_verified'    => ['in:0,1'],
            'is_mobile_verified'   => ['in:0,1'],
            'is_document_verified' => ['in:0,1'],
        ], [
            'password.regex'  => 'Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].'
        ]);
    }

    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }

    public function validateProfilePhoto($request)
    {
        return Validator::make($request->all(), [
            'profile_photo' => 'mimes:jpeg,png,jpg',
        ], [
            'profile_photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
}
