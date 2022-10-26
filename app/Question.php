<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use SoftDeletes;
    use HasFactory;

    public const MULTIPLE_CHOICE_TYPE = 'multiple_choice';
    public const SLIDER_TYPE = 'slider';
    public const FREE_RESPONSE_TYPE = 'free_response';
    public const IMAGE_RESPONSE_TYPE = 'image_response';
    public const HEATMAP_RESPONSE_TYPE = 'heatmap_response';
    public const TEXT_HEATMAP_RESPONSE_TYPE = 'text_heatmap_response';

    protected $fillable = ['text', 'order', 'question_info', 'anonymous', 'folder_id', "allow_multiple"];
    protected $dates = ['deleted_at'];

    protected $casts = [
        'question_info' => 'array',
        'anonymous' => 'boolean',
        'allow_multiple' => 'boolean'
    ];
    public function folder() {
        return $this->belongsTo(Folder::class);
    }

    public function sessions() {
        return $this->hasMany(Session::class);
    }

    public function current_session() {
        return $this->belongsTo('\App\Session', 'current_session_id');
    }
}
