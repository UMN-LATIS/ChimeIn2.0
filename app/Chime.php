<?php

namespace App;
use DB;
use Sessions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chime extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    
    protected $fillable = ['name', 'access_code', 'require_login', 'students_can_view', 'join_instructions', 'only_correct_answers_lti', 'resource_link_pk', 'lti_grade_mode', 'show_folder_title_to_participants'];

    protected $attributes = [
        'join_instructions' => true,
    ];
    
    protected $dates = ['deleted_at'];
    protected $cascadeDeletes = ['folders'];

    public function folders() {
        return $this->hasMany(Folder::class);
    }
    
    public function users() {
        return $this->belongsToMany(User::class)
            ->withPivot('permission_number')
            ->withTimestamps();
    }

    public function presenters() {
      return $this->belongsToMany(User::class)
        ->wherePivot('permission_number', CHIMEIN_PRESENTER);
    }

    public function sessions() {

        $sessions = DB::table('sessions')->join('questions', 'sessions.question_id', '=', 'questions.id')->join('folders', 'questions.folder_id', '=', 'folders.id')->join('chimes', 'folders.chime_id', '=', 'chimes.id')->where('chimes.id', $this->id)->select("sessions.*")->get();

        $sessionModels= \App\Session::hydrate($sessions->toArray()); 
        return $sessionModels;
    }

    public function getUniqueCode() {
        $accessCode = DB::select('SELECT LPAD(random_num, 6,0) as code
FROM (
  SELECT FLOOR(RAND() * 999999) AS random_num from chimes
) AS numbers_mst_plus_1
WHERE random_num NOT IN (SELECT access_code FROM chimes WHERE access_Code IS NOT NULL)
LIMIT 1');
        return $accessCode[0]->code;
    }
    
    
    public function lti13_resource_link() {
        return $this->belongsTo(LTI13ResourceLink::class);
    }

}
