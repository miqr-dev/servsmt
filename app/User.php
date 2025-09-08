<?php

namespace App;

use Carbon\Carbon;
use Laravelista\Comments\Commenter;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
  use Notifiable, AuthenticatesWithLdap, Commenter;
  use HasRoles;
  use SoftDeletes;

  protected $guard_name = 'web';

  protected $fillable = [
    'name',
    'email',
    'password',
    'roles_name',
    'status',
    'lastlogin',
    'position',
    'abteilung',
    'tel',
    'fax',
    'ort',
    'straße',
    'plz',
    'vorname',
    'name',
    'mobil',
    'privat',
    'email_privat',
    'abschluss',
    'businessUnit',
    'office',
    'title',
  ];

  /**
   * The attributes that should be hidden for arrays.
   *
   * @var array
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast to native types.
   *
   * @var array
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public static function getAll()
  {
    $users = User::get()->toArray();
    $user = Auth()->user();
    $now = Carbon::now()->locale('de_DE')->translatedFormat('d F Y H:i');
    $admins = User::role('Super_Admin')->get();
    return [$user, $users, $now, $admins];
  }


  public function telephones()
  {
    return $this->belongsToMany('App\InvItems', 'inv_item_user', 'user_id', 'inv_item_id');
  }

  public function reminders()
  {
    return $this->hasMany(Reminder::class);
  }
  public function assignedTickets()
  {
    return $this->hasMany(Korso::class, 'assignedTo')->whereNull('deleted_at'); // Exclude soft deleted tickets
  }
  public function sekGroups()
  {
    return $this->belongsToMany(SekGroup::class, 'sek_group_user');
  }
}
