<?php

namespace App;

use Carbon\Carbon;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;
use LdapRecord\Laravel\Auth\AuthenticatesWithLdap;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements LdapAuthenticatable
{
  use Notifiable, AuthenticatesWithLdap;
  // Spatie's own hasRole / hasAllRoles stay available as the *strict* checks
  // (hasAssignedRole / hasAllAssignedRoles below); the public ones are
  // overridden so Super_Admin implicitly holds every role.
  use HasRoles {
    hasRole as protected spatieHasRole;
    hasAllRoles as protected spatieHasAllRoles;
  }

  public const SUPER_ADMIN = 'Super_Admin';

  /**
   * Super_Admin can do everything (2026-09-28, your rule): a Super_Admin
   * counts as having EVERY role - Korso_Admin, Korso_ma, handwerk_admin,
   * Teilnehmer_Info, HR, … - without being assigned them.
   *
   * Because this lives in hasRole(), it covers every way the app checks a
   * role: hasRole / hasAnyRole / hasAllRoles in controllers and Blade,
   * @role / @hasanyrole, and the `role:...` route middleware. Permission
   * checks (@can, ->can()) were already covered by Gate::before in
   * AuthServiceProvider. The frontend gets the same effect through
   * HandleInertiaRequests, which shares every role name for a Super_Admin.
   *
   * NOT affected (on purpose): User::role('X') queries. "All Korso_ma users"
   * still means users actually assigned Korso_ma, so a Super_Admin doesn't
   * suddenly show up in assignee lists / per-admin buttons.
   *
   * Use hasAssignedRole() when you really need "is this role assigned to
   * the user" (e.g. before assigning it).
   */
  public function isSuperAdmin(): bool
  {
    return $this->spatieHasRole(self::SUPER_ADMIN);
  }

  public function hasRole($roles, ?string $guard = null): bool
  {
    return $this->isSuperAdmin() || $this->spatieHasRole($roles, $guard);
  }

  public function hasAllRoles($roles, ?string $guard = null): bool
  {
    return $this->isSuperAdmin() || $this->spatieHasAllRoles($roles, $guard);
  }

  /** Strict check: only roles actually assigned to this user. */
  public function hasAssignedRole($roles, ?string $guard = null): bool
  {
    return $this->spatieHasRole($roles, $guard);
  }

  /** Strict check: all of these roles are actually assigned to this user. */
  public function hasAllAssignedRoles($roles, ?string $guard = null): bool
  {
    return $this->spatieHasAllRoles($roles, $guard);
  }
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
    return [$user, $users, $now];
  }

  /**
   * Lightweight variant of getAll() for pages that only need the
   * current user and the formatted timestamp - avoids the full,
   * all-columns users table query when nothing on the page uses it.
   */
  public static function getCurrentAndNow()
  {
    $user = Auth()->user();
    $now = Carbon::now()->locale('de_DE')->translatedFormat('d F Y H:i');
    return [$user, $now];
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
